<?php
session_start();

if(!isset($_SESSION['inv_id'])){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>عرض المنح</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="CSS01Layout.css">
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
          <li><a href="#">إدارة المنح</a></li>
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
              <a href="#">التواصل والدعم</a>
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


    </div>
<div class="scholarship-card">

  <h2 class="scholarship-title">
    برنامج سابك - تطوير الأنظمة الصناعية المتقدمة
  </h2>

  <div class="info-row">
    <div>
      <div class="label">المجال الرئيسي:</div>
      <div class="value">صناعي وتشغيلي</div>
    </div>

    <div>
      <div class="label">الدرجة المستهدفة:</div>
      <div class="value">ماجستير</div>
    </div>
  </div>

  <div class="specializations">
    <div class="label">التخصصات الدقيقة:</div>
    <p>تحسين العمليات الصناعية.</p>
    <p>سلاسل الإمداد، نظم الدعم الشاملة</p>
  </div>

  <div class="divider"></div>

  <div class="deadline">
    <span>آخر موعد للتقديم: 15 أبريل 2026</span>
    <span>📅</span>
  </div>

  <a href="Inv05_ScholarshipsDetails.php" class="details-btn">
عرض تفاصيل أكثر
</a>

</div>
  </div>

</body>
</html>