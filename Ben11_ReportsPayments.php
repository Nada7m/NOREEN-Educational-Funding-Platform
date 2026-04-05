<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) { die("خطأ في الاتصال"); }
$conn->set_charset("utf8mb4");

$request_id = $_GET['request_id'] ?? null;
$bnf_id = $_SESSION['bnf_id'] ?? null;

if (!$request_id || !$bnf_id) { 
    header("Location: Ben09_TrackScholarship.php"); 
    exit(); 
}

// 2. جلب بيانات المنحة والعقد
$sql_info = "
    SELECT r.request_id, r.univ_name, r.major_name, s.sch_name,
           c.contract_id, c.amount, c.payments_count
    FROM scholarship_requests r
    INNER JOIN scholarship_opps s ON r.scholarship_id = s.scholarship_id
    INNER JOIN e_contract c ON r.request_id = c.request_id
    WHERE r.request_id = ? AND r.bnf_id = ?
";
$stmt = $conn->prepare($sql_info);
$stmt->bind_param("ii", $request_id, $bnf_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
$contract_id = $info['contract_id'];

// 3. معالجة رفع التقرير
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['report_file'])) {
    $p_id = $_POST['payment_id'];
    $file_name = time() . "_" . basename($_FILES['report_file']['name']);
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target_file)) {
        $sql_ins = "INSERT INTO academic_report (bnf_id, contract_id, payment_id, report_file, report_upload, report_appoval, submit_date) 
                    VALUES (?, ?, ?, ?, 'مرفوع', 'غير معتمد', NOW())";
        $ins = $conn->prepare($sql_ins);
        $ins->bind_param("iiis", $bnf_id, $contract_id, $p_id, $target_file);
        if($ins->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?request_id=" . $request_id);
            exit();
        }
    }
}

// 4. جلب الدفعات والتقارير
$payments_list = [];
$res_p = $conn->query("
    SELECT p.payment_id, p.installment_number, p.payment_status, 
           r.report_upload, r.report_appoval
    FROM payments p
    LEFT JOIN academic_report r ON p.payment_id = r.payment_id
    WHERE p.contract_id = $contract_id
    ORDER BY p.installment_number ASC
");
while($row = $res_p->fetch_assoc()) {
    $payments_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نورين - صفحة التقارير</title>
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        body { font-family: 'Noto Kufi Arabic', sans-serif; background-color: #f4f7f6; margin: 0; }
        .main-content { padding: 20px 40px; flex: 1; }
        
        /* ضبط الهيدر وزر التراجع */
        .header-flex { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .page-title { color: #000; font-weight: bold; font-size: 1.2rem; }
        .page-desc { color: #666; font-size: 0.9rem; }
        .back-icon img { width: 35px; transition: 0.3s; }
        .back-icon img:hover { transform: scale(1.1); }

        /* الكروت */
        .cards-container { display: flex; gap: 20px; margin-bottom: 25px; }
        .info-card { background: #fff; border-radius: 15px; padding: 25px; flex: 1; border: 1px solid #eee; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .info-card h3 { color: #5A2D74; font-size: 15px; margin-bottom: 15px; text-align: center; border-bottom: 1px solid #f5f5f5; padding-bottom: 10px; }
        .data-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .data-row label { color: #8EB4C2; font-weight: bold; }
        .data-row span { color: #333; font-weight: 500; }

        /* الجدول */
        .styled-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
        .styled-table th { padding: 15px; color: #5A2D74; font-size: 14px; text-align: center; }
        .styled-table td { background: #fff; padding: 20px; text-align: center; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; }
        .styled-table tr td:first-child { border-right: 1px solid #f0f0f0; border-radius: 0 12px 12px 0; }
        .styled-table tr td:last-child { border-left: 1px solid #f0f0f0; border-radius: 12px 0 0 12px; }

        /* الأزرار والحالات */
        .btn-upload { color: #5A2D74; font-weight: bold; cursor: pointer; text-decoration: underline; font-size: 13px; }
        .status-badge { padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; }
        .wait { background: #FFF4E5; color: #E6BC6A; } /* غير معتمد / بانتظار الدفع */
        .done { background: #D4F4E2; color: #55A082; } /* معتمد / تم الدفع */
        .txt-gray { color: #bbb; font-weight: bold; }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
                <li><a href="#">الاستشارات</a></li>
            </ul>
        </div>
        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content">
        <header class="header-flex">
            <div>
                <div class="page-title">متابعة المنح</div>
                <div class="page-desc">صفحة التقارير الأكاديمية</div>
            </div>
            <div class="back-icon">
                <a href="Ben09_TrackScholarship.php"><img src="سهم تراجع.svg"></a>
            </div>
        </header>

        <div class="cards-container">
            <div class="info-card">
                <h3>تفاصيل المنحة الحالية</h3>
                <div class="data-row"><label>رقم الطلب:</label> <span>#<?php echo $info['request_id']; ?></span></div>
                <div class="data-row"><label>المنحة:</label> <span><?php echo $info['sch_name']; ?></span></div>
                <div class="data-row"><label>التخصص:</label> <span><?php echo $info['major_name']; ?></span></div>
            </div>
            <div class="info-card">
                <h3>ملخص العقد</h3>
                <div class="data-row"><label>رقم العقد:</label> <span>CNT-<?php echo $info['contract_id']; ?></span></div>
                <div class="data-row"><label>قيمة المنحة الإجمالية:</label> <span><?php echo number_format($info['amount']); ?> ريال</span></div>
                <div class="data-row"><label>عدد الدفعات:</label> <span><?php echo $info['payments_count']; ?></span></div>
            </div>
        </div>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>رقم الدفعة</th>
                    <th>حالة التقرير</th>
                    <th>اعتماد التقرير</th>
                    <th>حالة الدفعة</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments_list as $p): ?>
                <tr>
                    <td><b><?php echo $p['installment_number']; ?></b></td>
                    <td>
                        <?php if ($p['report_upload'] == 'مرفوع'): ?>
                            <span class="txt-gray">تم الرفع</span>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="payment_id" value="<?php echo $p['payment_id']; ?>">
                                <label class="btn-upload"> ارفع التقرير 
                                    <input type="file" name="report_file" onchange="this.form.submit()" style="display:none;">
                                </label>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo ($p['report_appoval'] == 'معتمد') ? 'done' : 'wait'; ?>">
                            <?php echo $p['report_appoval'] ?: 'غير معتمد'; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo ($p['payment_status'] == 'تم الدفع' || $p['payment_status'] == 'مدفوعة') ? 'done' : 'wait'; ?>">
                            <?php echo $p['payment_status']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>