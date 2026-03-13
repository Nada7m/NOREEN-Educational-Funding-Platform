<?php

$con = mysqli_connect("localhost","root","","noreen");

$msg = "";
$type = "";

if(isset($_POST["save"])){

    $name = $_POST["orgName"];
    $ccr = $_POST["commercial"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $pass = $_POST["password"];

    $checkEmail = mysqli_query($con,"SELECT * FROM investor WHERE email='$email'");
    $checkCcr = mysqli_query($con,"SELECT * FROM investor WHERE ccr_number='$ccr'");

    if(mysqli_num_rows($checkEmail) > 0){
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
    }
    else if(mysqli_num_rows($checkCcr) > 0){
        $msg = "رقم السجل التجاري مسجل مسبقًا.";
        $type = "error";
    }
    else{
        $newpass = password_hash($pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO investor(inv_name,ccr_number,email,inv_number,password)
                VALUES('$name','$ccr','$email','$phone','$newpass')";

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
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">
<div class="box">

<h2>إنشاء حساب <span>مستثمر</span></h2>

<?php if($msg!=""){ ?>
<div class="message <?php echo $type; ?>">
<?php echo $msg; ?>
</div>
<?php } ?>

<form method="post" onsubmit="return checkForm()">

<div class="row">
<div class="field">
<label><span class="star">*</span> اسم الجهة</label>
<input type="text" id="name" name="orgName">
<div class="errorText" id="nameError"></div>
</div>

<div class="field">
<label><span class="star">*</span> رقم السجل التجاري</label>
<input type="text" id="ccr" name="commercial">
<div class="errorText" id="ccrError"></div>
</div>
</div>

<div class="row">
<div class="field">
<label><span class="star">*</span> البريد الإلكتروني</label>
<input type="text" id="email" name="email">
<div class="errorText" id="emailError"></div>
</div>

<div class="field">
<label><span class="star">*</span> رقم الهاتف</label>
<input type="text" id="phone" name="phone">
<div class="errorText" id="phoneError"></div>
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
<button type="submit" class="btn" name="save">إنشاء حساب</button>
</div>

</form>

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
    document.getElementById("emailError").innerText = "";
    document.getElementById("phoneError").innerText = "";
    document.getElementById("passError").innerText = "";
    document.getElementById("pass2Error").innerText = "";

    var name = document.getElementById("name").value;
    var ccr = document.getElementById("ccr").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;
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

    if(email == ""){
        document.getElementById("emailError").innerText = "يرجى إدخال البريد الإلكتروني.";
        ok = false;
    }
    else if(email.indexOf("@") == -1){
        document.getElementById("emailError").innerText = "يرجى إدخال بريد إلكتروني صحيح.";
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

    if(pass == ""){
        document.getElementById("passError").innerText = "يرجى إدخال كلمة المرور.";
        ok = false;
    }

    if(pass2 == ""){
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