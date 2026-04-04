<?php
session_start();

/* التحقق من تسجيل دخول المكتب */
if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الملف الشخصي</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">
<style>

</style>
</head>

<body>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-top">

            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="شعار نورين">
            </div>

            <ul class="sidebar-menu">
                <li><a href="Con00_MainPage.php" >الرئيسية</a></li>
                <li><a href="Con04_AdmissionReq.php">إدارة طلبات القبول</a></li>
                <li><a href="Con0_Consultations.php">الاستشارات</a></li>
                <li><a href="Con08_ReqRating.php"class="active">تقييمات المستفيدين</a></li>
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
                <div class="page-title">تقييمات المستفيدين</div>
                <div class="page-description">تقييمات المستفيدين لخدمة إصدار القبول الجامعي</div>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="القائمة">

                    <div class="dropdown-menu">
                        <a href="Off02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page">

        </div>

    </div>

</div>

</body>
</html>