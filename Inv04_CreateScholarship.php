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

<style>
/* زر إنشاء منحة */
.create-btn-box{
  margin-top:10px;
}

.create-btn{
  background:#3E2454;
  color:white;
  padding:10px 20px;
  border-radius:4px;
  display:inline-flex;
  align-items:center;
  gap:8px;
  font-size:14px;
  font-weight:600;
}

.create-btn:hover{
  opacity:.95;
}

/* مكان السهم وزر الإنشاء */
.page-top{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:0 40px;
  margin-top:15px;
}

/* الكروت */
.scholarships-list{
  padding:0 40px 40px;
  position:relative;
}

.empty-state{
  text-align:center;
  margin-top:220px;
  color:#cfcfcf;
  font-size:28px;
  position:absolute;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%);
}

.scholarship-card{
  width:460px;
  background:#fff;
  border:1.5px solid #E3E3E3;
  border-radius:6px;
  padding:32px 28px 24px;
  margin:20px 0 20px auto;
  text-align:right;
  box-sizing:border-box;
}

.scholarship-title{
  font-size:20px;
  font-weight:700;
  color:#3E2454;
  text-align:center;
  margin-bottom:22px;
}

.info-row{
  display:flex;
  justify-content:space-between;
  margin-bottom:20px;
}

.label{
  font-size:16px;
  color:#777;
  margin-bottom:6px;
}

.value{
  font-size:18px;
  font-weight:600;
  color:#70A0AF;
}

.specializations{
  margin-top:10px;
  margin-bottom:22px;
}

.specializations .label{
  margin-bottom:8px;
}

.specializations p{
  color:#70A0AF;
  font-size:16px;
  line-height:2;
  margin:0;
}

.divider{
  border-top:1px solid #E5E5E5;
  margin:22px 0 16px;
}

.deadline{
  display:flex;
  justify-content:flex-start;
  align-items:center;
  gap:6px;
  font-size:16px;
  color:#555;
  margin-bottom:18px;
}

.details-btn{
  width:100%;
  height:46px;
  background:#70A0AF;
  color:#fff;
  border-radius:6px;
  font-size:16px;
  font-weight:600;
  display:flex;
  align-items:center;
  justify-content:center;
  text-decoration:none;

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
      <li><a href="Inv04_CreateScholarship.php" class="active">عرض المنح</a></li>
      <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
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
    <h1 class="page-title">عرض المنح</h1>
    <p class="page-description">صفحة تقديم عروض فرص المنح</p>
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

<div class="page-top">

  <div class="create-btn-box">
    <a href="Inv04_CreateScholarshipForm.php" class="create-btn">
      <span>+</span>
      <span>إنشاء عرض منحة جديدة</span>
    </a>
  </div>
</div>

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