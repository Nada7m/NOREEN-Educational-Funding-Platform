<?php
session_start();

/* التأكد أن المستخدم مسجل دخول */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

/* إذا فشل الاتصال */
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* متغيرات الرسائل */
$success = "";
$error = "";

/* عند إرسال النموذج */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* أخذ البيانات */
    $sch_name = trim($_POST["sch_name"]);
    $sch_field = trim($_POST["sch_field"]);
    $study_level = trim($_POST["study_level"]);
    $app_deadline = trim($_POST["app_deadline"]);
    $requirements = trim($_POST["requirements"]);
    $specializations = trim($_POST["specializations"]);
    $inv_id = $_SESSION["inv_id"];

    /* التحقق */
    if (
        $sch_name == "" ||
        $sch_field == "" ||
        $study_level == "" ||
        $app_deadline == "" ||
        !isset($_POST["confirm_data"])
    ) {
        $error = "يرجى تعبئة جميع الحقول المطلوبة وتأكيد صحة البيانات.";
    } else {

        /* إدخال البيانات */
        $stmt = $conn->prepare("INSERT INTO scholarship_opps 
        (sch_field, inv_id, sch_name, requirements, study_level, Specializations, app_deadline)
        VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sisssss",
            $sch_field,
            $inv_id,
            $sch_name,
            $requirements,
            $study_level,
            $specializations,
            $app_deadline
        );

        if ($stmt->execute()) {
            $success = "تم نشر عرض المنحة بنجاح.";
        } else {
            $error = "حدث خطأ أثناء حفظ البيانات.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إنشاء عرض منحة جديدة</title>
<link rel="stylesheet" href="CSS01Layout.css">
<style>
.header{
  display:flex;
  flex-direction:row-reverse;
  justify-content:space-between;
  align-items:center;
}

.page-heading{
  text-align:right;
  align-items:flex-end;
}

.header-icons{
  display:flex;
  align-items:center;
}

.back-icon{
  width:26px;
  height:26px;
}

.back-btn{
  width:32px;
  height:32px;
  display:flex;
  align-items:center;
  justify-content:center;
}

::placeholder{
  color:#999;
  font-size:13px;
}

.form-card{
  width:96%;
  max-width:1450px;
  margin:0 auto;
  background:#ffffff;
  border-radius:10px;
  padding:42px 46px 52px;
  box-shadow:0 4px 18px rgba(0,0,0,0.05);
}

.intro-text{
  text-align:center;
  font-size:17px;
  color:#222;
  margin-bottom:28px;
}

.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:30px 38px;
  align-items:start;
}

.field{
  margin-bottom:22px;
}

.field label{
  display:block;
  font-size:17px;
  font-weight:600;
  margin-bottom:10px;
  color:#191919;
  line-height:1.9;
}

.field input[type="text"],
.field input[type="date"],
.field select{
  width:100%;
  height:50px;
  border:1.5px solid #8FB4C9;
  background:#F8F8F8;
  padding:10px 14px;
  font-size:15px;
  color:#222;
  outline:none;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.field textarea{
  width:100%;
  min-height:320px;
  border:1.5px solid #8FB4C9;
  background:#F8F8F8;
  padding:14px 16px;
  font-size:15px;
  color:#222;
  outline:none;
  resize:none;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.field input:focus,
.field select:focus,
.field textarea:focus{
  border-color:#6E4A8E;
  background:#ffffff;
}

.field input::placeholder,
.field textarea::placeholder,
.field select{
  color:#c9c9c9;
}

.radio-group{
  display:grid;
  grid-template-columns:repeat(2,max-content);
  gap:12px 46px;
  padding:6px 6px 0 0;
  margin-bottom:10px;
}

.radio-group label{
  display:flex;
  align-items:center;
  gap:10px;
  font-size:16px;
  color:#151515;
}

.radio-group input{
  width:16px;
  height:16px;
  accent-color:#4E2E65;
}

.confirm-row{
  display:flex;
  align-items:center;
  gap:10px;
  font-size:16px;
  color:#171717;
  margin-top:22px;
}

.confirm-row input{
  width:16px;
  height:16px;
}

.form-submit-box{
  margin-top:30px;
}

.form-submit-btn{
  background:#4A2B63;
  color:white;
  border:none;
  padding:14px 26px;
  border-radius:4px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  font-size:17px;
  font-weight:700;
  min-width:340px;
  cursor:pointer;
}

.form-submit-btn:hover{
  opacity:.95;
}

.error-text{
  display:none;
  margin-top:14px;
  color:#C0392B;
  font-size:15px;
  font-weight:600;
}

@media (max-width: 950px){
  .form-grid{
    grid-template-columns:1fr;
  }

  .form-submit-btn{
    min-width:100%;
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
      <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
      <li><a href="Inv04_CreateScholarship.php" class="active">عرض المنح</a></li>
     <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
<li><a href="Inv10_Payments.php">المدفوعات</a></li>
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

<!-- المحتوى -->
<main class="main-content">

<header class="header">

  <!-- الإعدادات -->
  <div class="header-icons">
    <div class="settings-dropdown">
      <img src="ايقونة قائمة الاعدادات.png" class="menu-icon">
      <div class="dropdown-menu">
        <a href="Inv02_Profile.php">الملف الشخصي</a>
        <a href="support.php">تقديم شكوى او استفسار</a>
      </div>
    </div>
  </div>

  <!-- العنوان -->
  <div class="page-heading">
    <h1 class="page-title">إنشاء عرض منحة جديدة</h1>
    <p class="page-description">صفحة تقديم عروض فرص المنح</p>
  </div>

</header>

<section class="page">

  <!-- زر الرجوع -->
  <div class="page-top">
    <div></div>

    <div class="back-btn">
      <a href="Inv04_CreateScholarship.php">
        <img src="سهم تراجع.svg" width="40">
      </a>
    </div>
  </div>

  <!-- الفورم -->
  <div class="form-card">

    <div class="intro-text">
      يرجى إدخال تفاصيل المنحة بدقة قبل نشرها للمستفيدين
    </div>

    <?php if ($error != ""): ?>
      <p class="error-text" style="display:block;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($success != ""): ?>
      <p style="color:green; font-weight:700; margin-bottom:15px;">
        <?php echo $success; ?>
      </p>
    <?php endif; ?>

    <form method="POST">

      <div class="form-grid">

        <!-- العمود الأول -->
        <div>

          <div class="field">
            <label>*اسم برنامج المنحة الدراسية</label>
            <input type="text" name="sch_name"
            placeholder="مثال: برنامج سابك - تطوير الأنظمة الصناعية">
          </div>

          <div class="field">
            <label>*  المجال الرئيسي</label>
            <select name="sch_field">
              <option value="">اختر المجال العام للبرنامج </option>
              <option value="تقني وحوسبي">تقني وحوسبي</option>
              <option value="علوم طبيعية">علوم طبيعية</option>
              <option value="صناعي وتشغيلي">صناعي وتشغيلي</option>
              <option value="اداري">اداري</option>
              <option value="قانوني">قانوني</option>
              <option value="اجتماعي وانساني">اجتماعي وانساني</option>
              <option value="تصميمي">تصميمي</option>
              <option value="اقتصادي">اقتصادي</option>
              <option value="إعلامي">إعلامي</option>
              <option value="بيئي">بيئي</option>
              <option value="لوجيستي">لوجيستي</option>
              <option value="صحي">صحي</option>
            </select>
          </div>

          <div class="field">
            <label>* اختر المرحلة الدراسية التي تستهدفها المنحة</label>
            <div class="radio-group">
              <label><input type="radio" name="study_level" value="بكالوريوس"> بكالوريوس</label>
              <label><input type="radio" name="study_level" value="ماجستير"> ماجستير</label>
              <label><input type="radio" name="study_level" value="دكتوراه"> دكتوراه</label>
            </div>
          </div>

          <div class="field">
            <label>* حدد تاريخ انتهاء فترة التقديم على المنحة المعروضة</label>
            <input type="date" name="app_deadline">
          </div>

          <label class="confirm-row">
            <input type="checkbox" name="confirm_data">
            <span>  أقر بصحة جميع المعلومات المدخلة في هذا النموذج</span>
          </label>

        </div>

        <!-- العمود الثاني -->
        <div>

          <div class="field">
            <label>الرجاء إدخال التخصصات الدقيقة المشمولة ضمن البرنامج.</label>
            <input type="text" name="specializations"
            placeholder="مثال: هندسة البرمجيات، الأمن السيبراني">
          </div>

          <div class="field">
            <label>متطلبات القبول في البرنامج</label>
            <textarea name="requirements"
            placeholder="اكتب الشروط مثل المعدل، اللغة، الخبرة"></textarea>
          </div>

          <div class="form-submit-box">
            <button class="form-submit-btn">إنشاء عرض منحة </button>
          </div>

        </div>

      </div>

    </form>

  </div>

</section>

</main>

</div>

</body>
</html>