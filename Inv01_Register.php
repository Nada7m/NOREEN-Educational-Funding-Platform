<?php
// الاتصال بقاعدة البيانات
$con = mysqli_connect("localhost", "root", "", "noreen");
// متغيرات لرسالة التنبيه ونوعها
$msg = "";
$type = "";
// لو فشل الاتصال يوقف الصفحة ويظهر رسالة
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}
// يشتغل هذا الجزء فقط إذا المستخدم ضغط زر "إنشاء حساب"
if (isset($_POST["save"])) {
    // أخذ القيم من الفورم
    $name  = $_POST["orgName"];
    $ccr   = $_POST["commercial"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $pass  = $_POST["password"];

    // التحقق هل البريد الإلكتروني مستخدم من قبل
    $stmt1 = mysqli_prepare($con, "SELECT inv_id FROM investor WHERE email = ?");
    mysqli_stmt_bind_param($stmt1, "s", $email);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_store_result($stmt1);
    // التحقق هل رقم السجل التجاري مستخدم من قبل
    $stmt2 = mysqli_prepare($con, "SELECT inv_id FROM investor WHERE ccr_number = ?");
    mysqli_stmt_bind_param($stmt2, "s", $ccr);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_store_result($stmt2);
    // إذا البريد موجود مسبقًا
    if (mysqli_stmt_num_rows($stmt1) > 0) {
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
    }
    // إذا السجل التجاري موجود مسبقًا
    elseif (mysqli_stmt_num_rows($stmt2) > 0) {
        $msg = "رقم السجل التجاري مسجل مسبقًا.";
        $type = "error";
    }
    // إذا كل شيء سليم، نخزن البيانات
    else {
        // تشفير كلمة المرور قبل حفظها
        $newpass = password_hash($pass, PASSWORD_DEFAULT);

        // تجهيز أمر الإضافة
        $stmt3 = mysqli_prepare(
            $con,
            "INSERT INTO investor (inv_name, ccr_number, email, inv_number, password)
             VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt3, "sssss", $name, $ccr, $email, $phone, $newpass);
        // إذا تم الحفظ بنجاح ينتقل لصفحة تسجيل الدخول
        if (mysqli_stmt_execute($stmt3)) {
            header("Location: login.php");
            exit();
        } else {
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
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">
    <!-- ملف التنسيق الخارجي -->
    <link rel="stylesheet" href="Style.css">
</head>
<body>
    <!-- الصندوق الرئيسي -->
    <div class="container">
        <div class="box">
            <!-- عنوان الصفحة -->
            <h2>إنشاء حساب <span>مستثمر</span></h2>
            <!-- تظهر الرسالة فقط إذا كان فيها نص -->
            <?php if ($msg != "") { ?>
                <div class="message <?php echo $type; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php } ?>
            <!-- الفورم -->
            <form method="post" onsubmit="return checkForm()">
                <!-- الصف الأول -->
                <div class="row">
                    <!-- اسم الجهة -->
                    <div class="field">
                        <label>* اسم الجهة</label>
                        <input type="text" id="name" name="orgName">
                        <div class="errorText" id="nameError"></div>
                    </div>
                    <!-- رقم السجل التجاري -->
                    <div class="field">
                        <label>* رقم السجل التجاري</label>
                        <input type="text" id="ccr" name="commercial">
                        <div class="errorText" id="ccrError"></div>
                    </div>
                </div>
                <!-- الصف الثاني -->
                <div class="row">
                    <!-- البريد الإلكتروني -->
                    <div class="field">
                        <label>* البريد الإلكتروني</label>
                        <input type="text" id="email" name="email">
                        <div class="errorText" id="emailError"></div>
                    </div>

                    <!-- رقم الهاتف -->
                    <div class="field">
                        <label>* رقم الهاتف</label>
                        <input type="text" id="phone" name="phone">
                        <div class="errorText" id="phoneError"></div>
                    </div>

                </div>

                <!-- الصف الثالث -->
                <div class="row">

                    <!-- كلمة المرور -->
                    <div class="field">
                        <label>* كلمة المرور</label>
                        <div class="passBox">
                            <input type="password" id="pass" name="password">
                            <span class="eye" onclick="showPass('pass')">👁</span>
                        </div>
                        <div class="errorText" id="passError"></div>
                    </div>

                    <!-- تأكيد كلمة المرور -->
                    <div class="field">
                        <label>* تأكيد كلمة المرور</label>
                        <div class="passBox">
                            <input type="password" id="pass2" name="confirmPassword">
                            <span class="eye" onclick="showPass('pass2')">👁</span>
                        </div>
                        <div class="errorText" id="pass2Error"></div>
                    </div>

                </div>
                <!-- زر الإنشاء -->
                <div class="center">
                    <button type="submit" class="btn" name="save">إنشاء حساب</button>
                </div>

            </form>

        </div>
    </div>
    <script>
        // هذه الدالة تبدل بين إخفاء وإظهار كلمة المرور
        function showPass(id) {
            var input = document.getElementById(id);

            if (input.type == "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }
        // هذه الدالة تتأكد أن البيانات صحيحة قبل إرسال الفورم
        function checkForm() {

            // أول شيء: نمسح رسائل الخطأ القديمة
            document.getElementById("nameError").innerText = "";
            document.getElementById("ccrError").innerText = "";
            document.getElementById("emailError").innerText = "";
            document.getElementById("phoneError").innerText = "";
            document.getElementById("passError").innerText = "";
            document.getElementById("pass2Error").innerText = "";
            // نقرأ القيم من الحقول
            var name  = document.getElementById("name").value;
            var ccr   = document.getElementById("ccr").value;
            var email = document.getElementById("email").value;
            var phone = document.getElementById("phone").value;
            var pass  = document.getElementById("pass").value;
            var pass2 = document.getElementById("pass2").value;

            // التحقق من اسم الجهة
            if (name == "") {
                document.getElementById("nameError").innerText = "يرجى إدخال اسم الجهة.";
                return false;
            }
            // التحقق من السجل التجاري
            if (ccr == "") {
                document.getElementById("ccrError").innerText = "يرجى إدخال رقم السجل التجاري.";
                return false;
            }
            if (ccr.length != 10 || isNaN(ccr)) {
                document.getElementById("ccrError").innerText = "يرجى إدخال رقم سجل تجاري مكوّن من 10 أرقام.";
                return false;
            }
            // التحقق من البريد
            if (email == "") {
                document.getElementById("emailError").innerText = "يرجى إدخال البريد الإلكتروني.";
                return false;
            }
            if (email.indexOf("@") == -1) {
                document.getElementById("emailError").innerText = "يرجى إدخال بريد إلكتروني صحيح.";
                return false;
            }
            // التحقق من الجوال
            if (phone == "") {
                document.getElementById("phoneError").innerText = "يرجى إدخال رقم الهاتف.";
                return false;
            }

            if (phone.length != 10 || isNaN(phone)) {
                document.getElementById("phoneError").innerText = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
                return false;
            }

            // التحقق من كلمة المرور
            if (pass == "") {
                document.getElementById("passError").innerText = "يرجى إدخال كلمة المرور.";
                return false;
            }

            // التحقق من تأكيد كلمة المرور
            if (pass2 == "") {
                document.getElementById("pass2Error").innerText = "يرجى تأكيد كلمة المرور.";
                return false;
            }

            if (pass != pass2) {
                document.getElementById("pass2Error").innerText = "كلمتا المرور غير متطابقتين.";
                return false;
            }

            // إذا وصلت هنا، يعني كل شيء صحيح
            return true;
        }
    </script>

</body>
</html>