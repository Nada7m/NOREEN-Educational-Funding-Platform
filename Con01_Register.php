<?php

$con = mysqli_connect("localhost","root","","noreen");

$msg = "";
$type = "";

if(isset($_POST["save"])){

    $office = $_POST["office_name"];
    $ccr = $_POST["ccr_number"];
    $email = $_POST["email"];
    $desc = $_POST["office_description"];
    $bachelor = $_POST["bachelor_fee"];
    $master = $_POST["master_fee"];
    $phd = $_POST["phd_fee"];
    $phone = $_POST["phone"];
    $pass = $_POST["password"];
    $country = $_POST["country"];

    $checkEmail = mysqli_query($con,"SELECT * FROM consulting_office WHERE email='$email'");
    $checkCcr = mysqli_query($con,"SELECT * FROM consulting_office WHERE ccr_number='$ccr'");

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

        $sql = "INSERT INTO consulting_office
        (ccr_number,email,office_name,office_description,Bachelor_fee,Masters_fee,Phd_fee,password,phone,country)
        VALUES
        ('$ccr','$email','$office','$desc','$bachelor','$master','$phd','$newpass','$phone','$country')";

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
<title>إنشاء حساب مكتب استشاري</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">
<div class="box">

<h2>إنشاء حساب <span>مكتب استشاري</span></h2>

<?php if($msg!=""){ ?>

<div class="message <?php echo $type; ?>">
<?php echo $msg; ?>
</div>

<?php } ?>

<form method="post" onsubmit="return checkForm()">

<div class="row">

<div class="field">
<label><span class="star">*</span> اسم المكتب</label>
<input type="text" id="office" name="office_name" placeholder="الرجاء إدخال الاسم الرسمي للمكتب">
<div class="errorText" id="officeError"></div>
</div>

<div class="field">
<label><span class="star">*</span> رقم السجل التجاري</label>
<input type="text" id="ccr" name="ccr_number" placeholder="مثل : 1010234567">
<div class="errorText" id="ccrError"></div>
</div>

</div>

<div class="row">

<div class="field">
<label><span class="star">*</span> رسوم خدمات الماجستير</label>
<input type="text" id="master" name="master_fee" placeholder="القيمة بالريال">
<div class="errorText" id="masterError"></div>
</div>

<div class="field">
<label><span class="star">*</span> رسوم خدمات الدكتوراه</label>
<input type="text" id="phd" name="phd_fee" placeholder="القيمة بالريال">
<div class="errorText" id="phdError"></div>
</div>

</div>




<div class="row">

<div class="field">
<label><span class="star">*</span> رسوم خدمات البكالوريوس</label>
<input type="text" id="bachelor" name="bachelor_fee" placeholder="القيمة بالريال">
<div class="errorText" id="bachelorError"></div>
</div>

<div class="field">

<label><span class="star">*</span> الدول</label>

<select id="country" name="country">

<option value="">اختر الدولة</option>

<option>امريكا</option>
<option>فرنسا</option>
<option>ايرلندا</option>
<option>مالطا</option>
<option>الهند</option>
<option>الصين</option>
<option>اليابان</option>
<option>بريطانيا</option>
<option>نيوزلندا</option>
<option>ماليزيا</option>
<option>تركيا</option>
<option>المانيا</option>
<option>كندا</option>
<option>استراليا</option>
<option>جنوب افريقيا</option>

</select>

<div class="errorText" id="countryError"></div>

</div>

</div>


<div class="row">

<div class="field" style="width:100%">
<label><span class="star">*</span> نص تعريفي بالمكتب</label>
<textarea id="desc" name="office_description" class="descBox" placeholder="تعريف مفصل بخدمات ونطاق المكتب"></textarea>
<div class="errorText" id="descError"></div>
</div>

</div>

<div class="row">

<div class="field">
<label><span class="star">*</span> البريد الإلكتروني</label>
<input type="text" id="email" name="email" placeholder="example@gmail.com">
<div class="errorText" id="emailError"></div>
</div>

<div class="field">
<label><span class="star">*</span> رقم الهاتف</label>
<input type="text" id="phone" name="phone" placeholder="05XXXXXXXX">
<div class="errorText" id="phoneError"></div>
</div>

</div>

<div class="row">

<div class="field">
<label><span class="star">*</span> كلمة المرور</label>

<div class="passBox">

<input type="password" id="pass" name="password" placeholder="إدخال كلمة مرور قوية">



</div>

<div class="errorText" id="passError"></div>

</div>

<div class="field">

<label><span class="star">*</span> تأكيد كلمة المرور</label>

<div class="passBox">

<input type="password" id="pass2" name="confirm_password" placeholder="أعد إدخال كلمة المرور">



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


function checkForm(){

document.getElementById("officeError").innerText = "";
document.getElementById("ccrError").innerText = "";
document.getElementById("masterError").innerText = "";
document.getElementById("phdError").innerText = "";
document.getElementById("bachelorError").innerText = "";
document.getElementById("descError").innerText = "";
document.getElementById("emailError").innerText = "";
document.getElementById("phoneError").innerText = "";
document.getElementById("passError").innerText = "";
document.getElementById("pass2Error").innerText = "";
document.getElementById("countryError").innerText = "";

var office = document.getElementById("office").value;
var ccr = document.getElementById("ccr").value;
var master = document.getElementById("master").value;
var phd = document.getElementById("phd").value;
var bachelor = document.getElementById("bachelor").value;
var desc = document.getElementById("desc").value;
var email = document.getElementById("email").value;
var phone = document.getElementById("phone").value;
var pass = document.getElementById("pass").value;
var pass2 = document.getElementById("pass2").value;
var country = document.getElementById("country").value;

var ok = true;

if(office == ""){
document.getElementById("officeError").innerText = "يرجى إدخال اسم المكتب.";
ok = false;
}

if(ccr == "" || ccr.length != 10 || isNaN(ccr)){
document.getElementById("ccrError").innerText = "رقم السجل التجاري يجب أن يكون 10 أرقام.";
ok = false;
}

if(master == "" || isNaN(master)){
document.getElementById("masterError").innerText = "أدخل قيمة رقمية صحيحة.";
ok = false;
}

if(phd == "" || isNaN(phd)){
document.getElementById("phdError").innerText = "أدخل قيمة رقمية صحيحة.";
ok = false;
}

if(bachelor == "" || isNaN(bachelor)){
document.getElementById("bachelorError").innerText = "أدخل قيمة رقمية صحيحة.";
ok = false;
}

if(country == ""){
document.getElementById("countryError").innerText = "يرجى اختيار الدولة.";
ok = false;
}

if(desc == ""){
document.getElementById("descError").innerText = "يرجى إدخال وصف المكتب.";
ok = false;
}

if(email == "" || email.indexOf("@") == -1){
document.getElementById("emailError").innerText = "أدخل بريد إلكتروني صحيح.";
ok = false;
}

if(phone == "" || phone.length != 10 || isNaN(phone)){
document.getElementById("phoneError").innerText = "رقم الهاتف يجب أن يكون 10 أرقام.";
ok = false;
}

if(pass == ""){
document.getElementById("passError").innerText = "أدخل كلمة المرور.";
ok = false;
}

if(pass2 == "" || pass != pass2){
document.getElementById("pass2Error").innerText = "كلمتا المرور غير متطابقتين.";
ok = false;
}

return ok;

}

</script>

</body>
</html>