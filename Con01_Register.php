<?php

$msg="";
$type="";

$conn = new mysqli("localhost","root","","noreen");

if($conn->connect_error){
die("Connection failed");
}

if($_SERVER["REQUEST_METHOD"]=="POST"){

$office = $_POST["office_name"];
$ccr = $_POST["ccr_number"];
$email = $_POST["email"];
$desc = $_POST["office_description"];
$bachelor = $_POST["bachelor_fee"];
$master = $_POST["master_fee"];
$phd = $_POST["phd_fee"];
$password = $_POST["password"];
$phone = $_POST["phone"];

$sql = "INSERT INTO consulting_office
(ccr_number,email,office_name,office_description,Bachelor_fee,Masters_fee,Phd_fee,password,phone)

VALUES
('$ccr','$email','$office','$desc','$bachelor','$master','$phd','$password','$phone')";

if($conn->query($sql)){
$msg="تم إنشاء الحساب بنجاح";
$type="success";
}else{
$msg="حدث خطأ أثناء إنشاء الحساب";
$type="error";
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

<form method="post">

<div class="row">

<div class="field">
<label><span class="star">*</span> اسم المكتب</label>
<input type="text" name="office_name"
placeholder="الرجاء إدخال الاسم الرسمي للمكتب">
</div>

<div class="field">
<label><span class="star">*</span> رقم السجل التجاري</label>
<input type="text" name="ccr_number"
placeholder="مثل : 101023456">
</div>

</div>


<div class="row">

<div class="field">
<label><span class="star">*</span> رسوم خدمات الماجستير</label>
<input type="text" name="master_fee"
placeholder="القيمة بالريال">
</div>

<div class="field">
<label><span class="star">*</span> رسوم خدمات الدكتوراه</label>
<input type="text" name="phd_fee"
placeholder="القيمة بالريال">
</div>

</div>


<div class="row">

<div class="field">
<label><span class="star">*</span> رسوم خدمات البكالوريوس</label>
<input type="text" name="bachelor_fee"
placeholder="القيمة بالريال">
</div>

<div class="field"></div>

</div>


<div class="row">

<div class="field" style="width:100%">

<label><span class="star">*</span> نص تعريفي بالمكتب</label>

<textarea
name="office_description"
class="descBox"
placeholder="تعريف مفصل بخدمات ونطاق المكتب"></textarea>

<hr class="line">

</div>

</div>


<div class="row">

<div class="field">
<label><span class="star">*</span> البريد الإلكتروني</label>
<input type="text" name="email"
placeholder="example@gmail.com">
</div>

<div class="field">
<label><span class="star">*</span> رقم الهاتف</label>
<input type="text" name="phone"
placeholder="+9665XXXXXXXX">
</div>

</div>


<div class="row">

<div class="field">
<label><span class="star">*</span> كلمة المرور</label>
<input type="password" name="password"
placeholder="إدخال كلمة مرور قوية">
</div>

<div class="field">
<label><span class="star">*</span> تأكيد كلمة المرور</label>
<input type="password" name="confirm_password"
placeholder="أعد إدخال كلمة المرور">
</div>

</div>


<div class="center">

<button type="submit" class="btn">
إنشاء حساب
</button>

</div>

</form>

</div>
</div>

</body>
</html>