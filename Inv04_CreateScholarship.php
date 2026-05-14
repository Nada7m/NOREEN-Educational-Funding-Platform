<?php
session_start();
/* التحقق من تسجيل دخول المستثمر */
if(!isset($_SESSION['inv_id'])){
    header("Location: login.php"); // إذا مو مسجل دخول يرجعه لصفحة الدخول
    exit();
}
/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost","root","","noreen");
if(!$con){
    die("فشل الاتصال بقاعدة البيانات"); // إيقاف الصفحة إذا فشل الاتصال
}
/* ضبط ترميز اللغة العربية */
mysqli_set_charset($con,"utf8mb4");
/* رقم المستثمر الحالي من الجلسة */
$inv_id = $_SESSION['inv_id'];
/* جلب منح المستثمر الحالي فقط */
$sql = "SELECT scholarship_id, sch_name, sch_field, study_level, Specializations, app_deadline
        FROM scholarship_opps
        WHERE inv_id = '$inv_id'"; // يجيب فقط المنح الخاصة بهذا المستثمر

$result = mysqli_query($con, $sql); // تنفيذ الاستعلام ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>عرض المنح</title>
<!-- الخطوط -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- ملف التنسيق الرئيسي -->
<link rel="stylesheet" href="CSS01Layout.css?v=3">
<style>
/* زر إنشاء منحة */
.create-btn-box {
  margin-top: 10px;
}
.create-btn {background: #3E2454;color: white;padding: 10px 20px;border-radius: 4px;display: inline-flex;
  align-items: center; gap: 8px; font-size: 14px;font-weight: 600;text-decoration: none;}
.create-btn:hover {opacity: .95;}
/* ترتيب أعلى الصفحة */
.page-top {display: flex;justify-content: space-between;align-items: center;
  padding: 0 40px;margin-top: 15px;}
/* قائمة المنح */
.scholarships-list { padding: 0 40px 40px;}
/* توزيع الكروت */
.scholarships-grid {display: grid;grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 25px;align-items: stretch;}
/* في حال ما فيه منح */
.empty-state {text-align: center;margin-top: 220px;color: #cfcfcf;font-size: 28px;}
/* كرت المنحة */
.scholarship-card {background: #fff;border: 1.5px solid #E3E3E3;border-radius: 10px;
  padding: 25px;text-align: right;box-sizing: border-box;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex;flex-direction: column;
  height: 100%;margin-top: 10px;}
/* عنوان المنحة */
.scholarship-title {font-size: 18px;font-weight: 700;color: #3E2454;text-align: center;
  line-height: 1.8;min-height: 65px;display: flex;align-items: center;justify-content: center;margin-bottom: 18px;}
/* محتوى الكرت */
.card-content {display: flex;flex-direction: column;gap: 14px;flex: 1;}
/* كل عنصر داخل الكرت */
.card-item { display: flex; flex-direction: column; gap: 6px;}
/* اسم الحقل */
.card-label {color: #777;font-size: 14px;font-weight: 600;}
/* قيمة الحقل */
.card-value {color: #70A0AF;font-size: 16px;font-weight: 600;line-height: 1.8;word-break: break-word;}
/* التخصصات */
.specializations-block {
  min-height: 90px;}
/* خط فاصل */
.card-divider {border-top: 1px solid #E5E5E5;margin: 18px 0 16px;}
/* تاريخ التقديم */
.deadline-block { margin-top: auto; margin-bottom: 18px;}
/* زر عرض التفاصيل */
.details-btn {width: 100%;height: 46px;background: #70A0AF;color: #fff;border-radius: 6px;
  font-size: 16px; font-weight: 600; display: flex; align-items: center;justify-content: center;text-decoration: none;}
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
      <li><a href="Inv04_CreateScholarship.php" class="active">عرض المنح</a></li>
      <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
      <li><a href="Inv10_Payments.php">المدفوعات</a></li>
    </ul>
  </div>
  <!-- زر تسجيل الخروج -->
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
<!-- الهيدر -->
<header class="header">
  <div class="page-heading">
    <h1 class="page-title">عرض المنح</h1>
    <p class="page-description">صفحة تقديم عروض فرص المنح</p>
  </div>
  <!-- قائمة الإعدادات -->
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
<!-- زر إنشاء منحة -->
<div class="page-top">
  <div class="create-btn-box">
    <a href="Inv04_CreateScholarshipForm.php" class="create-btn">
      <span>+</span>
      <span>إنشاء عرض منحة جديدة</span>
    </a>
  </div>
</div>
<div class="scholarships-list">
<?php if(mysqli_num_rows($result) > 0){ ?> <!-- إذا فيه منح -->
  <div class="scholarships-grid">
    <?php while($row = mysqli_fetch_assoc($result)){ ?> <!-- المرور على كل منحة -->
      <div class="scholarship-card">
        <!-- اسم المنحة -->
        <div class="scholarship-title">
          <?php echo htmlspecialchars($row['sch_name']); ?>
        </div>
        <div class="card-content">
          <!-- المجال -->
          <div class="card-item">
            <div class="card-label">المجال الرئيسي:</div>
            <div class="card-value"><?php echo htmlspecialchars($row['sch_field']); ?></div>
          </div>
          <!-- الدرجة -->
          <div class="card-item">
            <div class="card-label">الدرجة المستهدفة:</div>
            <div class="card-value"><?php echo htmlspecialchars($row['study_level']); ?></div>
          </div>
          <!-- التخصصات -->
          <div class="card-item specializations-block">
            <div class="card-label">التخصصات الدقيقة:</div>
            <div class="card-value"><?php echo nl2br(htmlspecialchars($row['Specializations'])); ?></div>
          </div>
        </div>
        <div class="card-divider"></div>
        <!-- تاريخ التقديم -->
        <div class="card-item deadline-block">
          <div class="card-label">آخر موعد للتقديم:</div>
          <div class="card-value"><?php echo date("Y-m-d", strtotime($row['app_deadline'])); ?></div>
        </div>
        <!-- زر التفاصيل -->
        <a href="Inv05_ScholarshipsDetails.php?id=<?php echo $row['scholarship_id']; ?>" class="details-btn">
          عرض تفاصيل أكثر
        </a>
      </div>
    <?php } ?>
  </div>
<?php } else { ?> <!-- إذا ما فيه منح -->
  <div class="empty-state">
    لم تقم بنشر منحة حتى الآن
  </div>
<?php } ?>
</div>
</div>
</div>
</body>
</html>