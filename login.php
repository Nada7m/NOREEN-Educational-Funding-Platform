<?php
session_start();

// الاتصال بقاعدة البيانات
$con = new mysqli("localhost", "root", "", "noreen");
if ($con->connect_error) { 
    die("فشل الاتصال بالقاعدة: " . $con->connect_error); 
}

$error = "";
$success_msg = ""; // متغير جديد للتأكد من نجاح العملية داخلياً

if(isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $tables = ['beneficiary', 'investor', 'consulting_office'];
    $found = false;

    foreach($tables as $table){
        // (الباسورد مشفر) نبحث عن المستخدم بواسطة البريد الإلكتروني فقط
        $stmt = $con->prepare("SELECT * FROM $table WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            
            //  التحقق من كلمة المرور المشفرة باستخدام password_verify
            
            if(password_verify($password, $user['password'])){
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $table;
                
                $found = true;
                $success_msg = "تم التحقق بنجاح!";
                break; 
            }
        }
    }

    if(!$found){ 
        $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة."; 
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تسجيل الدخول</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
/* تعديلات بسيطة لتوسيط الصندوق عموديًا */
body{
    margin:0;
    background:#f5f4f2;
    font-family:'Noto Kufi Arabic',sans-serif;
}
.container{
    min-height:100vh;
    padding-top:120px; /* يرفع الصندوق شوي */
}
.box{
    width:650px;
    max-width:90%;
    margin:auto;
}
.error{
    text-align:center;
    margin-bottom:10px;
    color:red;
}
.center{text-align:center;}
</style>
</head>
<body>

<div class="container">
    <div class="box">

        <h2>مرحباً بعودتك</h2>
        <p class="subtitle center">سجل الدخول للمتابعة</p>

        <?php if(!empty($error)) echo '<p class="error">'.$error.'</p>'; ?>

        <form method="POST">

            <label>البريد الإلكتروني</label>
            <div class="input-group">
                <input type="text" name="email" placeholder="أدخل البريد الإلكتروني أو اسم المستخدم" required>
            </div>

            <label>كلمة المرور</label>
            <div class="input-group">
                <input type="password" name="password" placeholder="أدخل كلمة المرور" required>
            </div>

            <button type="submit" name="login" class="btn">تسجيل الدخول</button>
<style>

.btn {
    margin-top: 15px; 
}
</style>

            <p class="register center">
  ليس لديك حساب؟ <a href="Main Page.html#accounts">إنشاء حساب</a>

</p>





            </p>

        </form>

    </div>
</div>

</body>
</html>
