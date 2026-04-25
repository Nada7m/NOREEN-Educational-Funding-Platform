<?php
// الاتصال بقاعدة البيانات + تحديد الترميز
$con = mysqli_connect("localhost","root","","noreen");
mysqli_set_charset($con,"utf8mb4");
// متغيرات لعرض الرسائل (نجاح / خطأ)
$msg = "";
$type = "";
// تنفيذ عند الضغط على زر "إنشاء حساب"
if(isset($_POST["save"])){
    // أخذ البيانات من الفورم
    $name = $_POST["orgName"];       // اسم الجهة
    $ccr = $_POST["commercial"];     // السجل التجاري
    $phone = $_POST["phone"];        // رقم الهاتف
    $email = $_POST["email"];        // البريد الإلكتروني
    $pass = $_POST["password"];      // كلمة المرور
    // استعلامات للتحقق من وجود بيانات مسبقًا
    $checkEmail = mysqli_query($con,"SELECT * FROM investor WHERE email='$email'");
    $checkCcr = mysqli_query($con,"SELECT * FROM investor WHERE ccr_number='$ccr'");
    // إذا الإيميل موجود مسبقًا
    if(mysqli_num_rows($checkEmail) > 0){
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
    }
    // إذا السجل التجاري موجود مسبقًا
    elseif(mysqli_num_rows($checkCcr) > 0){
        $msg = "رقم السجل التجاري مسجل مسبقًا.";
        $type = "error";
    }
    else{
        // تشفير كلمة المرور قبل التخزين 
        $newpass = password_hash($pass, PASSWORD_DEFAULT);
        // أمر إدخال البيانات في الجدول
        $sql = "INSERT INTO investor (inv_name,ccr_number,inv_number,email,password)
                VALUES('$name','$ccr','$phone','$email','$newpass')";
        // تنفيذ الإدخال
        if(mysqli_query($con,$sql)){
            $msg = "تم إنشاء الحساب بنجاح.";
            $type = "success";
        }
        else{
            $msg = "حدث خطأ أثناء حفظ البيانات.";
            $type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إنشاء حساب مستثمر</title>
<!--  الخط -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- ملف التنسيق العام -->
<link rel="stylesheet" href="Style.css">
<style>
/*  نص تسجيل الدخول تحت الزر */
.login-text{
    text-align:center;
    margin-top:12px;
    color:#777;
    font-size:14px;
}
.login-text a{
    color:#777;
    text-decoration:none;
}
.login-text a:hover{
    text-decoration:underline;
}
</style>
</head>
<body>
<div class="container">
<div class="box">
<h2>إنشاء حساب <span>مستثمر</span></h2>
<!-- عرض رسالة النجاح أو الخطأ -->
<?php if($msg!=""){ ?>
<div class="message <?php echo $type; ?>">
<?php echo $msg; ?>
</div>
<?php } ?>
<!-- الفورم لإدخال البيانات -->
<form method="post" onsubmit="return checkForm()">
<div class="row">
<div class="field">
<label><span class="star">*</span> اسم الجهة</label>
<input type="text" id="name" name="orgName" placeholder="الرجاء إدخال اسم الجهة">
<div class="errorText" id="nameError"></div>
</div>
<div class="field">
<label><span class="star">*</span> رقم السجل التجاري</label>
<input type="text" id="ccr" name="commercial" placeholder="الرجاء إدخال رقم السجل التجاري">
<div class="errorText" id="ccrError"></div>
</div>
</div>
<div class="row">
<div class="field">
<label><span class="star">*</span> رقم الهاتف</label>
<input type="text" id="phone" name="phone" placeholder="05XXXXXXXX">
<div class="errorText" id="phoneError"></div>
</div>
<div class="field">
<label><span class="star">*</span> البريد الإلكتروني</label>
<input type="email" id="email" name="email" placeholder="example@gmail.com">
<div class="errorText" id="emailError"></div>
</div>
</div>
<div class="row">
<div class="field">
<label><span class="star">*</span> كلمة المرور</label>
<div class="passBox">
<input type="password" id="pass" name="password">
<span class="eye" onclick="showPass('pass')">👁</span>
</div>
<div class="errorText" id="passError"></div>
</div>
<div class="field">
<label><span class="star">*</span> تأكيد كلمة المرور</label>
<div class="passBox">
<input type="password" id="pass2" name="confirmPassword">
<span class="eye" onclick="showPass('pass2')">👁</span>
</div>
<div class="errorText" id="pass2Error"></div>
</div>
</div>
<div class="center">
<!-- زر إرسال الفورم -->
<button type="submit" class="btn" name="save">إنشاء حساب</button>
<!-- رابط الانتقال لتسجيل الدخول -->
<div class="login-text">
    <a href="login.php">هل ترغب بتسجيل الدخول؟</a>
</div>
</div>
</form>
</div>
</div>
<script>
// إظهار / إخفاء كلمة المرور
function showPass(id){
    var x = document.getElementById(id);
    x.type = (x.type == "password") ? "text" : "password";
}
// التحقق من صحة البيانات قبل الإرسال
function checkForm(){
    //  رسائل الخطأ
    document.getElementById("nameError").innerText = "";
    document.getElementById("ccrError").innerText = "";
    document.getElementById("phoneError").innerText = "";
    document.getElementById("emailError").innerText = "";
    document.getElementById("passError").innerText = "";
    document.getElementById("pass2Error").innerText = "";
    // أخذ القيم من الحقول
    var name = document.getElementById("name").value;
    var ccr = document.getElementById("ccr").value;
    var phone = document.getElementById("phone").value;
    var email = document.getElementById("email").value;
    var pass = document.getElementById("pass").value;
    var pass2 = document.getElementById("pass2").value;
    var ok = true;
    // تحقق من الاسم
    if(name == ""){
        document.getElementById("nameError").innerText = "يرجى إدخال اسم الجهة.";
        ok = false;
    }
    // تحقق من السجل التجاري
    if(ccr == ""){
        document.getElementById("ccrError").innerText = "يرجى إدخال رقم السجل التجاري.";
        ok = false;
    }
    else if(ccr.length != 10 || isNaN(ccr)){
        document.getElementById("ccrError").innerText = "يرجى إدخال رقم سجل تجاري مكوّن من 10 أرقام.";
        ok = false;
    }
    // تحقق من الهاتف
    if(phone == ""){
        document.getElementById("phoneError").innerText = "يرجى إدخال رقم الهاتف.";
        ok = false;
    }
    else if(phone.length != 10 || isNaN(phone)){
        document.getElementById("phoneError").innerText = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
        ok = false;
    }
    // تحقق من الإيميل
    if(email == ""){
        document.getElementById("emailError").innerText = "يرجى إدخال البريد الإلكتروني.";
        ok = false;
    }
    else if(email.indexOf("@") == -1){
        document.getElementById("emailError").innerText = "يرجى إدخال بريد إلكتروني صحيح.";
        ok = false;
    }
    // تحقق من كلمة المرور
    if(pass == ""){
        document.getElementById("passError").innerText = "يرجى إدخال كلمة المرور.";
        ok = false;
    }
    // تحقق من تأكيد كلمة المرور
    if(pass2 == ""){
        document.getElementById("pass2Error").innerText = "يرجى تأكيد كلمة المرور.";
        ok = false;
    }
    else if(pass != pass2){
        document.getElementById("pass2Error").innerText = "كلمتا المرور غير متطابقتين.";
        ok = false;
    }
    return ok; // يرجع true أو false
}
</script>

</body>
</html>