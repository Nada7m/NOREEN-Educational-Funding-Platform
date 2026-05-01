<?php

// الاتصال بقاعدة البيانات
$con = mysqli_connect("localhost","root","","noreen");

// متغيرات لرسالة النجاح أو الخطأ
$msg = "";
$type = "";

// التحقق إذا تم الضغط على زر إنشاء الحساب
if(isset($_POST["save"])){

// استقبال البيانات من الفورم
$office = $_POST["office_name"]; // اسم المكتب
$ccr = $_POST["ccr_number"]; // رقم السجل التجاري
$email = $_POST["email"]; // البريد الإلكتروني
$desc = $_POST["office_description"]; // وصف المكتب
$bachelor = $_POST["bachelor_fee"]; // رسوم البكالوريوس
$master = $_POST["master_fee"]; // رسوم الماجستير
$phd = $_POST["phd_fee"]; // رسوم الدكتوراه
$phone = $_POST["phone"]; // رقم الهاتف
$pass = $_POST["password"]; // كلمة المرور

// استقبال الدول المختارة كمصفوفة
$countries = isset($_POST["country"]) ? $_POST["country"] : [];

// التحقق من عدم تكرار البريد الإلكتروني
$checkEmail = mysqli_query($con,"SELECT * FROM consulting_office WHERE email='$email'");

// التحقق من عدم تكرار السجل التجاري
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

// تشفير كلمة المرور للحماية
$newpass = password_hash($pass, PASSWORD_DEFAULT);

// إدخال بيانات المكتب في جدول consulting_office
$sql = "INSERT INTO consulting_office
(ccr_number,email,office_name,office_description,Bachelor_fee,Masters_fee,Phd_fee,password,phone)
VALUES
('$ccr','$email','$office','$desc','$bachelor','$master','$phd','$newpass','$phone')";

if(mysqli_query($con,$sql)){

// الحصول على رقم المكتب الذي تم إدخاله
$office_id = mysqli_insert_id($con);

// إدخال الدول المختارة وربطها بالمكتب
foreach($countries as $oneCountry){

$sql_country = "INSERT INTO office_country (office_id, con_name)
VALUES ('$office_id', '$oneCountry')";

mysqli_query($con, $sql_country);
}

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

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">
<title>إنشاء حساب مكتب استشاري</title>

<!-- خط عربي -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">

<!-- مكتبة select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="Style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- مكتبة select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

<!-- قائمة الدول ويمكن اختيار أكثر من دولة -->
<select id="country" name="country[]" multiple>
<option value="امريكا">امريكا</option>
<option value="فرنسا">فرنسا</option>
<option value="ايرلندا">ايرلندا</option>
<option value="مالطا">مالطا</option>
<option value="الهند">الهند</option>
<option value="الصين">الصين</option>
<option value="اليابان">اليابان</option>
<option value="بريطانيا">بريطانيا</option>
<option value="نيوزلندا">نيوزلندا</option>
<option value="ماليزيا">ماليزيا</option>
<option value="تركيا">تركيا</option>
<option value="المانيا">المانيا</option>
<option value="كندا">كندا</option>
<option value="استراليا">استراليا</option>
<option value="جنوب افريقيا">جنوب افريقيا</option>
<option value="اسبانيا">اسبانيا</option>
<option value="ايطاليا">ايطاليا</option>
<option value="هولندا">هولندا</option>
<option value="بلجيكا">بلجيكا</option>
<option value="سويسرا">سويسرا</option>
<option value="السويد">السويد</option>
<option value="النرويج">النرويج</option>
<option value="فنلندا">فنلندا</option>
<option value="الدنمارك">الدنمارك</option>
<option value="بولندا">بولندا</option>
<option value="النمسا">النمسا</option>
<option value="التشيك">التشيك</option>
<option value="المجر">المجر</option>
<option value="البرتغال">البرتغال</option>
<option value="اليونان">اليونان</option>
<option value="روسيا">روسيا</option>
<option value="كوريا الجنوبية">كوريا الجنوبية</option>
<option value="سنغافورة">سنغافورة</option>
<option value="تايلاند">تايلاند</option>
<option value="اندونيسيا">اندونيسيا</option>
<option value="الفلبين">الفلبين</option>
<option value="فيتنام">فيتنام</option>
<option value="مصر">مصر</option>
<option value="الامارات">الامارات</option>
<option value="الكويت">الكويت</option>
<option value="قطر">قطر</option>
<option value="الاردن">الاردن</option>

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
<button type="submit" class="btn" name="save">إنشاء حساب</button>
<div class="login-text">
    <a href="login.php">هل ترغب بتسجيل الدخول؟</a>
</div>
</div>
</form>
</div>
</div>
<script>

$(document).ready(function(){
$('#country').select2({
placeholder:"اختر الدول من القائمة أدناه",
width:'100%'
});
});

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
var country = $('#country').val();
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

if(country == null || country.length == 0){
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