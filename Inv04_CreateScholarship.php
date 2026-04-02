<?php
session_start();

/* التحقق من تسجيل دخول المستثمر */
if(!isset($_SESSION['inv_id'])){
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost","root","","noreen");

if(!$con){
    die("فشل الاتصال بقاعدة البيانات");
}

/* ضبط ترميز اللغة العربية */
mysqli_set_charset($con,"utf8mb4");

/* رقم المستثمر الحالي من الجلسة */
$inv_id = $_SESSION['inv_id'];

/* جلب منح المستثمر الحالي فقط */
$sql = "SELECT scholarship_id, sch_name, sch_field, study_level, Specializations, app_deadline
        FROM scholarship_opps
        WHERE inv_id = '$inv_id'";

$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>عرض المنح</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">
</head>

<body>

<div class="layout">

<!-- الشريط الجانبي -->
<aside class="sidebar">
  <div class="sidebar-top">

    <div class="sidebar-logo">
      <img src="شعار نورين.png" alt="نورين">
    </div>

    <ul class="sidebar-menu">
      <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
      <li><a href="Inv04_CreateScholarship.php" class="active">عرض المنح</a></li>
       <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
      <li><a href="#">المدفوعات</a></li>
    </ul>

  </div>

  <div class="sidebar-bottom">
    <form action="logout.php" method="post">
      <button type="submit" class="logout-btn">
        <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
        <b>تسجيل الخروج</b>
      </button>
    </form>
  </div>
</aside>

<!-- المحتوى -->
<div class="main-content">

<header class="header">

  <!-- الإعدادات (يسار) -->
  <div class="header-icons">
    <div class="settings-dropdown">
      <img src="ايقونة قائمة الاعدادات.png" class="menu-icon">

      <div class="dropdown-menu">
        <a href="Inv02_Profile.php">الملف الشخصي</a>
        <a href="support.php">تقديم شكوى او استفسار</a>
      </div>
    </div>
  </div>

  <!-- العنوان (يمين) -->
  <div class="page-heading">
    <h1 class="page-title">عرض المنح</h1>
    <p class="page-description">صفحة تقديم عروض فرص المنح</p>
  </div>

</header>

<!-- زر -->
<div class="page-top">
  <div class="create-btn-box">
    <a href="Inv04_CreateScholarshipForm.php" class="create-btn">
      <span>+</span>
      <span>إنشاء عرض منحة جديدة</span>
    </a>
  </div>
</div>

<!-- الكروت -->
<div class="scholarships-list">

<?php if(mysqli_num_rows($result) > 0){ ?>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<div class="scholarship-card">

<h2 class="scholarship-title">
<?php echo htmlspecialchars($row['sch_name']); ?>
</h2>

<div class="info-row">
  <div>
    <div class="label">المجال الرئيسي:</div>
    <div class="value"><?php echo htmlspecialchars($row['sch_field']); ?></div>
  </div>

  <div>
    <div class="label">الدرجة المستهدفة:</div>
    <div class="value"><?php echo htmlspecialchars($row['study_level']); ?></div>
  </div>
</div>

<div class="specializations">
  <div class="label">التخصصات الدقيقة:</div>
  <p><?php echo nl2br(htmlspecialchars($row['Specializations'])); ?></p>
</div>

<div class="divider"></div>

<div class="deadline">
  <span>آخر موعد للتقديم: <?php echo date("Y-m-d", strtotime($row['app_deadline'])); ?></span>
</div>

<a href="Inv05_ScholarshipsDetails.php?id=<?php echo $row['scholarship_id']; ?>" class="details-btn">
عرض تفاصيل أكثر
</a>

</div>

<?php } ?>

<?php } else { ?>

<div class="empty-state">
لم تقم بنشر منحة حتى الآن
</div>

<?php } ?>

</div>

</div>
</div>

</body>
</html>