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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>بيانات الحساب</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css">

<style>
body{
    margin:0;
    background:#FFFDFB;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.layout{
    display:flex;
    min-height:100vh;
}

/* المحتوى */
.main-content{
    flex:1;
    background:#FFFDFB;
}

/* الهيدر العلوي */
.header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    padding:28px 34px 20px;
    border-bottom:1px solid #d3d3d3;
    background:#FFFDFB;
}

.page-heading{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.page-title{
    font-size:22px;
    font-weight:800;
    color:#3E2454;
}

.page-subtitle{
    font-size:16px;
    color:#333;
    line-height:1.9;
}

/* زر بيانات الحساب */
.profile-btn{
    background:#F3ECDD;
    color:#78A7B8;
    border:1px solid #bfb6a7;
    border-radius:18px;
    padding:8px 18px;
    text-decoration:none;
    font-size:14px;
    font-weight:700;
    box-shadow:0 1px 2px rgba(0,0,0,0.08);
}

/* محتوى الصفحة */
.page-wrapper{
    padding:45px 55px;
}

/* نص الترحيب */
.welcome-box{
    text-align:center;
    margin-bottom:35px;
}

.welcome-box p{
    margin:0;
    font-size:18px;
    color:#222;
    line-height:2;
}

/* الكروت */
.cards{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:35px;
    max-width:980px;
    margin:0 auto;
}

.card{
    background:#fff;
    min-height:250px;
    border:1px solid #ece6e6;
    box-shadow:0 2px 8px rgba(0,0,0,0.06);
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:flex-start;
    text-align:center;
    padding:28px 20px;
}

.card-title{
    font-size:17px;
    font-weight:700;
    color:#111;
    line-height:1.9;
    min-height:70px;
}

.card-number{
    margin-top:55px;
    font-size:18px;
    font-weight:700;
    color:#76A8B9;
}

@media (max-width:1100px){
    .cards{
        grid-template-columns:1fr;
        max-width:450px;
    }

    .page-wrapper{
        padding:30px 20px;
    }

    .header{
        padding:20px;
        flex-direction:column;
        gap:15px;
        align-items:flex-start;
    }
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

            <ul class="sidebar-menu">
                <li><a href="Admin1_profile.php" class="active">الملف الشخصي</a></li>
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

    <!-- المحتوى -->
    <div class="main-content">

        <!-- الهيدر -->
        <header class="header">
            <div class="page-heading">
                <div class="page-title">بيانات الحساب</div>
                        </div>

            <a href="Admin1_profile.php" class="profile-btn">بيانات الحساب</a>
        </header>

        <!-- محتوى الصفحة -->
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

</body>
</html>