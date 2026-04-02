<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>بيانات الحساب</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css">

<style>

/* الهيدر */
.header{
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* العنوان */
.header h2{
    margin: 0;
    font-size: 22px;
}

/* زر بيانات الحساب */
.profile-btn{
    background: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid #ddd;
    font-size: 13px;
    color: #3E2454;
    text-decoration: none;
}

/* النص */
.page-header{
    margin-bottom: 20px;
}

.page-header p{
    font-size: 13px;
    color: #333;
    margin: 0;
}

/* الكروت */
.cards{
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 20px;
}

.card{
    background: #fff;
    padding: 18px;
    text-align: center;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.card p{
    margin-bottom: 8px;
    font-size: 14px;
}

/* الرقم */
.number{
    font-size: 22px;
    color: #70A0AF;
    font-weight: bold;
}

/* زر تسجيل الخروج */
.logout-btn{
    background: transparent;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* أيقونة تسجيل الخروج */
.logout-icon{
    width: 18px;
    height: 18px;
}

</style>
</head>

<body>

<div class="layout">

<!-- الشريط الجانبي -->
<aside class="sidebar">

    <div class="sidebar-top">

        <!-- الشعار -->
        <div class="sidebar-logo">
            <img src="شعار نورين.png">
        </div>

        <!-- القائمة -->
        <ul class="sidebar-menu">
            <li><a href="contracts_manage.php">إدارة العقود</a></li>
            <li><a href="complaints.php">الشكاوى والاستفسارات</a></li>
            <li><a href="users_manage.php">إدارة المستخدمين</a></li>
            <li><a href="entities_approval.php">اعتماد الجهات</a></li>
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

<!-- المحتوى -->
<div class="main-content">

    <!-- الهيدر -->
    <header class="header">
        <h2>بيانات الحساب</h2>
        <a href="admin_profile.php" class="profile-btn">بيانات الحساب</a>
    </header>

    <!-- النص -->
    <div class="page-header">
        <p>أهلاً بك، فيما يلي نظرة شاملة على حالة النظام وأبرز المؤشرات الحالية.</p>
    </div>

    <!-- الكروت -->
    <div class="cards">

        <div class="card">
            <p>إجمالي المستفيدين المسجلين</p>
            <div class="number">30</div>
        </div>

        <div class="card">
            <p>إجمالي المستثمرين المسجلين</p>
            <div class="number">18</div>
        </div>

        <div class="card">
            <p>إجمالي المكاتب الاستشارية المسجلة</p>
            <div class="number">15</div>
        </div>

        <div class="card">
            <p>شكاوى جديدة</p>
            <div class="number">16</div>
        </div>

        <div class="card">
            <p>شكاوى قيد المعالجة</p>
            <div class="number">13</div>
        </div>

        <div class="card">
            <p>طلبات بانتظار الاعتماد</p>
            <div class="number">2</div>
        </div>

    </div>

</div>

</div>

</body>
</html>