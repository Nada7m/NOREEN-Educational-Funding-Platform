<?php
session_start();

if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}

$inv_id = $_SESSION['inv_id'];

$stmt = $conn->prepare("SELECT inv_name, inv_number, ccr_number, email, password FROM investor WHERE inv_id = ?");
$stmt->bind_param("i", $inv_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $investor = $result->fetch_assoc();
} else {
    die("لم يتم العثور على بيانات المستثمر");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الملف الشخصي</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css">

<style>

/* تنسيق مساحة الصفحة */
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

/* الخط أسفل اسم الشركة */
.line{
    width:100%;
    height:1px;
    background:#d8cde2;
    margin:0 auto 25px;
}

/* كل قسم من البيانات */
.profile-section{
    margin-bottom:25px;
    padding-bottom:15px;
    border-bottom:1px solid #eee;
}

/* عناوين الأقسام */
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

/* زر إظهار كلمة المرور */
.show-btn{
    background:#eee;
    border:none;
    padding:6px 12px;
    border-radius:6px;
    cursor:pointer;
    font-size:13px;
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
                <li><a href="#">إدارة المنح</a></li>
                <li><a href="#">المدفوعات</a></li>
            </ul>

        </div>

        <!-- زر تسجيل الخروج -->
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

        <!-- الهيدر -->
        <header class="header">

            <div class="page-title">
                الملف الشخصي
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">

                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="القائمة">

                    <div class="dropdown-menu">
                        <a href="Inv02_Profile.php">الملف الشخصي</a>
                        <a href="#">التواصل والدعم</a>
                    </div>

                </div>
            </div>

        </header>

        <!-- محتوى الصفحة -->
        <div class="page">

            <div class="profile-box">

                <!-- اسم الشركة -->
                <h2><?php echo htmlspecialchars($investor['company_name']); ?></h2>

                <!-- خط أسفل اسم الشركة -->
                <div class="line"></div>

                <!-- بيانات الشركة -->
                <div class="profile-section">
                    <h3>بيانات الشركة</h3>

                    <p><strong>رقم السجل التجاري:</strong> <?php echo htmlspecialchars($investor['commercial_no']); ?></p>
                    <p><strong>مجال نشاط الشركة:</strong> <?php echo htmlspecialchars($investor['company_field']); ?></p>
                    <p><strong>رقم الهاتف:</strong> <?php echo htmlspecialchars($investor['phone']); ?></p>
                </div>

                <!-- بيانات الحساب -->
                <div class="profile-section">
                    <h3>بيانات الحساب</h3>

                    <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($investor['email']); ?></p>

                    <div class="password-box">
                        <strong>كلمة المرور:</strong>
                        <span id="password">********</span>
                        <button class="show-btn" onclick="togglePassword()">إظهار</button>
                    </div>
                </div>

                <!-- زر تعديل البيانات -->
                
<a href="Inv03_EditProfile.php" class="edit-btn ">
 تعديل البيانات
</a>
            </div>

        </div>

    </div>

</div>

<script>
/* إظهار وإخفاء كلمة المرور */
function togglePassword() {
    let pass = document.getElementById("password");

    if (pass.innerHTML === "********") {
        pass.innerHTML = "<?php echo htmlspecialchars($investor['password']); ?>";
    } else {
        pass.innerHTML = "********";
    }
}
</script>

</body>
</html>