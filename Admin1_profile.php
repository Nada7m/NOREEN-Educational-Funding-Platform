<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");
/* إحصائيات النظام   */

/* حساب إجمالي المستفيدين */
$beneficiaries_count = 0;
$q1 = mysqli_query($con, "SELECT COUNT(bnf_id) AS total FROM beneficiary");
if ($q1) {
    $row1 = mysqli_fetch_assoc($q1);
    $beneficiaries_count = $row1['total'];
}

/* حساب إجمالي المستثمرين */
$investors_count = 0;
$q2 = mysqli_query($con, "SELECT COUNT(inv_id) AS total FROM investor");
if ($q2) {
    $row2 = mysqli_fetch_assoc($q2);
    $investors_count = $row2['total'];
}

/* حساب إجمالي المكاتب الاستشارية */
$offices_count = 0;
$q3 = mysqli_query($con, "SELECT COUNT(office_id) AS total FROM consulting_office");
if ($q3) {
    $row3 = mysqli_fetch_assoc($q3);
    $offices_count = $row3['total'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>بيانات الحساب</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS_AdminLayout.css?v=3">

<style>
/* الحاوية العامة */
.page-wrapper{ padding:40px 45px; }

/* صندوق الترحيب */
.welcome-box{ text-align:center; margin-bottom:30px; }

/* نص الترحيب */
.welcome-box p{ margin:0; font-size:18px; color:#222222; line-height:2; }

/* شبكة الكروت */
.cards{ display:grid; grid-template-columns:repeat(3,1fr); gap:22px; max-width:820px; margin:0 auto; }

/* الكرت */
.card{ background:#FFFFFF; min-height:200px; border:1px solid #ECE6E6; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; text-align:center; padding:22px 15px; border-radius:12px; }

/* عنوان الكرت */
.card-title{ font-size:16px; font-weight:700; color:#111111; line-height:1.8; min-height:60px; }

/* رقم الإحصائية */
.card-number{ margin-top:35px; font-size:38px; font-weight:700; color:#70A0AF; line-height:1; }

</style>
</head>
<body>

<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-logo">
        <img src="شعار نورين بنفسجي.svg" alt="نورين">
      </div>

      <ul class="sidebar-menu">
        <li><a href="Admin2_EntitiesApproval.php">اعتماد الجهات</a></li>
        <li><a href="Admin3_Contracts.php">إدارة العقود</a></li>
        <li><a href="Admin4_UsersManage.php">إدارة المستخدمين</a></li>
        <li><a href="Admin5_Complaints.php">الشكاوى والاستفسارات</a></li>
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
        <div class="page-title">بيانات الحساب</div>
        <div class="page-description">عرض إحصائي مختصر للمستخدمين المسجلين في النظام</div>
      </div>
      <div class="header-left">
        <a href="Admin1_profile.php" class="profile-btn">لوحة التحكم</a>
      </div>
    </header>

    <div class="page">
      <div class="page-wrapper">
        <div class="welcome-box">
          <p>
            أهلاً بك،<br>
            فيما يلي إحصائية بعدد المستخدمين المسجلين في النظام.
          </p>
        </div>
        <div class="cards">
          <div class="card">
            <div class="card-title">إجمالي المستفيدين المسجلين</div>
            <div class="card-number"><?php echo $beneficiaries_count; ?></div>
          </div>
          <div class="card">
            <div class="card-title">إجمالي المستثمرين المسجلين</div>
            <div class="card-number"><?php echo $investors_count; ?></div>
          </div>
          <div class="card">
            <div class="card-title">إجمالي المكاتب الاستشارية المسجلة</div>
            <div class="card-number"><?php echo $offices_count; ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>