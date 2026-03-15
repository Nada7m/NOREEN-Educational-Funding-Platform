<?php
session_start();

$con = new mysqli("localhost", "root", "", "noreen");
if ($con->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $con->connect_error);
}

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $users = [
        [
            "table" => "investor",
            "id_field" => "inv_id",
            "session_key" => "inv_id",
            "redirect" => "Inv00_MainPage.php"
        ],
        [
            "table" => "beneficiary",
            "id_field" => "bnf_id",
            "session_key" => "bnf_id",
            "redirect" => "Ben00_MainPage.php"
        ],
        [
            "table" => "consulting_office",
            "id_field" => "office_id",
            "session_key" => "office_id",
            "redirect" => "Con00_MainPage.php"
        ],
        [
            "table" => "admin",
            "id_field" => "admin_id",
            "session_key" => "admin_id",
            "redirect" => "Admin00_MainPage.php"
        ]
    ];

    foreach ($users as $userType) {
        $table = $userType["table"];
        $id_field = $userType["id_field"];
        $session_key = $userType["session_key"];
        $redirect = $userType["redirect"];

        $stmt = $con->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $table;
                $_SESSION[$session_key] = $user[$id_field];

                header("Location: " . $redirect);
                exit();
            }
        }
    }

    $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تسجيل الدخول</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">
<link rel="stylesheet" href="Style.css">

<style>
body{
    margin:0;
    background:#f5f4f2;
    font-family:'Noto Kufi Arabic',sans-serif;
}
.container{
    min-height:100vh;
    padding-top:120px;
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
.center{
    text-align:center;
}
.btn{
    margin-top:15px;
}
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
                <input type="text" name="email" placeholder="أدخل البريد الإلكتروني" required>
            </div>

            <label>كلمة المرور</label>
            <div class="input-group">
                <input type="password" name="password" placeholder="أدخل كلمة المرور" required>
            </div>

            <button type="submit" name="login" class="btn">تسجيل الدخول</button>

            <p class="register center">
              ليس لديك حساب؟ <a href="Main Page.html#accounts">إنشاء حساب</a>
            </p>

        </form>

    </div>
</div>

</body>
</html>