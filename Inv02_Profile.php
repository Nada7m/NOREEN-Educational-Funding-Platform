<?php
session_start();
/* التحقق من تسجيل دخول المستثمر */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");}

/* دعم العربية */
$conn->set_charset("utf8mb4");

/* جلب رقم المستثمر من الجلسة */
$inv_id = $_SESSION['inv_id'];

/* استعلام جلب بيانات المستثمر */
$stmt = $conn->prepare("
    SELECT inv_name, ccr_number, inv_number, email
    FROM investor
    WHERE inv_id = ?");

$stmt->bind_param("i", $inv_id);
$stmt->execute();
$result = $stmt->get_result();

/* التحقق من وجود البيانات */
if ($result->num_rows > 0) {
    $investor = $result->fetch_assoc();
} else {
    die("لم يتم العثور على بيانات المستثمر");
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الملف الشخصي</title>
<!-- الخط -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- ملف التنسيق الأساسي المشترك -->
<link rel="stylesheet" href="CSS01Layout.css?v=4">
<style>
/* مساحة الصفحة الداخلية */
.page{
    padding:40px;
    font-family:"Noto Kufi Arabic", sans-serif;
}

/* صندوق الملف الشخصي */
.profile-box{
    background:#fff;
    max-width:650px;
    margin:auto;
    padding:35px;
    border-radius:14px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

/* اسم الشركة */
.profile-box h2{
    text-align:center;
    margin-bottom:15px;
    font-size:24px;
    color:#4b2a63;
}

/* الخط الفاصل */
.line{
    width:100%;
    height:1px;
    background:#d8cde2;
    margin:0 auto 25px;
}

/* كل قسم داخل الصندوق */
.profile-section{
    margin-bottom:25px;
    padding-bottom:15px;
    border-bottom:1px solid #eee;
}

/* عنوان القسم */
.profile-section h3{
    margin-bottom:15px;
    font-size:19px;
    color:#6a3d8f;
}

/* نصوص البيانات */
.profile-section p{
    margin:8px 0;
    font-size:16px;
}

/* صف كلمة المرور */
.password-box{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

/* زر تعديل البيانات */
.edit-btn{
    margin-top:20px;
    padding:10px 22px;
    border:none;
    border-radius:8px;
    background:#4b2a63;
    color:#fff;
    font-size:15px;
    cursor:pointer;
    display:inline-block;
    transition:0.3s;
}
.edit-btn:hover{
    opacity:0.9;
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
                <img src="شعار نورين.png" alt="شعار نورين">
            </div>
            <!-- روابط التنقل -->
            <ul class="sidebar-menu">
                <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
                <li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
                <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
                <li><a href="#">المدفوعات</a></li>
            </ul>

        </div>
        <!-- زر تسجيل الخروج -->
        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>
    <!-- محتوى الصفحة -->
    <div class="main-content">
        <!-- الهيدر -->
  <header class="header">

  <div class="page-heading">
    <div class="page-title">الملف الشخصي</div>
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
        <!-- محتوى الصفحة -->
        <div class="page">
            <div class="profile-box">
                <!-- اسم الشركة -->
                <h2><?php echo htmlspecialchars($investor['inv_name']); ?></h2>
                <!-- خط فاصل -->
                <div class="line"></div>
                <!-- بيانات الشركة -->
                <div class="profile-section">
                    <h3>بيانات الشركة</h3>
                    <p><strong>رقم السجل التجاري:</strong> <?php echo htmlspecialchars($investor['ccr_number']); ?></p>
                    <p><strong>رقم الهاتف:</strong> <?php echo htmlspecialchars($investor['inv_number']); ?></p>
                </div>
                <!-- بيانات الحساب -->
                <div class="profile-section">
                    <h3>بيانات الحساب</h3>
                    <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($investor['email']); ?></p>
                    <div class="password-box">
                        <label>كلمة المرور:</label>
                        <p>********</p>
                    </div>
                </div>
                <!-- زر التعديل -->
                <a href="Inv03_EditProfile.php" class="edit-btn">تعديل البيانات</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>