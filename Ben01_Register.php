<?php

$con = mysqli_connect("localhost","root","","noreen");

$msg = "";
$type = "";

if(isset($_POST["save"])){

$firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $degree = $_POST['degree'];
    $major = $_POST['major'];
    $iban = $_POST['iban'];
    $bank = $_POST['bank'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

     $checkEmail = mysqli_query($con,"SELECT * FROM investor WHERE email='$email'");

    if(mysqli_num_rows($checkEmail) > 0){
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
    }
    
    else{
        $newpass = password_hash($password, PASSWORD_DEFAULT);}
        
        $sql = "INSERT INTO users (firstname, lastname, degree, major, iban, bank, phone, email, password) VALUES ('$firstname', '$lastname', '$degree', '$major', '$iban', '$bank', '$phone', '$email', '$newpass')";
        if(mysqli_query($con, $sql)){
            $msg = "Registration successful!";
            $type = "success";
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
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<title>إنشاء حساب مستفيد</title>

<?php if($msg!=""){ ?>
<div class="message <?php echo $type; ?>">
<?php echo $msg; ?>
</div>
<?php } ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">
<div class="box">

<h2>إنشاء حساب <span>مستفيد</span></h2>

<form method="post" onsubmit="return checkForm()">

<div class="row">

<div class="field">
<label><span class="star">*</span> الاسم الأول</label>
<input type="text" id="first" name="firstName" placeholder="الرجاء إدخال الاسم الاول">
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
<option>دبلوم</option>
<option>بكالوريوس</option>
<option>ماجستير</option>
<option>دكتوراه</option>
</select>
<div class="errorText" id="degreeError"></div>

</div>

<div class="field">

<label><span class="star">*</span> المجال الدراسي</label>

<select id="major" name="major">

<option>اختر المجال الدراسي من القائمة أدناه</option>
<option>تقني وحوسبي</option>
<option>علوم طبيعية</option>
<option>صناعي وتشغيلي</option>
<option>أداري</option>
<option>قانوني</option>
<option>اجتماعي وانساني</option>
<option>تصميمي</option>
<option>صحي</option>
<option>اقتصادي</option>
<option>بيئي</option>
<option>لوجيستي</option>
<option>أعلامي</option>

</select>

<div class="errorText" id="majorError"></div>

</div>

</div>

<div class="row">

<div class="field">

<label><span class="star">*</span> رقم الايبان</label>

<input type="text" id="iban" name="iban" placeholder="SA">

<div class="errorText" id="ibanError"></div>

</div>
<div class="field">
<label><span class="star">*</span> اسم المصرف</label>
<input type="text" id="bank" name="bank" placeholder="أدخل اسم المصرف">

<div class="errorText" id="bankError"></div>

</div>

</div>

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

<button type="submit" class="btn" name="save">

إنشاء حساب

</button>

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

document.getElementById("firstError").innerText="";
document.getElementById("lastError").innerText="";
document.getElementById("degreeError").innerText="";
document.getElementById("majorError").innerText="";
document.getElementById("ibanError").innerText="";
document.getElementById("bankError").innerText="";
document.getElementById("phoneError").innerText="";
document.getElementById("emailError").innerText="";
document.getElementById("passError").innerText="";
document.getElementById("pass2Error").innerText="";

var first=document.getElementById("first").value;
var last=document.getElementById("last").value;
var degree=document.getElementById("degree").value;
var major=document.getElementById("major").value;
var iban=document.getElementById("iban").value;
var bank=document.getElementById("bank").value;
var phone=document.getElementById("phone").value;
var email=document.getElementById("email").value;
var pass=document.getElementById("pass").value;
var pass2=document.getElementById("pass2").value;

var ok=true;

if(first==""){
document.getElementById("firstError").innerText="يرجى إدخال الاسم الأول.";
ok=false;
}

if(last==""){
document.getElementById("lastError").innerText="يرجى إدخال الاسم الأخير.";
ok=false;
}

if(degree=="اختر المؤهل الدراسي من القائمة أدناه"){
document.getElementById("degreeError").innerText="يرجى اختيار المؤهل الدراسي.";
ok=false;
}

if(major=="اختر المجال الدراسي من القائمة أدناه"){
document.getElementById("majorError").innerText="يرجى اختيار المجال الدراسي.";
ok=false;
}

if(iban==""){
document.getElementById("ibanError").innerText="يرجى إدخال رقم الايبان.";
ok=false;
}

if(bank==""){
document.getElementById("bankError").innerText="يرجى إدخال اسم المصرف.";
ok=false;
}

if(phone==""){
document.getElementById("phoneError").innerText="يرجى إدخال رقم الهاتف.";
ok=false;
}
else if(phone.length!=10 || isNaN(phone)){
document.getElementById("phoneError").innerText="يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
ok=false;
}

if(email==""){
document.getElementById("emailError").innerText="يرجى إدخال البريد الإلكتروني.";
ok=false;
}
else if(email.indexOf("@")==-1){
document.getElementById("emailError").innerText="يرجى إدخال بريد إلكتروني صحيح.";
ok=false;
}

if(pass==""){
document.getElementById("passError").innerText="يرجى إدخال كلمة المرور.";
ok=false;
}

if(pass2==""){
document.getElementById("pass2Error").innerText="يرجى تأكيد كلمة المرور.";
ok=false;
}
else if(pass!=pass2){
document.getElementById("pass2Error").innerText="كلمتا المرور غير متطابقتين.";
ok=false;
}

return ok;

}

</script>

</body>
</html>