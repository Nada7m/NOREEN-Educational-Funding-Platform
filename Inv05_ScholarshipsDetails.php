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
        <button class="logout-btn">
          <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="تسجيل الخروج">
          <span>تسجيل الخروج</span>
        </button>
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

    
       <section class="page">
      <div class="page-top">
        <div></div>
        <div class="back-btn">
          <a href="Inv04_CreateScholarship.php">
            <img src="سهم تراجع.svg" alt="رجوع" class="back-icon">
          </a>
        </div>
      </div>

        <div class="tabs-row">
          <div class="tab tab-active">تفاصيل المنحة</div>
          <div class="tab">تفاصيل المتقدمين</div>
        </div>

        <div class="scholarship-details-box">

          <div class="top-info-row">

            <div class="deadline-box">
              <span class="deadline-icon">🗓</span>
              <span class="deadline-label">آخر موعد للتقديم:</span>
              <span class="deadline-value">15 أبريل 2026</span>
            </div>

            <div class="main-info-box">
              <h2 class="main-title">برنامج سابك - تطوير الأنظمة الصناعية المتقدمة</h2>

              <div class="double-info-row">
                <div class="info-item">
                  <span class="info-label">المجال الرئيسي:</span>
                  <span class="info-value">صناعي وتشغيلي</span>
                </div>

                <div class="info-item">
                  <span class="info-label">الدرجة المستهدفة:</span>
                  <span class="info-value">ماجستير</span>
                </div>
              </div>

              <div class="specialization-line">
                <span class="specialization-label">التخصصات الدقيقة:</span>
                <span class="specialization-value">تحسين العمليات الصناعية، سلاسل الإمداد، نظم الدعم الشاملة</span>
              </div>
            </div>

          </div>

          <div class="section-divider"></div>

          <div class="conditions-box">
            <h3 class="conditions-title">الشروط:</h3>
            <ul class="conditions-list">
              <li>أن يكون المتقدم سعودي الجنسية</li>
              <li>حاصل على بكالوريوس في الهندسة الصناعية بمعدل لا يقل عن 3.5 من 4</li>
              <li>خبرة عملية لا تقل عن سنة في مجال ذات صلة (للماجستير)</li>
              <li>الالتزام بالعمل في سابك بعد التخرج لمدة محددة</li>
            </ul>
          </div>

        </div>
      </div>

    </div>