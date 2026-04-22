<?php
session_start();

/* التحقق من تسجيل دخول المستثمر */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php"); // تحويل لصفحة الدخول إذا ما فيه جلسة
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost","root","","noreen");

if(!$con){
    die("فشل الاتصال بقاعدة البيانات"); // إيقاف الصفحة إذا فشل الاتصال
}

/* ضبط الترميز العربي */
mysqli_set_charset($con,"utf8mb4");

/* رقم المستثمر الحالي */
$inv_id=$_SESSION['inv_id'];

/* جلب الطلبات المقبولة فقط لهذا المستثمر */
$sql="SELECT scholarship_requests.request_id,
      scholarship_requests.bnf_id,
      beneficiary.f_name,
      beneficiary.l_name,
      scholarship_opps.sch_name
      FROM scholarship_requests
      INNER JOIN scholarship_opps
      ON scholarship_requests.scholarship_id=scholarship_opps.scholarship_id
      INNER JOIN beneficiary
      ON scholarship_requests.bnf_id=beneficiary.bnf_id
      WHERE scholarship_opps.inv_id=?
      AND scholarship_requests.request_status='مقبول'
      ORDER BY scholarship_requests.request_id DESC";

$stmt=mysqli_prepare($con,$sql);
mysqli_stmt_bind_param($stmt,"i",$inv_id); // تمرير رقم المستثمر
mysqli_stmt_execute($stmt);
$result=mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إدارة المنح</title>

<!-- الخط -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- التنسيق العام -->
<link rel="stylesheet" href="CSS01Layout.css?v=4">

<style>

/* مساحة الصفحة */
.manage-page{padding:35px;}

/* كرت المستفيد */
.accepted-card{
    width:100%;
    background:#FFFFFF;
    border:1px solid #CFCFCF;
    border-radius:8px;
    padding:20px;
    display:flex;
    flex-direction:row-reverse;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    box-sizing:border-box;
}

/* معلومات المستفيد */
.accepted-info{
    display:flex;
    flex-direction:column;
    gap:10px;
    text-align:right;
}

/* نص المعلومات */
.accepted-line{
    font-size:20px;
    font-weight:700;
    color:#111111;
}

/* عنوان الحقل */
.accepted-label{
    color:#7EA8B7;
    margin-left:6px;
}

/* الأزرار */
.accepted-actions{
    display:flex;
    flex-direction:column;
    gap:12px;
    align-items:center;
}

/* شكل الأزرار */
.manage-btn{
    width:220px;
    height:55px;
    border:none;
    border-radius:6px;
    color:#FFFFFF;
    font-size:16px;
    font-weight:700;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    text-decoration:none;
    font-family:"Noto Kufi Arabic",sans-serif;
    box-sizing:border-box;
}

/* ألوان الأزرار */
.btn-view{background:#77A7B8;}
.btn-contact{background:#C5A8D6;}
.btn-contract{background:#472764;}

/* رسالة إذا ما فيه بيانات */
.empty-manage{
    text-align:center;
    font-size:22px;
    color:#777777;
    margin-top:60px;
}

</style>
</head>

<body>

<div class="layout">

<!-- الشريط الجانبي -->
<aside class="sidebar">

<div class="sidebar-top">

<div class="sidebar-logo">
<img src="شعار نورين.png" alt="نورين">
</div>

<!-- روابط الصفحات -->
<ul class="sidebar-menu">
<li><a href="Inv00_MainPage.php">الرئيسية</a></li>
<li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
<li><a href="Inv06_ManageScholarships.php" class="active">إدارة المنح</a></li>
<li><a href="Inv10_Payments.php">المدفوعات</a></li>
</ul>

</div>

<!-- تسجيل الخروج -->
<div class="sidebar-bottom">
<form action="logout.php" method="post">
<button type="submit" class="logout-btn">
<img src="ايقونة تسجيل الخروج.png" class="logout-icon">
<b>تسجيل الخروج</b>
</button>
</form>
</div>

</aside>

<div class="main-content">

<!-- الهيدر -->
<header class="header">

<div class="page-heading">
<h1 class="page-title">إدارة المنح</h1>
<p class="page-description">صفحة التواصل مع المرشح المعني بالمنحة</p>
</div>

<div class="header-icons">
<div class="settings-dropdown">
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon">

<div class="dropdown-menu">
<a href="Inv02_Profile.php">الملف الشخصي</a>
<a href="support.php">تقديم شكوى او استفسار</a>
</div>

</div>
</div>

</header>

<div class="manage-page">

<?php if(mysqli_num_rows($result)>0): ?> <!-- إذا فيه بيانات -->

<?php while($row=mysqli_fetch_assoc($result)): ?> <!-- المرور على كل مستفيد -->

<div class="accepted-card">

<!-- الأزرار -->
<div class="accepted-actions">

<!-- عرض بيانات الطالب -->
<a href="Inv07_StudentDetails.php?request_id=<?php echo $row['request_id']; ?>" 
class="manage-btn btn-view">عرض البيانات</a>

<!-- التواصل -->
<a href="Inv08_ContactBeneficiary.php?bnf_id=<?php echo $row['bnf_id']; ?>" 
class="manage-btn btn-contact">التواصل</a>

<!-- إنشاء العقد -->
<a href="Inv09_create_contract.php?request_id=<?php echo $row['request_id']; ?>" 
class="manage-btn btn-contract">العقد الإلكتروني</a>

</div>

<!-- معلومات الطالب -->
<div class="accepted-info">

<div class="accepted-line">
<span class="accepted-label">الاسم:</span>
<?php echo htmlspecialchars($row['f_name'])." ".htmlspecialchars($row['l_name']); ?>
</div>

<div class="accepted-line">
<span class="accepted-label">رقم الطلب:</span>
#<?php echo htmlspecialchars($row['request_id']); ?>
</div>

<div class="accepted-line">
<span class="accepted-label">المنحة:</span>
<?php echo htmlspecialchars($row['sch_name']); ?>
</div>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<!-- في حال لا يوجد مستفيدين -->
<div class="empty-manage">
لا يوجد مرشحون مقبولون حالياً
</div>

<?php endif; ?>

</div>
</div>
</div>

</body>
</html>