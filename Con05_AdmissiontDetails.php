<?php
session_start();

if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");}

mysqli_set_charset($con, "utf8mb4");
$office_id = (int) $_SESSION['office_id'];
$request_id = isset($_GET['request_id']) ? (int) $_GET['request_id'] : 0;

if ($request_id <= 0) {
    die("رقم الطلب غير صحيح");}

/* عند قبول الطلب */
if (isset($_POST['accept_request'])) {
    $sqlUpdate = "UPDATE admission_request
                  SET request_status = 'مقبول',
                      Result_status = 'قيد المعالجة'
                  WHERE request_id = $request_id AND office_id = $office_id";

    if (!mysqli_query($con, $sqlUpdate)) {
        die("خطأ في تحديث حالة الطلب");}

    header("Location: Con05_AdmissiontDetails.php?request_id=" . $request_id);exit();}

/* عند رفض الطلب */
if (isset($_POST['reject_request'])) {
    $sqlUpdate = "UPDATE admission_request
                  SET request_status = 'مرفوض',
                      Result_status = 'لم تُصدر'
                  WHERE request_id = $request_id AND office_id = $office_id";
    if (!mysqli_query($con, $sqlUpdate)) {
        die("خطأ في تحديث حالة الطلب"); }
    header("Location: Con05_AdmissiontDetails.php?request_id=" . $request_id);
    exit();}

$sql = "SELECT 
            ar.request_id,
            ar.program_type,
            ar.major_name,
            ar.univ_name,
            ar.Submit_date,
         ar.request_status,
ar.Result_status,
ar.payment_status,
b.f_name,
b.l_name,
b.email,
b.phone_num,
b.sch_field,
b.degree_level
        FROM admission_request ar
        INNER JOIN beneficiary b ON ar.bnf_id = b.bnf_id
        WHERE ar.request_id = $request_id AND ar.office_id = $office_id";

$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("الطلب غير موجود");
}

$request = mysqli_fetch_assoc($result);
$payment_status = trim($request['payment_status']);
$isPaid = ($payment_status == "مدفوع");

$sqlDocs = "SELECT doc_type, file_name, file
            FROM admission_request_documents
            WHERE request_id = $request_id
            ORDER BY doc_id ASC";
$resDocs = mysqli_query($con, $sqlDocs);

/* حالة الطلب */
$request_status = trim($request['request_status']);
if ($request_status == "") {
    $request_status = "في الانتظار";
}

/* حالة النتيجة */
$result_status = trim($request['Result_status']);
if ($result_status == "") {
    $result_status = "قيد المعالجة";
}

/* التحكم في الأزرار */
$isPending = ($request_status == "في الانتظار");
$isAccepted = ($request_status == "مقبول");
$isRejected = ($request_status == "مرفوض");
$resultIssued = ($result_status == "أُصدرت");

$program_type = $request['program_type'];
$program_name = "";

if ($program_type == "bachelor") {
    $program_name = "بكالوريوس";
} elseif ($program_type == "master") {
    $program_name = "ماجستير";
} elseif ($program_type == "phd") {
    $program_name = "دكتوراه";
}

$docLabels = [
    "CV" => "السيرة الذاتية",
    "Passport" => "جواز السفر",
    "Language Certificate" => "شهادة اللغة",
    "Recommendation Letters" => "خطابات التوصية",
    "Other Certificates" => "شهادات أخرى",
    "High School Certificate" => "شهادة الثانوية العامة",
    "Letter of Intent" => "خطاب النوايا",
    "University Degree Certificate" => "الشهادة الجامعية",
    "Academic Transcript" => "السجل الأكاديمي",
    "Statement of Purpose" => "بيان الغرض الدراسي",
    "Academic Certificates" => "الشهادات الأكاديمية",
    "Research Proposal" => "المقترح البحثي"
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تفاصيل طلب القبول</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=6">
<style>
.page-wrap{padding:14px 18px 18px;}
/* شريط زر الرجوع */
.top-bar { display:flex; justify-content:flex-end; align-items:center; margin-bottom:8px; max-width:980px; margin-right:300px; }
.back-btn img{width:38px; height:38px; object-fit:contain;}
.details-card{background:#fff; border:0.5px solid #c5c3c3; border-radius:12px; margin-bottom: 40px; padding:16px 18px; max-width:980px; margin:0 auto;}
.request-head{display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;}
.section-title{font-size:18px; font-weight:700; color:#3E2454; margin:0; font-family:'Noto Kufi Arabic', sans-serif;}
.action-box{display:flex; gap:8px; align-items:center;}
.action-btn{border:none; border-radius:8px; padding:8px 20px; font-size:13px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif; cursor:pointer; color:#fff;}
.accept-btn{background:#63B68B;}
.reject-btn{background:#E53935;}
.accept-btn.disabled-btn,.reject-btn.disabled-btn{opacity:0.45; cursor:default; pointer-events:none;}
.info-box{background:#fcfcfc; border:1px solid #efefef; border-radius:10px; padding:14px 16px; margin-bottom:12px;}
.info-grid{display:grid; grid-template-columns:1fr 1fr; gap:8px 20px;}
.info-row{display:flex; justify-content:space-between; align-items:flex-start; gap:10px; padding:2px 0;}
.info-label{min-width:135px; font-size:13px; color:#666; font-weight:700; text-align:right; font-family:'Noto Kufi Arabic', sans-serif;}
.info-value{flex:1; font-size:14px; color:#222; font-weight:600; line-height:1.7; text-align:right; word-break:break-word; font-family:'Noto Kufi Arabic', sans-serif;}
.full-width{grid-column:1 / -1;}
.docs-title{font-size:17px; font-weight:700; color:#3E2454; margin:6px 0 10px; font-family:'Noto Kufi Arabic', sans-serif;}
.docs-grid{display:grid; grid-template-columns:1fr 1fr; gap:8px 18px;}
.doc-item{display:flex; justify-content:space-between; align-items:center; gap:10px; padding:8px 10px; border:1px solid #ececec; border-radius:8px; background:#fff;}
.doc-label{font-size:13px; color:#444; font-weight:700; text-align:right; font-family:'Noto Kufi Arabic', sans-serif;}
.doc-link{font-size:12px; color:#4b6cb7; text-decoration:underline; text-align:left; word-break:break-word; font-family:'Noto Kufi Arabic', sans-serif;}
.empty-docs{font-size:13px; color:#777; margin-top:4px; font-family:'Noto Kufi Arabic', sans-serif;}
.bottom-actions { display:flex; justify-content:center; margin-top:16px; }
.upload-result-btn{display:inline-block; text-decoration:none; border:none; border-radius:8px; padding:10px 20px; font-size:13px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif;}
.upload-enabled{background:#3E2454; color:#fff;}
.upload-finished{background:#63B68B; color:#fff; pointer-events:none; cursor:default;}
.upload-disabled{background:#d4d4d4; color:#7a7a7a; cursor:not-allowed; pointer-events:none;}
        .page-description{ width:100%; direction:rtl; text-align:right;}

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
          <li><a href="Con00_MainPage.php">الرئيسية</a></li>
          <li><a href="Con04_AdmissionReq.php" class="active">إدارة طلبات القبول</a></li>
          <li><a href="Con03_Consultations.php">الاستشارات</a></li>
          <li><a href="Con08_ReqRating.php">تقييمات المستفيدين</a></li>
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
                <div class="page-title">إدارة طلبات القبول</div>
                <div class="page-description">تفاصيل طلب القبول المقدم</div>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
                    <div class="dropdown-menu">
                        <a href="Con02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-wrap">

            <div class="top-bar">
                <a href="Con04_AdmissionReq.php" class="back-btn">
                    <img src="سهم تراجع.svg" alt="رجوع">
                </a>
            </div>

            <div class="details-card">

                <div class="request-head">
                    <div class="section-title">بيانات الطلب</div>

                    <?php if ($isPending) { ?>
                        <div class="action-box">
                            <form method="POST">
                                <input type="hidden" name="accept_request" value="1">
                                <button type="submit" class="action-btn accept-btn">قبول</button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="reject_request" value="1">
                                <button type="submit" class="action-btn reject-btn">رفض</button>
                            </form>
                        </div>
                    <?php } ?>
                </div>

                <div class="info-box">
                    <div class="section-title">بيانات المستفيد</div>

                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">الاسم الكامل:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['f_name'] . " " . $request['l_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">رقم الهاتف:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['phone_num'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">البريد الإلكتروني:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">المؤهل الدراسي:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['degree_level'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row full-width">
                            <div class="info-label">التخصص الدراسي الحالي:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['sch_field'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="info-box">
                    <div class="section-title">بيانات الطلب</div>

                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">نوع البرنامج:</div>
                            <div class="info-value"><?php echo htmlspecialchars($program_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">الجامعة المطلوبة:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['univ_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">التخصص المطلوب:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request['major_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">حالة الطلب:</div>
                            <div class="info-value"><?php echo htmlspecialchars($request_status, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">حالة النتيجة:</div>
                            <div class="info-value"><?php echo htmlspecialchars($result_status, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>

                    <div class="docs-title">المستندات المقدمة</div>

                    <?php if ($resDocs && mysqli_num_rows($resDocs) > 0) { ?>
                        <div class="docs-grid">
                            <?php while ($doc = mysqli_fetch_assoc($resDocs)) { ?>
                                <?php
                                $label = isset($docLabels[$doc['doc_type']]) ? $docLabels[$doc['doc_type']] : $doc['doc_type'];
                                ?>
                                <div class="doc-item">
                                    <div class="doc-label"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <a href="<?php echo htmlspecialchars($doc['file'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="doc-link"><?php echo htmlspecialchars($doc['file_name'], ENT_QUOTES, 'UTF-8'); ?></a>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-docs">لا توجد مستندات مرفوعة لهذا الطلب.</div>
                    <?php } ?>

                    <div class="bottom-actions">
                   <?php if ($resultIssued) { ?>

    <a href="#" class="upload-result-btn upload-finished">تم إصدار النتيجة</a>

<?php } elseif ($isAccepted && $isPaid) { ?>

    <a href="Con06_UploadResult.php?request_id=<?php echo $request_id; ?>" class="upload-result-btn upload-enabled">رفع نتيجة التقديم</a>

<?php } elseif ($isAccepted && !$isPaid) { ?>

    <a href="#" class="upload-result-btn upload-disabled">رفع نتيجة التقديم </a>

<?php } else { ?>

    <a href="#" class="upload-result-btn upload-disabled">رفع نتيجة التقديم</a>

<?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>