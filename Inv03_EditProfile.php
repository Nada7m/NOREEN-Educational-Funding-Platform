<?php
session_start();

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* رسالة النظام */
$msg = "";
$type = "";

/* التحقق من وجود مستثمر مسجل دخول */
if (!isset($_SESSION["inv_id"])) {
    die("يجب تسجيل الدخول أولاً.");
}

/* رقم المستثمر الحالي */
$inv_id = $_SESSION["inv_id"];

/* عند الضغط على حفظ التغييرات */
if (isset($_POST["save"])) {

    $name  = trim($_POST["orgName"]);
    $ccr   = trim($_POST["commercial"]);
    $field = trim($_POST["companyField"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $pass  = trim($_POST["password"]);
    $pass2 = trim($_POST["confirmPassword"]);

    if ($name == "" || $ccr == "" || $phone == "" || $email == "") {
        $msg = "يرجى تعبئة جميع الحقول المطلوبة.";
        $type = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $ccr)) {
        $msg = "رقم السجل التجاري يجب أن يكون 10 أرقام.";
        $type = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $msg = "رقم الهاتف يجب أن يكون 10 أرقام.";
        $type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "يرجى إدخال بريد إلكتروني صحيح.";
        $type = "error";
    } elseif ($pass != "" && $pass != $pass2) {
        $msg = "كلمتا المرور غير متطابقتين.";
        $type = "error";
    } else {

        /* التحقق من تكرار البريد */
        $stmtEmail = mysqli_prepare($con, "SELECT inv_id FROM investor WHERE email = ? AND inv_id != ?");
        mysqli_stmt_bind_param($stmtEmail, "si", $email, $inv_id);
        mysqli_stmt_execute($stmtEmail);
        $resultEmail = mysqli_stmt_get_result($stmtEmail);

        /* التحقق من تكرار السجل التجاري */
        $stmtCcr = mysqli_prepare($con, "SELECT inv_id FROM investor WHERE ccr_number = ? AND inv_id != ?");
        mysqli_stmt_bind_param($stmtCcr, "si", $ccr, $inv_id);
        mysqli_stmt_execute($stmtCcr);
        $resultCcr = mysqli_stmt_get_result($stmtCcr);

        if (mysqli_num_rows($resultEmail) > 0) {
            $msg = "البريد الإلكتروني مستخدم مسبقًا.";
            $type = "error";
        } elseif (mysqli_num_rows($resultCcr) > 0) {
            $msg = "رقم السجل التجاري مسجل مسبقًا.";
            $type = "error";
        } else {

            if ($pass != "") {
                $newpass = password_hash($pass, PASSWORD_DEFAULT);

                $stmtUpdate = mysqli_prepare($con, "UPDATE investor SET inv_name = ?, ccr_number = ?, inv_number = ?, email = ?, password = ? WHERE inv_id = ?");
                mysqli_stmt_bind_param($stmtUpdate, "sssssi", $name, $ccr, $phone, $email, $newpass, $inv_id);
            } else {
                $stmtUpdate = mysqli_prepare($con, "UPDATE investor SET inv_name = ?, ccr_number = ?, inv_number = ?, email = ? WHERE inv_id = ?");
                mysqli_stmt_bind_param($stmtUpdate, "ssssi", $name, $ccr, $phone, $email, $inv_id);
            }

            if (mysqli_stmt_execute($stmtUpdate)) {
                $msg = "تم حفظ التغييرات بنجاح.";
                $type = "success";
            } else {
                $msg = "حدث خطأ أثناء حفظ البيانات.";
                $type = "error";
            }
        }
    }
}

/* جلب بيانات المستثمر الحالي */
$stmtData = mysqli_prepare($con, "SELECT * FROM investor WHERE inv_id = ?");
mysqli_stmt_bind_param($stmtData, "i", $inv_id);
mysqli_stmt_execute($stmtData);
$data = mysqli_stmt_get_result($stmtData);
$row = mysqli_fetch_assoc($data);

if (!$row) {
    die("لم يتم العثور على بيانات المستثمر.");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل بيانات المستثمر</title>

  <!-- خط الموقع -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

 
  <!-- التنسيق الأساسي المشترك -->
  <link rel="stylesheet" href="CSS01Layout.css?v=3">

  <style>
    /* مساحة الصفحة */
    .profile-content{
      padding:35px 40px 55px;
    }

    /* الصندوق الرئيسي */
    .edit-wrapper{
      width:980px;
      margin:0 auto;
    }

    /* عنوان الصفحة داخل المحتوى */
    .form-title{
      text-align:right;
      font-size:20px;
      font-weight:700;
      color:#222222;
      margin-bottom:8px;
    }

    /* الوصف تحت العنوان */
    .form-subtitle{
      text-align:right;
      font-size:14px;
      font-weight:500;
      color:#444444;
      margin-bottom:24px;
    }

    /* صف اسم الجهة */
    .org-name-row{
      margin-bottom:22px;
    }

    .org-label{
      display:block;
      text-align:right;
      font-size:15px;
      font-weight:700;
      color:#333333;
      margin-bottom:8px;
    }

    .org-input{
      width:100%;
      height:38px;
      border:1px solid #d8d8d8;
      background:#FFFFFF;
      padding:8px 10px;
      font-size:13px;
      outline:none;
    }

    /* الصندوقين */
    .form-boxes{
      display:flex;
      gap:28px;
      align-items:flex-start;
    }

    .info-box{
      flex:1;
      background:#FFFFFF;
      border:1px solid #ececec;
      padding:18px 20px 20px;
    }

    .info-box-title{
      text-align:center;
      font-size:16px;
      font-weight:700;
      color:#333333;
      margin-bottom:18px;
      padding-bottom:10px;
      border-bottom:1px solid #e6e6e6;
    }

    /* كل حقل */
    .form-group{
      margin-bottom:18px;
    }

    .form-label{
      display:block;
      text-align:right;
      font-size:14px;
      font-weight:600;
      color:#333333;
      margin-bottom:7px;
    }

    .form-input{
      width:100%;
      height:38px;
      border:1px solid #d8d8d8;
      background:#FFFFFF;
      padding:8px 10px;
      font-size:13px;
      outline:none;
    }

    .form-input:focus,
    .org-input:focus{
      border-color:#70A0AF;
    }

    /* مربع كلمة المرور */
    .passBox{
      position:relative;
    }

    .passBox .form-input{
      padding-left:35px;
    }

    .eye{
      position:absolute;
      left:10px;
      top:50%;
      transform:translateY(-50%);
      cursor:pointer;
      font-size:14px;
      user-select:none;
    }

    /* الأزرار */
    .form-actions{
      display:flex;
      justify-content:center;
      gap:24px;
      margin-top:28px;
    }

    .save-btn{
      background:#63B27A;
      color:#FFFFFF;
      border:none;
      border-radius:8px;
      padding:12px 28px;
      font-size:15px;
      font-weight:600;
      cursor:pointer;
    }

    .cancel-btn{
      background:#B54747;
      color:#FFFFFF;
      border:none;
      border-radius:8px;
      padding:12px 28px;
      font-size:15px;
      font-weight:600;
      cursor:pointer;
    }

    /* رسائل الخطأ الصغيرة تحت الحقول */
    .errorText{
      color:red;
      font-size:12px;
      margin-top:5px;
      min-height:18px;
    }

    /* رسالة النظام أعلى النموذج */
    .message{
      text-align:center;
      padding:10px;
      margin-bottom:20px;
      border-radius:6px;
      font-size:13px;
    }

    .error{
      background:#fff3f3;
      color:#b42318;
    }

    .success{
      background:#f1fff3;
      color:#1f7a2e;
    }
     </style>
</head>

  <!-- الهيكل العام -->
  <div class="layout">

    <!-- الشريط الجانبي -->
    <aside class="sidebar">

      <div class="sidebar-top">

        <!-- الشعار -->
        <div class="sidebar-logo">
          <img src="شعار نورين.png" alt="نورين">
        </div>

        <!-- روابط الشريط -->
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
      <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
      <b>تسجيل الخروج</b>
    </button>
  </form>
</div>

    </aside>

    <!-- المحتوى الرئيسي -->
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
      <div class="profile-content">

        <div class="edit-wrapper">

          <h2 class="form-title">تعديل بيانات الجهة الاستثمارية</h2>
          <p class="form-subtitle">يمكنك تحديث معلومات الجهة وحفظ التغييرات</p>

          <form method="post" action="#" onsubmit="return checkForm()">

            <!-- اسم الجهة -->
            <div class="org-name-row">
              <label class="org-label">اسم الجهة</label>
              <input type="text" id="name" name="orgName" class="org-input">
              <div class="errorText" id="nameError"></div>
            </div>

            <!-- الصندوقين -->
            <div class="form-boxes">

              <!-- بيانات الشركة -->
              <div class="info-box">
                <div class="info-box-title">بيانات الشركة :</div>

                <div class="form-group">
                  <label class="form-label">رقم السجل التجاري :</label>
                  <input type="text" id="ccr" name="commercial" class="form-input">
                  <div class="errorText" id="ccrError"></div>
                </div>

                <div class="form-group">
                  <label class="form-label">مجال نشاط الشركة :</label>
                  <input type="text" id="field" name="companyField" class="form-input">
                  <div class="errorText" id="fieldError"></div>
                </div>

                <div class="form-group">
                  <label class="form-label">رقم الهاتف :</label>
                  <input type="text" id="phone" name="phone" class="form-input" placeholder="+966xxxxxxxxx">
                  <div class="errorText" id="phoneError"></div>
                </div>
              </div>

              <!-- بيانات تسجيل الدخول -->
              <div class="info-box">
                <div class="info-box-title">بيانات تسجيل الدخول :</div>

                <div class="form-group">
                  <label class="form-label">البريد الإلكتروني :</label>
                  <input type="text" id="email" name="email" class="form-input" placeholder="example@company.com">
                  <div class="errorText" id="emailError"></div>
                </div>

                <div class="form-group">
                  <label class="form-label">كلمة المرور :</label>
                  <div class="passBox">
                    <input type="password" id="pass" name="password" class="form-input" placeholder="exa4567999">
                    <span class="eye" onclick="showPass('pass')">👁</span>
                  </div>
                  <div class="errorText" id="passError"></div>
                </div>

                <div class="form-group">
                  <label class="form-label">تأكيد كلمة المرور :</label>
                  <div class="passBox">
                    <input type="password" id="pass2" name="confirmPassword" class="form-input" placeholder="exa4567999">
                    <span class="eye" onclick="showPass('pass2')">👁</span>
                  </div>
                  <div class="errorText" id="pass2Error"></div>
                </div>
              </div>

            </div>

            <!-- الأزرار -->
            <div class="form-actions">
              <button type="button" class="cancel-btn">إلغاء التغييرات</button>
              <button type="submit" class="save-btn" name="save">حفظ التغييرات</button>
            </div>

          </form>

        </div>

      </div>

    </div>

  </div>

<script>
function showPass(id){
  var x = document.getElementById(id);

  if(x.type == "password"){
    x.type = "text";
  }
  else{
    x.type = "password";
  }
}

function checkForm(){

  document.getElementById("nameError").innerText = "";
  document.getElementById("ccrError").innerText = "";
  document.getElementById("fieldError").innerText = "";
  document.getElementById("phoneError").innerText = "";
  document.getElementById("emailError").innerText = "";
  document.getElementById("passError").innerText = "";
  document.getElementById("pass2Error").innerText = "";

  var name = document.getElementById("name").value;
  var ccr = document.getElementById("ccr").value;
  var field = document.getElementById("field").value;
  var phone = document.getElementById("phone").value;
  var email = document.getElementById("email").value;
  var pass = document.getElementById("pass").value;
  var pass2 = document.getElementById("pass2").value;

  var ok = true;

  if(name == ""){
    document.getElementById("nameError").innerText = "يرجى إدخال اسم الجهة.";
    ok = false;
  }

  if(ccr == ""){
    document.getElementById("ccrError").innerText = "يرجى إدخال رقم السجل التجاري.";
    ok = false;
  }
  else if(ccr.length != 10 || isNaN(ccr)){
    document.getElementById("ccrError").innerText = "يرجى إدخال رقم سجل تجاري مكوّن من 10 أرقام.";
    ok = false;
  }

  if(field == ""){
    document.getElementById("fieldError").innerText = "يرجى إدخال مجال نشاط الشركة.";
    ok = false;
  }

  if(phone == ""){
    document.getElementById("phoneError").innerText = "يرجى إدخال رقم الهاتف.";
    ok = false;
  }
  else if(phone.length != 10 || isNaN(phone)){
    document.getElementById("phoneError").innerText = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
    ok = false;
  }

  if(email == ""){
    document.getElementById("emailError").innerText = "يرجى إدخال البريد الإلكتروني.";
    ok = false;
  }
  else if(email.indexOf("@") == -1){
    document.getElementById("emailError").innerText = "يرجى إدخال بريد إلكتروني صحيح.";
    ok = false;
  }

  if(pass != "" && pass2 == ""){
    document.getElementById("pass2Error").innerText = "يرجى تأكيد كلمة المرور.";
    ok = false;
  }
  else if(pass != pass2){
    document.getElementById("pass2Error").innerText = "كلمتا المرور غير متطابقتين.";
    ok = false;
  }

  return ok;
}
</script>

</body>
</html>