<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 1. الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) { die("فشل الاتصال"); }
$conn->set_charset("utf8mb4");

/* 2. التقاط البيانات الأساسية */
$request_id = $_GET['request_id'] ?? null;
$bnf_id = $_SESSION['bnf_id'] ?? null; 
$step = $_GET['step'] ?? 'view'; 

$contract_data = null;
$user_name = "المستخدم"; 

if ($request_id && $bnf_id) {
    $sql = "
        SELECT r.request_id, r.major_name, r.univ_name,
               c.contract_id, c.amount, c.terms, c.funding_duration, c.payments_count, c.approval_status,
               b.f_name, b.l_name
        FROM scholarship_requests r
        JOIN e_contract c ON r.request_id = c.request_id
        JOIN beneficiary b ON r.bnf_id = b.bnf_id 
        WHERE r.request_id = ? AND r.bnf_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $bnf_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $contract_data = $result->fetch_assoc();
        $user_name = $contract_data['f_name'] . " " . $contract_data['l_name'];
    }
}

/* 3. معالجة توقيع العقد وإنشاء الدفعات تلقائياً */
if (isset($_POST['confirm_final']) && $contract_data) {
    $conn->begin_transaction();
    try {
        // تحديث حالة العقد
        $update_sql = "UPDATE e_contract SET approval_status = 'تمت الموافقة' WHERE request_id = ?";
        $stmt_upd = $conn->prepare($update_sql);
        $stmt_upd->bind_param("i", $contract_data['request_id']);
        $stmt_upd->execute();

        $c_id = $contract_data['contract_id'];
        $p_count = $contract_data['payments_count'];
        $total_amt = $contract_data['amount'];
        $each_amt = $total_amt / $p_count;

        // التحقق من عدم وجود دفعات مسبقة لتجنب التكرار
        $check_existing = $conn->query("SELECT COUNT(*) as existing FROM payments WHERE contract_id = $c_id");
        if ($check_existing->fetch_assoc()['existing'] == 0) {
            
            // القيمة النصية الدقيقة كما هي في enum قاعدة البيانات
            $status_default = "بانتظار الدفع"; 

            for ($i = 1; $i <= $p_count; $i++) {
                $ins_p = $conn->prepare("INSERT INTO payments (contract_id, installment_number, payment_amount, payment_status) VALUES (?, ?, ?, ?)");
                // ربط البيانات: i (int), i (int), d (double), s (string)
                $ins_p->bind_param("iids", $c_id, $i, $each_amt, $status_default);
                $ins_p->execute();
            }
        }
        
        $conn->commit();
        header("Location: Ben12_EContract.php?request_id=" . $request_id . "&step=view");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "خطأ في النظام: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الموافقة على العقد الإلكتروني</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        body { font-family: 'Noto Kufi Arabic', sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; }
        .main-content { padding: 20px 40px; }
        
        /* الهيدر المنسق */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 0; 
            margin-bottom: 25px; 
            border-bottom: 1px solid #eee;
        }
        .page-heading { display: flex; flex-direction: column; gap: 5px; }
        .page-title { font-size: 22px; font-weight: 700; color: #333; margin: 0; }
        .page-description { font-size: 14px; color: #777; margin: 0; }
        
        .back-nav img { width: 35px; cursor: pointer; transition: 0.2s; }
        .back-nav img:hover { transform: scale(1.1); }

        .contract-layout { display: flex; gap: 25px; align-items: flex-start; }
        .right-col { flex: 1; display: flex; flex-direction: column; gap: 20px; }
        .left-col { flex: 1.8; }
        
        .card { background: #fff; border-radius: 12px; padding: 25px; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card-h { font-size: 16px; font-weight: 700; color: #5A2D74; margin-bottom: 20px; text-align: center; border-bottom: 1px solid #f2f2f2; padding-bottom: 12px; }
        
        .info-line { display: flex; justify-content: flex-start; gap: 15px; margin-bottom: 15px; font-size: 13.5px; }
        .info-line label { color: #8EB4C2; font-weight: 600; min-width: 110px; }
        .info-line span { color: #555; font-weight: 500; }
        
        .terms-box { font-size: 13.5px; line-height: 1.9; color: #555; white-space: pre-line; text-align: justify; }
        .agree-text { font-size: 13px; color: #444; line-height: 1.7; margin-bottom: 20px; }
        .btn-blue { background: #5A2D74; color: #fff; border: none; width: 100%; padding: 12px; border-radius: 6px; font-weight: 700; cursor: pointer; display: block; text-align: center; text-decoration: none; }
        .signed-msg { background: #D4F4E2; color: #55A082; padding: 12px; border-radius: 6px; font-weight: 700; text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px; }
        
        /* Modal Style */
        .modal-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 999; }
        .modal-inner { background: #fff; padding: 35px; border-radius: 15px; text-align: center; max-width: 400px; }
        .warn-icon { width: 50px; height: 50px; background: #C96B6B; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold; }
        .modal-buttons { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
        .m-btn { padding: 10px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; border: none; text-decoration: none; font-size: 14px; }
        .m-btn-confirm { background: #69B38A; color: white; }
        .m-btn-cancel { background: #f88a8a; color: white; }
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
        <header class="header">
            <div class="page-heading">
                <div class="page-title">متابعة المنح</div>
                <div class="page-description">صفحة العقد الإلكتروني</div>
            </div>
            <div class="back-nav">
                <a href="Ben09_TrackScholarship.php"><img src="سهم تراجع.svg" alt="رجوع"></a>
            </div>
        </header>

        <?php if ($contract_data): ?>
        <div class="contract-layout">
            <div class="right-col">
                <div class="card">
                    <div class="card-h">ملخص العقد</div>
                    <div class="info-line"><label>قيمة التمويل:</label> <span><?php echo number_format($contract_data['amount']); ?> ريال</span></div>
                    <div class="info-line"><label>مدة التمويل:</label> <span><?php echo $contract_data['funding_duration']; ?> سنوات</span></div>
                    <div class="info-line"><label>عدد الدفعات:</label> <span><?php echo $contract_data['payments_count']; ?> دفعات</span></div>
                    <div class="info-line"><label>اسم الطالب:</label> <span><?php echo $user_name; ?></span></div>
                    <div class="info-line"><label>رقم العقد:</label> <span><?php echo $contract_data['contract_id']; ?></span></div>
                </div>

                <div class="card">
                    <div class="card-h">الإقرار والموافقة</div>
                    <p class="agree-text">
                        أقر أنا <b><?php echo $user_name; ?></b> على الشروط والأحكام الواردة في هذا العقد وأتعهد بالالتزام بها تماماً.
                    </p>
                    <?php if ($contract_data['approval_status'] === 'تمت الموافقة'): ?>
                        <div class="signed-msg"><span>تمت الموافقة ✓</span></div>
                    <?php else: ?>
                        <a href="?request_id=<?php echo $request_id; ?>&step=confirm" class="btn-blue">أوافق على الشروط والأحكام</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="left-col">
                <div class="card">
                    <div class="card-h">شروط العقد</div>
                    <div class="terms-box"><?php echo nl2br(htmlspecialchars($contract_data['terms'])); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($step == 'confirm'): ?>
<div class="modal-bg">
    <div class="modal-inner">
        <div class="warn-icon">!</div>
        <p>بموافقتك على هذا العقد، فإنك تقر بالتزامك الكامل بجميع الشروط وتتحمل المسؤولية النظامية في حال الإخلال بها</p>
        <div class="modal-buttons">
            <form method="POST" action="Ben12_EContract.php?request_id=<?php echo $request_id; ?>">
                <button type="submit" name="confirm_final" class="m-btn m-btn-confirm">موافق</button>
            </form>
            <a href="?request_id=<?php echo $request_id; ?>&step=view" class="m-btn m-btn-cancel">تراجع</a>
        </div>
    </div>
</div>
<?php endif; ?>
</body>
</html>