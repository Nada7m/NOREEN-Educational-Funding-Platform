<?php
session_start();

/* التحقق من تسجيل دخول المكتب */
if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}

$conn->set_charset("utf8mb4");

/* رقم المكتب الحالي */
$office_id = $_SESSION['office_id'];

/* جلب بيانات المكتب */
$stmt = $conn->prepare("
    SELECT office_name, ccr_number, email, office_description, Bachelor_fee, Masters_fee, Phd_fee, phone
    FROM consulting_office
    WHERE office_id = ?
");
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $office = $result->fetch_assoc();
} else {
    die("لم يتم العثور على بيانات المكتب");
}

$stmt->close();

/* جلب الدول المرتبطة بالمكتب */
$countryStmt = $conn->prepare("
    SELECT con_name
    FROM office_country
    WHERE office_id = ?
");
$countryStmt->bind_param("i", $office_id);
$countryStmt->execute();
$countryResult = $countryStmt->get_result();

$countries = [];

while ($row = $countryResult->fetch_assoc()) {
    $countries[] = $row['con_name'];
}

$countryStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الملف الشخصي</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">
<style>
.page{
    padding:40px;
    font-family:"Noto Kufi Arabic", sans-serif;
}

.profile-box{
    background:#fff;
    max-width:900px;
    margin:auto;
    padding:35px 55px;
    border-radius:14px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

.profile-box h2{
    text-align:center;
    margin-bottom:15px;
    font-size:34px;
    color:#4b2a63;
    font-weight:700;
}

.line{
    width:100%;
    height:1px;
    background:#d8cde2;
    margin:0 auto 30px;
}

.profile-section{
    margin-bottom:30px;
    padding-bottom:22px;
    border-bottom:1px solid #eee;
}

.profile-section h3{
    margin-bottom:18px;
    font-size:24px;
    color:#6a3d8f;
    font-weight:700;
}

.profile-section p{
    margin:10px 0;
    font-size:15px;
    line-height:1.9;
    color:#000;
}

.profile-section label{
    font-weight:700;
}

.password-box{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.password-box p{
    margin:0;
    font-size:20px;
}

.countries-box{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top:10px;
}

.country-tag{
    background:#f3ecf9;
    color:#4b2a63;
    padding:8px 14px;
    border-radius:20px;
    font-size:14px;
    font-weight:600;
}

.edit-wrap{
    text-align:left;
    margin-top:25px;
}

.edit-btn{
    padding:12px 28px;
    border:none;
    border-radius:10px;
    background:#4b2a63;
    color:#fff;
    font-size:17px;
    font-family:"Noto Kufi Arabic", sans-serif;
    cursor:pointer;
    transition:0.3s;
}

.edit-btn:hover{
    background:#3d2251;
}
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
          <li><a href="Con00_MainPage.php" class="active">الرئيسية</a></li>
          <li><a href="Con0_AdmissionRequests.php">إدارة طلبات القبول</a></li>
          <li><a href="Con0_Consultations.php">الاستشارات</a></li>
          <li><a href="Con0_BeneficiaryRatings.php">تقييمات المستفيدين</a></li>
        </ul>

        </div>

   <div class="sidebar-bottom">
  <form action="logout.php" method="post">
    <button type="submit" class="logout-btn">
      <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
      <b>تسجيل الخروج</b>
    </button>
  </form>
</div>   </aside>

    <div class="main-content">

        <header class="header">
            <div class="page-title">الملف الشخصي</div>

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

            <div class="profile-box">

                <h2><?php echo ($office['office_name']); ?></h2>

                <div class="line"></div>

                <div class="profile-section">
                    <h3>بيانات المكتب</h3>

                    <p><label>اسم المكتب:</label> <?php echo ($office['office_name']); ?></p>
                    <p><label>السجل التجاري:</label> <?php echo ($office['ccr_number']); ?></p>
                    <p><label>رقم الهاتف:</label> <?php echo ($office['phone']); ?></p>
                    <p><label>وصف المكتب:</label> <?php echo ($office['office_description']); ?></p>

                    <p><label>الدول المتاحة:</label></p>

                <div class="countries-box">
<?php if (!empty($countries)) { ?>
    <?php foreach ($countries as $country) { ?>
        <div class="country-tag">
            <?php echo ($country); ?>
        </div>
    <?php } ?>
<?php } else { ?>
    <p>لا توجد دول مضافة</p>
<?php } ?>
</div>
                </div>

                <div class="profile-section">
                    <h3>رسوم الخدمات</h3>

                    <p><label>رسوم البكالوريوس:</label> <?php echo ($office['Bachelor_fee']); ?> ريال</p>
                    <p><label>رسوم الماجستير:</label> <?php echo ($office['Masters_fee']); ?> ريال</p>
                    <p><label>رسوم الدكتوراه:</label> <?php echo ($office['Phd_fee']); ?> ريال</p>
                </div>

                <div class="profile-section">
                    <h3>بيانات الحساب</h3>

                    <p><label>البريد الإلكتروني:</label> <?php echo ($office['email']); ?></p>

                    <div class="password-box">
                        <label>كلمة المرور:</label>
                        <p>********</p>
                    </div>
                </div>

                <div class="edit-wrap">
                    <button type="button" class="edit-btn" onclick="window.location.href='Off03_EditProfile.php'">
                        تعديل البيانات
                    </button>
                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>