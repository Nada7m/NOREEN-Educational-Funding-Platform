<?php

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");

/* الرسالة الافتراضية */
$msg = "";
$type = "";

/* تنفيذ إنشاء الحساب بعد الضغط على زر الحفظ */
if (isset($_POST["save"])) {

    /* استقبال البيانات المدخلة من النموذج */
    $first = trim($_POST["firstName"]);
    $last = trim($_POST["lastName"]);
    $degree = trim($_POST["degree"]);
    $major = trim($_POST["major"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $pass = $_POST["password"];

    /** التحقق من عدم تكرار البريد الإلكتروني داخل النظام **/
    $check_stmt = mysqli_prepare($con, "
        SELECT bnf_id
        FROM beneficiary
        WHERE email = ?
    ");

    mysqli_stmt_bind_param($check_stmt, "s", $email);

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {

        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";

    } else {

        /** تشفير كلمة المرور قبل حفظها داخل قاعدة البيانات **/
        $newpass = password_hash($pass, PASSWORD_DEFAULT);

        /* تجهيز استعلام إضافة المستفيد الجديد */
        $insert_stmt = mysqli_prepare($con, "
            INSERT INTO beneficiary
            (f_name, l_name, degree_level, sch_field, phone_num, email, password)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $insert_stmt,
            "sssssss",
            $first,
            $last,
            $degree,
            $major,
            $phone,
            $email,
            $newpass
        );

        /** حفظ بيانات المستفيد الجديدة داخل النظام **/
        if (mysqli_stmt_execute($insert_stmt)) {

            $msg = "تم إنشاء الحساب بنجاح.";
            $type = "success";

        } else {

            $msg = "حدث خطأ أثناء حفظ البيانات.";
            $type = "error";
        }

        mysqli_stmt_close($insert_stmt);
    }

    mysqli_stmt_close($check_stmt);
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<title>إنشاء حساب مستفيد</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="Style.css">

<style>

/* نص تسجيل الدخول أسفل الزر */
.login-text{ text-align:center; margin-top:12px; color:#777777; font-size:14px; }

/* رابط تسجيل الدخول */
.login-text a{ color:#777777; text-decoration:none; }

/* تأثير عند المرور على الرابط */
.login-text a:hover{ text-decoration:underline; }

</style>
</head>

<body>

<div class="container">

    <div class="box">

        <h2>إنشاء حساب <span>مستفيد</span></h2>

        <!-- عرض الرسالة بعد تنفيذ العملية -->
        <?php if($msg != ""){ ?>

        <div class="message <?php echo $type; ?>">
            <?php echo $msg; ?>
        </div>

        <?php } ?>

        <!-- نموذج إنشاء الحساب -->
        <form method="post" onsubmit="return checkForm()">

            <div class="row">

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        الاسم الأول
                    </label>

                    <input type="text" id="first" name="firstName" placeholder="الرجاء إدخال الاسم الأول">

                    <div class="errorText" id="firstError"></div>

                </div>

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        الاسم الأخير
                    </label>

                    <input type="text" id="last" name="lastName" placeholder="الرجاء إدخال الاسم الأخير">

                    <div class="errorText" id="lastError"></div>

                </div>

            </div>

            <div class="row">

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        المؤهل الدراسي
                    </label>

                    <select id="degree" name="degree">
                        <option>اختر المؤهل الدراسي من القائمة أدناه</option>
                        <option>ثانوي</option>
                        <option>بكالوريوس</option>
                        <option>ماجستير</option>
                        <option>دكتوراه</option>
                    </select>

                    <div class="errorText" id="degreeError"></div>

                </div>

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        المجال الدراسي
                    </label>

                    <select id="major" name="major">
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
                        <option>لا يوجد</option>
                    </select>

                    <div class="errorText" id="majorError"></div>

                </div>

            </div>

            <div class="row">

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        رقم الهاتف
                    </label>

                    <input type="text" id="phone" name="phone" placeholder="05XXXXXXXX">

                    <div class="errorText" id="phoneError"></div>

                </div>

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        البريد الإلكتروني
                    </label>

                    <input type="email" id="email" name="email" placeholder="example@gmail.com">

                    <div class="errorText" id="emailError"></div>

                </div>

            </div>

            <div class="row">

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        كلمة المرور
                    </label>

                    <div class="passBox">

                        <input type="password" id="pass" name="password">

                        <!-- إظهار وإخفاء كلمة المرور -->
                        <span class="eye" onclick="showPass('pass')">👁</span>

                    </div>

                    <div class="errorText" id="passError"></div>

                </div>

                <div class="field">

                    <label>
                        <span class="star">*</span>
                        تأكيد كلمة المرور
                    </label>

                    <div class="passBox">

                        <input type="password" id="pass2" name="confirmPassword">

                        <!-- إظهار وإخفاء تأكيد كلمة المرور -->
                        <span class="eye" onclick="showPass('pass2')">👁</span>

                    </div>

                    <div class="errorText" id="pass2Error"></div>

                </div>

            </div>

            <div class="center">
                <button type="submit" class="btn" name="save">إنشاء حساب</button>
            </div>

            <div class="login-text">
                <a href="login.php">هل ترغب بتسجيل الدخول؟</a>
            </div>

        </form>

    </div>

</div>

<script>

/* إظهار أو إخفاء كلمة المرور */
function showPass(id){

    var x = document.getElementById(id);

    if(x.type == "password"){
        x.type = "text";
    }
    else{
        x.type = "password";
    }
}

/* التحقق من صحة الحقول قبل إرسال النموذج */
function checkForm(){

    /* تصفير رسائل الأخطاء السابقة */
    document.getElementById("firstError").innerText = "";
    document.getElementById("lastError").innerText = "";
    document.getElementById("degreeError").innerText = "";
    document.getElementById("majorError").innerText = "";
    document.getElementById("phoneError").innerText = "";
    document.getElementById("emailError").innerText = "";
    document.getElementById("passError").innerText = "";
    document.getElementById("pass2Error").innerText = "";

    /* جلب القيم المدخلة */
    var first = document.getElementById("first").value;
    var last = document.getElementById("last").value;
    var degree = document.getElementById("degree").value;
    var major = document.getElementById("major").value;
    var phone = document.getElementById("phone").value;
    var email = document.getElementById("email").value;
    var pass = document.getElementById("pass").value;
    var pass2 = document.getElementById("pass2").value;

    var ok = true;

    /* التحقق من الاسم الأول */
    if(first == ""){
        document.getElementById("firstError").innerText = "يرجى إدخال الاسم الأول.";
        ok = false;
    }

    /* التحقق من الاسم الأخير */
    if(last == ""){
        document.getElementById("lastError").innerText = "يرجى إدخال الاسم الأخير.";
        ok = false;
    }

    /** التأكد من اختيار المؤهل الدراسي **/
    if(degree == "اختر المؤهل الدراسي من القائمة أدناه"){
        document.getElementById("degreeError").innerText = "يرجى اختيار المؤهل الدراسي.";
        ok = false;
    }

    /** التأكد من اختيار المجال الدراسي **/
    if(major == "اختر المجال الدراسي من القائمة أدناه"){
        document.getElementById("majorError").innerText = "يرجى اختيار المجال الدراسي.";
        ok = false;
    }

    /* التحقق من رقم الهاتف */
    if(phone == ""){
        document.getElementById("phoneError").innerText = "يرجى إدخال رقم الهاتف.";
        ok = false;
    }

    /** التحقق من أن رقم الهاتف مكوّن من 10 أرقام **/
    else if(phone.length != 10 || isNaN(phone)){
        document.getElementById("phoneError").innerText = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
        ok = false;
    }

    /* التحقق من البريد الإلكتروني */
    if(email == ""){
        document.getElementById("emailError").innerText = "يرجى إدخال البريد الإلكتروني.";
        ok = false;
    }

    /** التأكد من احتواء البريد الإلكتروني على علامة @ **/
    else if(email.indexOf("@") == -1){
        document.getElementById("emailError").innerText = "يرجى إدخال بريد إلكتروني صحيح.";
        ok = false;
    }

    /* التحقق من كلمة المرور */
    if(pass == ""){
        document.getElementById("passError").innerText = "يرجى إدخال كلمة المرور.";
        ok = false;
    }

    /* التحقق من تأكيد كلمة المرور */
    if(pass2 == ""){
        document.getElementById("pass2Error").innerText = "يرجى تأكيد كلمة المرور.";
        ok = false;
    }

    /** التأكد من تطابق كلمتي المرور **/
    else if(pass != pass2){
        document.getElementById("pass2Error").innerText = "كلمتا المرور غير متطابقتين.";
        ok = false;
    }

    return ok;
}

</script>

</body>
</html>