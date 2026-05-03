<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) { die("خطأ في الاتصال"); }
$conn->set_charset("utf8mb4");
/* بيانات أساسية */
$request_id = $_GET['request_id'] ?? null;
$bnf_id = $_SESSION['bnf_id'] ?? null;
if (!$request_id || !$bnf_id) {
    header("Location: Ben09_TrackScholarship.php");
    exit();}

/* جلب بيانات العقد */
$sql_info = "SELECT r.request_id,r.univ_name,r.major_name,s.sch_name,c.contract_id,c.amount,c.payments_count
             FROM scholarship_requests r
             INNER JOIN scholarship_opps s ON r.scholarship_id = s.scholarship_id
             INNER JOIN e_contract c ON r.request_id = c.request_id
             WHERE r.request_id = ? AND r.bnf_id = ?";
$stmt = $conn->prepare($sql_info);
$stmt->bind_param("ii", $request_id, $bnf_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
if (!$info) { header("Location: Ben09_TrackScholarship.php");   exit();}
$contract_id = $info['contract_id'];
/* رفع التقرير */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['report_file'])) {
    $p_id = (int) $_POST['payment_id'];
    $original_name = basename($_FILES['report_file']['name']);
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if ($file_ext == "pdf") {
        $file_name = time() . "_" . $original_name;
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target_file)) {
            $check_sql = "SELECT report_id FROM academic_report WHERE payment_id = ? AND contract_id = ? AND bnf_id = ? LIMIT 1";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iii", $p_id, $contract_id, $bnf_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                $update_sql = "UPDATE academic_report SET report_file = ?, report_upload = 'مرفوع', report_appoval = 'غير معتمد', submit_date = NOW() WHERE payment_id = ? AND contract_id = ? AND bnf_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("siii", $target_file, $p_id, $contract_id, $bnf_id);
                $update_stmt->execute();
            } else {
                $sql_ins = "INSERT INTO academic_report (bnf_id, contract_id, payment_id, report_file, report_upload, report_appoval, submit_date) VALUES (?, ?, ?, ?, 'مرفوع', 'غير معتمد', NOW())";
                $ins = $conn->prepare($sql_ins);
                $ins->bind_param("iiis", $bnf_id, $contract_id, $p_id, $target_file);
                $ins->execute();
            }
            header("Location: " . $_SERVER['PHP_SELF'] . "?request_id=" . $request_id);
            exit(); }  }}
/* جلب الدفعات */
$payments_list = [];
$res_p = $conn->query("SELECT p.payment_id,p.installment_number,p.payment_status,r.report_upload,r.report_appoval,r.report_file
                      FROM payments p
                      LEFT JOIN academic_report r ON p.payment_id = r.payment_id
                      WHERE p.contract_id = $contract_id
                      ORDER BY p.installment_number ASC");
while ($row = $res_p->fetch_assoc()) {
    $payments_list[] = $row;}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>نورين - صفحة التقارير</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">
<style>
.header{display:flex;justify-content:space-between;align-items:center}
.page-heading{display:flex;flex-direction:column;align-items:flex-start;text-align:right}
.header-icons{display:flex;align-items:center}
.page{padding:30px}
.page-top{display:flex;justify-content:flex-end;align-items:center;margin-bottom:20px}
.content-grid{display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:stretch}
.info-card{background:#FFFFFF;border:1px solid #EAEAEA;border-radius:16px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);height:100%}
.info-card h3{color:#3E2454;font-size:16px;font-weight:700;text-align:center;margin-bottom:18px;padding-bottom:10px;border-bottom:1px solid #F0F0F0}
.contract-box{direction:rtl;text-align:right}
.data-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;font-size:14px}
.data-row:last-child{margin-bottom:0}
.data-row label{color:#70A0AF;font-weight:700;flex-shrink:0}
.data-row span{color:#333333;font-weight:600;text-align:left}
.table-wrap{background:#FFFFFF;border:1px solid #EAEAEA;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:10px 16px 16px;height:100%}
.styled-table{width:100%;border-collapse:collapse}
.styled-table th{padding:16px 14px;text-align:center;color:#3E2454;font-size:15px;font-weight:700;border-bottom:1px solid #EAEAEA;background:#FFFFFF}
.styled-table td{padding:18px 14px;text-align:center;border-bottom:1px solid #F1F1F1;color:#444444;font-size:14px;background:#FFFFFF;vertical-align:middle}
.styled-table tbody tr:last-child td{border-bottom:none}
.installment-number{font-size:16px;font-weight:700;color:#333333}
.action-btn{display:inline-block;width:120px;padding:8px 0;border:1px solid #999;border-radius:10px;background:#fff;color:#3E2454;text-decoration:none;font-size:13px;font-weight:600;font-family:'Noto Kufi Arabic',sans-serif;transition:0.3s;cursor:pointer;text-align:center}
.action-btn:hover{background:#f4f0f7}
.status-badge{display:inline-block;min-width:120px;padding:7px 14px;border-radius:20px;color:#fff;font-size:13px;font-weight:700;font-family:'Noto Kufi Arabic',sans-serif;text-align:center}
.done{background:#63B68B}
.wait{background:#E9BE66}
.gray-badge{background:#FFFFFF;color:#444444;border:1px solid #DDDDDD}
.report-actions{display:flex;flex-direction:column;align-items:center;gap:8px}
.download-link{color:#777777;font-size:13px;font-weight:700;text-decoration:underline;text-underline-offset:4px}
.download-link:hover{color:#3E2454}
@media (max-width:950px){.content-grid{grid-template-columns:1fr}.styled-table{display:block;overflow-x:auto}}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
<div class="sidebar-top">
<div class="sidebar-logo">
<img src="شعار نورين.png" alt="نورين">
</div>
<ul class="sidebar-menu">
<li><a href="Ben00_MainPage.php">الرئيسية</a></li>
<li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
<li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
<li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
<li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
<li><a href="Ben19_Consultations.php">الاستشارات</a></li>
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
<div class="page-description">صفحة التقارير الأكاديمية</div>
</div>
<div class="header-icons">
<div class="settings-dropdown">
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
<div class="dropdown-menu">
<a href="Ben02_Profile.php">الملف الشخصي</a>
<a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
<a href="support.php">تقديم شكوى او استفسار</a>
</div>
</div>
</div>
</header>
<div class="page">
<div class="page-top">
<a href="Ben09_TrackScholarship.php" class="backbtn">
<img src="سهم تراجع.svg" class="backicon" alt="رجوع">
</a>
</div>
<div class="content-grid">
<div class="info-card contract-box">
<h3>ملخص العقد</h3>
<div class="data-row">
<label>رقم العقد:</label>
<span><?php echo $info['contract_id']; ?></span>
</div>
<div class="data-row">
<label>قيمة المنحة الإجمالية:</label>
<span><?php echo number_format($info['amount'], 2); ?></span>
</div>
<div class="data-row">
<label>عدد الدفعات:</label>
<span><?php echo $info['payments_count']; ?></span>
</div>
</div>
<div class="table-wrap">
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
<?php foreach ($payments_list as $p) { ?>
<tr>
<td><span class="installment-number"><?php echo $p['installment_number']; ?></span></td>
<td>
<div class="report-actions">
<?php if ($p['report_upload'] == 'مرفوع') { ?>
<span class="status-badge done">تم الرفع</span>
<?php if (!empty($p['report_file'])) { ?>
<a href="<?php echo htmlspecialchars($p['report_file']); ?>" target="_blank" class="download-link">تنزيل التقرير</a>
<?php } ?>
<?php } else { ?>
<form method="POST" enctype="multipart/form-data" class="upload-form">
<input type="hidden" name="payment_id" value="<?php echo $p['payment_id']; ?>">
<label class="action-btn">
رفع التقرير
<input type="file" name="report_file" accept=".pdf" onchange="this.form.submit()" style="display:none;">
</label>
</form>
<?php } ?>
</div>
</td>
<td>
<?php if ($p['report_appoval'] == 'معتمد') { ?>
<span class="status-badge done">معتمد</span>
<?php } else { ?>
<span class="status-badge wait"><?php echo $p['report_appoval'] ?: 'غير معتمد'; ?></span>
<?php } ?>
</td>
<td>
<?php if ($p['payment_status'] == 'تم الدفع' || $p['payment_status'] == 'مدفوعة') { ?>
<span class="status-badge done"><?php echo htmlspecialchars($p['payment_status']); ?></span>
<?php } else { ?>
<span class="status-badge wait"><?php echo htmlspecialchars($p['payment_status']); ?></span>
<?php } ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</body>
</html>