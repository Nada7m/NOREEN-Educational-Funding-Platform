<?php

$con = mysqli_connect("localhost","root","","noreen");

$msg = "";
$type = "";

if(isset($_POST["save"])){

    $first = $_POST["firstName"];
    $last = $_POST["lastName"];
    $degree = $_POST["degree_level"];
    $major = $_POST["field"];
    $phone = $_POST["phone_num"];
    $email = $_POST["email"];
    $pass = $_POST["password"];

    $checkEmail = mysqli_query($con,"SELECT * FROM beneficiary WHERE email='$email'");

    if(mysqli_num_rows($checkEmail) > 0){
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
    }
    else{
        $newpass = password_hash($pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO beneficiary (f_name,l_name,degree_level,sch_field,phone_num,email,password)
                VALUES('$first','$last','$degree_level','$field','$phone','$email','$newpass')";

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
<title>إنشاء حساب مستفيد</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">
<div class="box">

<h2>إنشاء حساب <span>مستفيد</span></h2>

<?php if($msg!=""){ ?>
<div class="message <?php echo $type; ?>">
<?php echo $msg; ?>
</div>
<?php } ?>

<form method="post" onsubmit="return checkForm()">

<div class="row">

<div class="field">
<label><span class="star">*</span> الاسم الأول</label>
<input type="text" id="first" name="firstName" placeholder="الرجاء إدخال الاسم الأول">
<div class="errorText" id="firstError"></div>
</div>

<div class="field">
<label><span class="star">*</span> الاسم الأخير</label>
<input type="text" id="last" name="lastName" placeholder="الرجاء إدخال الاسم الأخير">
<div class="errorText" id="lastError"></div>
</div>

</div>

<div class="row">

<div class="field">
<label><span class="star">*</span> المؤهل الدراسي</label>
<select id="degree" name="degree">
<option>اختر المؤهل الدراسي من القائمة أدناه</option>
<option>ثانوي</option>
<option>بكالوريوس</option>
<option>ماجستير</option>
<option>دكتوراه</option>
</select>
<div class="errorText" id="degree_levelError"></div>
</div>

<div class="field">
<label><span class="star">*</span> المجال الدراسي</label>
<select id="field" name="field">
<option>اختر المجال الدراسي من القائمة أدناه</option>
<option>تقني وحوسبي</option>
<option>علوم طبيعية</option>
<option>صناعي وتشغيلي</option>
<option>إداري</option>
<option>قانوني</option>
<option>اجتماعي وإنساني</option>
<option>تصميمي</option>
<option>صحي</option>
<option>اقتصادي</option>
<option>بيئي</option>
<option>لوجستي</option>
<option>إعلامي</option>
</select>
<div class="errorText" id="fieldError"></div>
</div>

</div>

<div class="row">

<div class="field">
<label><span class="star">*</span> رقم الهاتف</label>
<input type="text" id="phone_num" name="phone_num" placeholder="05XXXXXXXX">
<div class="errorText" id="phone_numError"></div>
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

    document.getElementById("firstError").innerText = "";
    document.getElementById("lastError").innerText = "";
    document.getElementById("degree_levelError").innerText = "";
    document.getElementById("fieldError").innerText = "";
    document.getElementById("phone_numError").innerText = "";
    document.getElementById("emailError").innerText = "";
    document.getElementById("passError").innerText = "";
    document.getElementById("pass2Error").innerText = "";

    var first = document.getElementById("first").value;
    var last = document.getElementById("last").value;
    var degree = document.getElementById("degree_level").value;
    var field = document.getElementById("field").value;
    var phone = document.getElementById("phone").value;
    var email = document.getElementById("email").value;
    var pass = document.getElementById("pass").value;
    var pass2 = document.getElementById("pass2").value;

    var ok = true;

    if(first == ""){
        document.getElementById("firstError").innerText = "يرجى إدخال الاسم الأول.";
        ok = false;
    }

    if(last == ""){
        document.getElementById("lastError").innerText = "يرجى إدخال الاسم الأخير.";
        ok = false;
    }

    if(degree == "اختر المؤهل الدراسي من القائمة أدناه"){
        document.getElementById("degree_levelError").innerText = "يرجى اختيار المؤهل الدراسي.";
        ok = false;
    }

    if(field == "اختر المجال الدراسي من القائمة أدناه"){
        document.getElementById("fieldError").innerText = "يرجى اختيار المجال الدراسي.";
        ok = false;
    }

    if(phone == ""){
        document.getElementById("phone_numError").innerText = "يرجى إدخال رقم الهاتف.";
        ok = false;
    }
    else if(phone.length != 10 || isNaN(phone)){
        document.getElementById("phone_numError").innerText = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
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