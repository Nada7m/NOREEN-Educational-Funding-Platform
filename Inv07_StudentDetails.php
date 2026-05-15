<?php
session_start();

/* التحقق من تسجيل دخول المستثمر */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

/** التحقق من نجاح الاتصال **/
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* ضبط الترميز لدعم اللغة العربية */
mysqli_set_charset($con, "utf8mb4");

/* الحصول على رقم المستثمر الحالي */
$inv_id = $_SESSION['inv_id'];

/** التحقق من وجود رقم الطلب في الرابط **/
if (!isset($_GET['request_id']) || $_GET['request_id'] == "") {
    die("رقم الطلب غير موجود.");
}

/* تحويل رقم الطلب إلى عدد صحيح */
$request_id = (int)$_GET['request_id'];

/* دالة تجهيز رابط الملف */
function file_link($file){

    /* حذف الفراغات */
    $file = trim($file);

    /** التحقق إذا كان الملف فارغ **/
    if($file == ""){
        return "";
    }

    /** التحقق إذا كان الرابط جاهز **/
    if(strpos($file, 'uploads/') === 0 || strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0){
        return $file;
    }

    /* إضافة مسار uploads */
    return 'uploads/' . $file;
}

/* جلب بيانات الطلب مع بيانات المستفيد وبيانات المنحة */
$sql = "SELECT
scholarship_requests.request_id,
scholarship_requests.bnf_id,
scholarship_requests.scholarship_id,
scholarship_requests.univ_name,
scholarship_requests.major_name,
scholarship_requests.request_status,
scholarship_requests.Submit_date,
beneficiary.f_name,
beneficiary.l_name,
beneficiary.phone_num,
beneficiary.email,
beneficiary.degree_level,
beneficiary.sch_field,
scholarship_opps.sch_name
FROM scholarship_requests
INNER JOIN beneficiary ON scholarship_requests.bnf_id = beneficiary.bnf_id
INNER JOIN scholarship_opps ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
WHERE scholarship_requests.request_id = ?
AND scholarship_opps.inv_id = ?";

/* تجهيز الاستعلام */
$stmt = mysqli_prepare($con, $sql);

/* ربط المتغيرات بالاستعلام */
mysqli_stmt_bind_param($stmt, "ii", $request_id, $inv_id);

/* تنفيذ الاستعلام */
mysqli_stmt_execute($stmt);

/* جلب النتيجة */
$result = mysqli_stmt_get_result($stmt);

/* تحويل البيانات إلى مصفوفة */
$row = mysqli_fetch_assoc($result);

/** التحقق إذا كانت البيانات موجودة **/
if (!$row) {
    die("لم يتم العثور على بيانات الطلب.");
}

/* جلب ملفات الطلب من جدول scholarship_request_documents */
$documents = [];

/* تجهيز استعلام الملفات */
$doc_stmt = mysqli_prepare($con, "SELECT doc_type, file_name, file FROM scholarship_request_documents WHERE request_id = ? ORDER BY doc_id ASC");

/* ربط رقم الطلب */
mysqli_stmt_bind_param($doc_stmt, "i", $request_id);

/* تنفيذ الاستعلام */
mysqli_stmt_execute($doc_stmt);

/* جلب النتائج */
$doc_result = mysqli_stmt_get_result($doc_stmt);

/* تخزين الملفات داخل المصفوفة */
while ($doc_row = mysqli_fetch_assoc($doc_result)) {
    $documents[] = $doc_row;
}

/* تجهيز متغيرات الملفات */
$cv_file = "";
$certificate_file = "";
$recommendation_file = "";
$acceptance_file = "";

/* توزيع الملفات حسب النوع */
foreach ($documents as $doc) {

    /** التحقق إذا كان الملف سيرة ذاتية **/
    if ($doc["doc_type"] == "CV") {

        $cv_file = $doc["file"];

    /** التحقق إذا كان الملف شهادة **/
    } elseif ($doc["doc_type"] == "Certificate") {

        $certificate_file = $doc["file"];

    /** التحقق إذا كان الملف توصية **/
    } elseif ($doc["doc_type"] == "Recommendation") {

        $recommendation_file = $doc["file"];

    /** التحقق إذا كان الملف قبول **/
    } elseif ($doc["doc_type"] == "Acceptance") {

        $acceptance_file = $doc["file"];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>عرض بيانات المستفيد</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">
<style>
/* تنسيق زر الرجوع */
.back-btn{
width:34px;
height:34px;
display:flex;
align-items:center;
justify-content:center;
}
/* تنسيق صورة السهم */
.back-icon{
width:24px;
height:24px;
object-fit:contain;
}
/* تنسيق الصفحة الداخلية */
.student-page{
padding:30px 40px 60px;
}
/* عنوان كل قسم */
.section-title{
font-size:18px;
font-weight:700;
color:#111111;
margin-bottom:14px;
text-align:right;
}
/* توزيع الصندوقين داخل الصفحة */
.data-grid{
display:flex;
gap:18px;
align-items:flex-start;
}
/* تنسيق الصندوق العام */
.data-box{
background:#FFFFFF;
border:1px solid #E3E3E3;
border-radius:8px;
padding:22px;
box-shadow:0 3px 10px rgba(0,0,0,0.05);
}
/* الصندوق الأيمن لبيانات المستفيد */
.right-box{
width:34%;
}
/* الصندوق الأيسر لبيانات الطلب */
.left-box{
width:66%;
}
/* تنسيق صف المعلومة */
.info-row{
display:flex;
justify-content:space-between;
align-items:flex-start;
margin-bottom:16px;
gap:16px;
}
/* اسم الحقل */
.info-label{
font-size:16px;
font-weight:700;
color:#7EA8B7;
min-width:200px;
}
/* قيمة الحقل */
.info-value{
font-size:16px;
color:#555555;
line-height:1.9;
flex:1;
}
/* تنسيق روابط الملفات */
.file-link{
color:#3B61B8;
text-decoration:underline;
display:inline-block;
margin-bottom:14px;
word-break:break-word;
}
/* إذا لم يوجد ملف */
.no-file{
color:#777777;
}
/* ضبط شكل التاريخ */
.date-fix{
direction:ltr;
unicode-bidi:isolate;
display:inline-block;
}
/* في الشاشات الصغيرة تصبح الصناديق تحت بعض */
@media (max-width: 950px){
.data-grid{
flex-direction:column;
}
.right-box,.left-box{
width:100%;
}
}
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
<li><a href="Inv00_MainPage.php">الرئيسية</a></li>
<li><a href="Inv06_ManageScholarships.php" class="active">إدارة المنح</a></li>
<li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
<li><a href="Inv10_Payments.php">المدفوعات</a></li>
</ul>
</div>
<div class="sidebar-bottom">
<form action="logout.php" method="post">
<button type="submit" class="logout-btn">
<img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="تسجيل الخروج">
<b>تسجيل الخروج</b>
</button>
</form>
</div>
</aside>
<div class="main-content">
<header class="header">
    <div class="page-heading">
<h1 class="page-title">إدارة المنح</h1>
<p class="page-description">صفحة استعراض بيانات التقديم على المنحة</p>
</div>
<div class="header-icons">
<div class="settings-dropdown">
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
<div class="dropdown-menu">
<a href="Inv02_Profile.php">الملف الشخصي</a>
<a href="support.php">تقديم شكوى او استفسار</a>
</div>
</div>
</div>
</header>
<section class="page">
<div class="page-top">
<div></div>
<div class="back-btn">
<a href="Inv06_ManageScholarships.php">
        <img src="سهم تراجع.svg" width="40">
</a>
</div>
</div>
<div class="student-page">
<div class="data-grid">
<div class="data-box right-box">
<div class="section-title">البيانات الشخصية للمستفيد:</div>
<div class="info-row">
<div class="info-label">الاسم:</div>
<div class="info-value"><?php echo htmlspecialchars($row['f_name'])." ".htmlspecialchars($row['l_name']); ?></div>
</div>
<div class="info-row">
<div class="info-label">المؤهل:</div>
<div class="info-value"><?php echo htmlspecialchars($row['degree_level']); ?></div>
</div>
<div class="info-row">
<div class="info-label">التخصص:</div>
<div class="info-value"><?php echo htmlspecialchars($row['sch_field']); ?></div>
</div>
<div class="info-row">
<div class="info-label">رقم الهاتف:</div>
<div class="info-value"><?php echo htmlspecialchars($row['phone_num']); ?></div>
</div>
<div class="info-row">
<div class="info-label">البريد الإلكتروني:</div>
<div class="info-value"><?php echo htmlspecialchars($row['email']); ?></div>
</div>
</div>
<div class="data-box left-box">
<div class="section-title">بيانات التقديم على المنحة المعروضة</div>
<div class="info-row">
<div class="info-label">رقم الطلب:</div>
<div class="info-value">#<?php echo htmlspecialchars($row['request_id']); ?></div>
</div>
<div class="info-row">
<div class="info-label">التخصص:</div>
<div class="info-value"><?php echo htmlspecialchars($row['major_name']); ?></div>
</div>
<div class="info-row">
<div class="info-label">الجامعة:</div>
<div class="info-value"><?php echo htmlspecialchars($row['univ_name']); ?></div>
</div>
<div class="info-row">
<div class="info-label">المنحة:</div>
<div class="info-value"><?php echo htmlspecialchars($row['sch_name']); ?></div>
</div>
<div class="info-row">
<div class="info-label">حالة الطلب:</div>
<div class="info-value"><?php echo htmlspecialchars($row['request_status']); ?></div>
</div>
<div class="info-row">
<div class="info-label">تاريخ التقديم:</div>
<div class="info-value"><span class="date-fix"><?php echo date("d-m-Y", strtotime($row['Submit_date'])); ?></span></div>
</div>
<div class="info-row">
<div class="info-label">السيرة الذاتية:</div>
<div class="info-value">
<?php if ($cv_file != ""): ?>
<a class="file-link" href="<?php echo htmlspecialchars(file_link($cv_file)); ?>" target="_blank"><?php echo htmlspecialchars($cv_file); ?></a>
<?php else: ?>
<span class="no-file">لا يوجد ملف</span>
<?php endif; ?>
</div>
</div>
<div class="info-row">
<div class="info-label">الشهادة الجامعية / شهادة آخر مؤهل:</div>
<div class="info-value">
<?php if ($certificate_file != ""): ?>
<a class="file-link" href="<?php echo htmlspecialchars(file_link($certificate_file)); ?>" target="_blank"><?php echo htmlspecialchars($certificate_file); ?></a>
<?php else: ?>
<span class="no-file">لا يوجد ملف</span>
<?php endif; ?>
</div>
</div>
<div class="info-row">
<div class="info-label">خطابات التوصية (التركية):</div>
<div class="info-value">
<?php if ($recommendation_file != ""): ?>
<a class="file-link" href="<?php echo htmlspecialchars(file_link($recommendation_file)); ?>" target="_blank"><?php echo htmlspecialchars($recommendation_file); ?></a>
<?php else: ?>
<span class="no-file">لا يوجد ملف</span>
<?php endif; ?>
</div>
</div>
<div class="info-row">
<div class="info-label">خطاب القبول الجامعي من الجامعة المرغوبة:</div>
<div class="info-value">
<?php if ($acceptance_file != ""): ?>
<a class="file-link" href="<?php echo htmlspecialchars(file_link($acceptance_file)); ?>" target="_blank"><?php echo htmlspecialchars($acceptance_file); ?></a>
<?php else: ?>
<span class="no-file">لا يوجد ملف</span>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>
</section>
</div>
</div>
</body>
</html>