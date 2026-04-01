<?php
session_start();
/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) { die("فشل الاتصال بقاعدة البيانات");}

/* دعم العربية */
$conn->set_charset("utf8mb4");

/* جلب رقم المستفيد من الجلسة */
$bnf_id = $_SESSION['bnf_id'];

/* استعلام جلب بيانات المستفيد */
$stmt = $conn->prepare("
    SELECT f_name, l_name, sch_field, degree_level, phone_num, email
    FROM beneficiary
    WHERE bnf_id = ?
");
/* ربط قيمة رقم المستفيد مكان علامة الاستفهام في الاستعلام، مع تحديد  نوع القيمة(رقمية) */
$stmt->bind_param("i", $bnf_id);
/* تنفيذ الاستعلام بعد ربط القيم */
$stmt->execute();
/* تخزين نتيجة الاستعلام في متغير */
$result = $stmt->get_result();
/* جلب بيانات المستفيد من نتيجة الاستعلام على شكل مصفوفة */
$beneficiary = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الملف الشخصي</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=2">
<style>
/* الصفحة */
.page{padding: 40px; font-family: "Noto Kufi Arabic", sans-serif;}

/* صندوق الملف الشخصي */
.profile-box{background: #fff; max-width: 650px; margin: auto; padding: 35px; border-radius: 14px; box-shadow: 0 3px 10px rgba(0,0,0,0.08);}

/* اسم المستفيد */
.profile-box h2{text-align: center; margin-bottom: 15px; font-size: 24px; color: #4b2a63;}

/* الخط أسفل الاسم */
.line{width: 100%; height: 1px; background: #d8cde2; margin: 0 auto 25px;}

/* كل قسم من البيانات */
.profile-section{margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee;}

/* عناوين الأقسام */
.profile-section h3{margin-bottom: 15px; font-size: 19px; color: #6a3d8f;}

/* نصوص البيانات */
.profile-section p{margin: 8px 0; font-size: 16px;}

/* صف كلمة المرور */
.password-box{display: flex; align-items: center; gap: 10px;}

/* زر تعديل البيانات */
.edit-btn{display: block; margin: 20px auto 0; margin-top: 20px; padding: 10px 22px; border: none; border-radius: 8px; background: #4b2a63; color: #fff; font-size: 15px; cursor: pointer;}

/* تنسيق العناوين داخل البيانات */
label, .field{font-weight: 600;}

/* إزالة مارجن الفقرة داخل صف كلمة المرور */
.password-box p{margin: 0;}

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
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                 <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
          <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
          <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
                <li><a href="#">الاستشارات</a></li>
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
                    <a href="Ben02_Profile.php">الملف الشخصي</a>
              <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- محتوى الصفحة -->
        <div class="page">
            <div class="profile-box">
                <!-- اسم المستفيد -->
                <h2><?php echo  ($beneficiary['f_name'] . " " . $beneficiary['l_name']); ?></h2>
                <!-- خط أسفل الاسم -->
                <div class="line"></div>
                <!-- بيانات المستفيد -->
                <div class="profile-section">
                    <h3>بيانات المستفيد</h3>
                    <p><label>المجال الدراسي:</label> <?php echo ($beneficiary['sch_field']); ?></p>
                    <p><label>المؤهل الدراسي:</label> <?php echo  ($beneficiary['degree_level']); ?></p>
                    <p><label>رقم الهاتف:</label> <?php echo  ($beneficiary['phone_num']); ?></p>
                </div>

                <!-- بيانات الحساب -->
                <div class="profile-section">
                    <h3>بيانات الحساب</h3>
                    <p class="field">
                        البريد الإلكتروني: <?php echo  ($beneficiary['email']); ?>
                    </p>
                    <div class="password-box">
                        <label>كلمة المرور:</label>
                        <p>********</p>
                    </div>
                </div>

                <!-- زر تعديل البيانات -->
                <button type="button" class="edit-btn" onclick="window.location.href='Ben03_EditProfile.php'">
                    تعديل البيانات
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>