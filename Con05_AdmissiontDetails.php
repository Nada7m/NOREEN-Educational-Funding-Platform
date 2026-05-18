<?php
session_start();
// التحقق من تسجيل دخول المكتب
if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}
$con = mysqli_connect("localhost", "root", "", "noreen");
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");}
mysqli_set_charset($con, "utf8mb4");
// جلب رقم المكتب الحالي
$office_id = (int) $_SESSION['office_id'];
// جلب رقم الطلب من الرابط
$request_id = isset($_GET['request_id']) ? (int) $_GET['request_id'] : 0;
// التحقق من صحة رقم الطلب
if ($request_id <= 0) {
    die("رقم الطلب غير صحيح");}

/* عند قبول الطلب */
if (isset($_POST['accept_request'])) {
    $sqlUpdate = "UPDATE admission_request 
                  SET request_status = 'مقبول',
                      Result_status = 'قيد المعالجة'
                  WHERE request_id = $request_id 
                  AND office_id = $office_id";

    // تنفيذ التحديث
    if (!mysqli_query($con, $sqlUpdate)) {
        die("خطأ في تحديث حالة الطلب: " . mysqli_error($con));}

    // تحديث الصفحة
    header("Location: Con05_AdmissiontDetails.php?request_id=" . $request_id);exit();}

   /* عند رفض الطلب */
if (isset($_POST['reject_request'])) {
    $sqlUpdate = "UPDATE admission_request 
                  SET request_status = 'مرفوض',
                      Result_status = 'لم تُصدر'
                  WHERE request_id = $request_id 
                  AND office_id = $office_id";

        // تنفيذ التحديث
    if (!mysqli_query($con, $sqlUpdate)) {
     die("خطأ في تحديث حالة الطلب: " . mysqli_error($con)); }
         // تحديث الصفحة
    header("Location: Con05_AdmissiontDetails.php?request_id=" . $request_id);
    exit();}

/*   جلب بيانات الطلب والمستفيد */
$sql = "SELECT  ar.request_id, ar.program_type, ar.major_name, ar.univ_name, ar.Submit_date,
     ar.request_status,ar.Result_status,ar.payment_status,b.f_name,b.l_name,b.email,
b.phone_num,b.sch_field,b.degree_level FROM admission_request ar
    INNER JOIN beneficiary b ON ar.bnf_id = b.bnf_id WHERE ar.request_id = $request_id AND ar.office_id = $office_id";
// تنفيذ الاستعلام
$result = mysqli_query($con, $sql);
// التحقق من وجود الطلب
if (!$result || mysqli_num_rows($result) == 0) {
    die("الطلب غير موجود");
}
// جلب البيانات
$request = mysqli_fetch_assoc($result);
// حالة الدفع
$payment_status = trim($request['payment_status']);
// هل تم الدفع؟
$isPaid = ($payment_status == "مدفوع");
/*   جلب المستندات المرفوعة*/
$sqlDocs = "SELECT doc_type, file_name, file FROM admission_request_documents
            WHERE request_id = $request_id ORDER BY doc_id ASC";
            // تنفيذ الاستعلام
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
// هل الطلب في الانتظار؟
$isPending = ($request_status == "في الانتظار");
// هل الطلب مقبول؟
$isAccepted = ($request_status == "مقبول");
$isRejected = ($request_status == "مرفوض");
$resultIssued = ($result_status == "أُصدرت");
/*   تحويل نوع البرنامج للعربية*/
$program_type = $request['program_type'];
$program_name = "";
// بكالوريوس
if ($program_type == "bachelor") {
    $program_name = "بكالوريوس";
    // ماجستير
} elseif ($program_type == "master") {
    $program_name = "ماجستير";
    // دكتوراه
} elseif ($program_type == "phd") {
    $program_name = "دكتوراه";
}
/*  أسماء المستندات بالعربي */
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
    "Research Proposal" => "المقترح البحثي"];?>
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
/* أيقونة الرجوع */
.back-btn img{width:38px; height:38px; object-fit:contain;}
/*   بطاقة التفاصيل */
.details-card{background:#fff; border:0.5px solid #c5c3c3; border-radius:12px; margin-bottom: 40px; padding:16px 18px; max-width:980px; margin:0 auto;}

.request-head{display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;}
/* رأس الطلب */
.section-title{font-size:18px; font-weight:700; color:#3E2454; margin:0; font-family:'Noto Kufi Arabic', sans-serif;}
/* صندوق الأزرار */
.action-box{display:flex; gap:8px; align-items:center;}
/* الأزرار */
.action-btn{border:none; border-radius:8px; padding:8px 20px; font-size:13px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif; cursor:pointer; color:#fff;}
 /* زر القبول */
.accept-btn{background:#63B68B;}
/* زر الرفض */
.reject-btn{background:#E53935;}
/* الأزرار المعطلة */
.accept-btn.disabled-btn,.reject-btn.disabled-btn{opacity:0.45; cursor:default; pointer-events:none;}
/*   صندوق المعلومات  */
.info-box{background:#fcfcfc; border:1px solid #efefef; border-radius:10px; padding:14px 16px; margin-bottom:12px;}
/* شبكة المعلومات */
.info-grid{display:grid; grid-template-columns:1fr 1fr; gap:8px 20px;}
/* صف المعلومات */
.info-row{display:flex; justify-content:space-between; align-items:flex-start; gap:10px; padding:2px 0;}
/* عنوان الحقل */
.info-label{min-width:135px; font-size:13px; color:#666; font-weight:700; text-align:right; font-family:'Noto Kufi Arabic', sans-serif;}
/* قيمة الحقل */
.info-value{flex:1; font-size:14px; color:#222; font-weight:600; line-height:1.7; text-align:right; word-break:break-word; font-family:'Noto Kufi Arabic', sans-serif;}
/* عنصر بعرض كامل */
.full-width{grid-column:1 / -1;}
/* عنصر بعرض كامل */
/*  المستندات */
.docs-title{font-size:17px; font-weight:700; color:#3E2454; margin:6px 0 10px; font-family:'Noto Kufi Arabic', sans-serif;}
/* شبكة المستندات */
.docs-grid{display:grid; grid-template-columns:1fr 1fr; gap:8px 18px;}
/* العنصر الواحد */
.doc-item{display:flex; justify-content:space-between; align-items:center; gap:10px; padding:8px 10px; border:1px solid #ececec; border-radius:8px; background:#fff;}
/* اسم المستند */
.doc-label{font-size:13px; color:#444; font-weight:700; text-align:right; font-family:'Noto Kufi Arabic', sans-serif;}
/* رابط الملف */
.doc-link{font-size:12px; color:#4b6cb7; text-decoration:underline; text-align:left; word-break:break-word; font-family:'Noto Kufi Arabic', sans-serif;}
/* عند عدم وجود مستندات */
.empty-docs{font-size:13px; color:#777; margin-top:4px; font-family:'Noto Kufi Arabic', sans-serif;}
/*  زر رفع النتيجة */
.bottom-actions { display:flex; justify-content:center; margin-top:16px; }
/* الزر */
.upload-result-btn{display:inline-block; text-decoration:none; border:none; border-radius:8px; padding:10px 20px; font-size:13px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif;}
/* مفعل */
.upload-enabled{background:#3E2454; color:#fff;}
/* تم إصدار النتيجة */
.upload-finished{background:#63B68B; color:#fff; pointer-events:none; cursor:default;}
/* معطل */
.upload-disabled{background:#d4d4d4; color:#7a7a7a; cursor:not-allowed; pointer-events:none;}
/* وصف الصفحة */
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
                    <!-- أزرار القبول والرفض -->
                    <?php if ($isPending) { ?>
                        <div class="action-box">
                            <!-- زر قبول الطلب -->
                            <form method="POST">
                                <input type="hidden" name="accept_request" value="1">
                                <button type="submit" class="action-btn accept-btn">قبول</button>
                            </form>
                           <!-- زر رفض الطلب -->
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
               <!-- بيانات الطلب  -->
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
                    <!--  المستندات المرفوعة -->
                    <div class="docs-title">المستندات المقدمة</div>
                    <!-- إذا توجد مستندات -->
                    <?php if ($resDocs && mysqli_num_rows($resDocs) > 0) { ?>
                    <!-- المرور على جميع المستندات -->
                            <?php while ($doc = mysqli_fetch_assoc($resDocs)) { ?>
                                <?php
                            // اسم المستند بالعربي
                                $label = isset($docLabels[$doc['doc_type']]) ? $docLabels[$doc['doc_type']] : $doc['doc_type'];
                                ?>
                                <div class="doc-item">
                                    <div class="doc-label"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <a href="<?php echo htmlspecialchars($doc['file'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="doc-link"><?php echo htmlspecialchars($doc['file_name'], ENT_QUOTES, 'UTF-8'); ?></a>
                                </div>
                            <?php } ?>
                        </div>
                      <!-- إذا لا توجد مستندات -->
                    <?php } else { ?>
                        <div class="empty-docs">لا توجد مستندات مرفوعة لهذا الطلب.</div>
                    <?php } ?>
  <!--  زر رفع النتيجة -->
                    <div class="bottom-actions">
                   <?php if ($resultIssued) { ?>
    <!-- إذا تم إصدار النتيجة -->
    <a href="#" class="upload-result-btn upload-finished">تم إصدار النتيجة</a>
<?php } elseif ($isAccepted && $isPaid) { ?>
    <!-- إذا الطلب مقبول وتم الدفع -->
    <a href="Con06_UploadResult.php?request_id=<?php echo $request_id; ?>" class="upload-result-btn upload-enabled">رفع نتيجة التقديم</a>
<?php } elseif ($isAccepted && !$isPaid) { ?>
    <!-- إذا الطلب مقبول لكن لم يتم الدفع -->
    <a href="#" class="upload-result-btn upload-disabled">رفع نتيجة التقديم </a>
<?php } else { ?>
    <!-- إذا الطلب غير مقبول -->
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