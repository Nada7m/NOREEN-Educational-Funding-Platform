<?php
session_start();
/* التحقق من تسجيل دخول المستثمر */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}
/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");
/* رقم المستثمر الحالي */
$inv_id = $_SESSION['inv_id'];
$msg = "";
$type = "";
/* عند الضغط على زر حفظ التعديلات */
if (isset($_POST["save"])) {
    /* قراءة البيانات من الفورم */
    $name  = trim($_POST["orgName"]);
    $ccr   = trim($_POST["commercial"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    /* قراءة حقول كلمة المرور */
    $currentPass = trim($_POST["currentPassword"]);
    $newPass     = trim($_POST["newPassword"]);
    $confirmPass = trim($_POST["confirmPassword"]);
    /* التحقق من البريد إذا كان مستخدمًا من مستثمر آخر */
    $checkEmail = $conn->prepare("SELECT inv_id FROM investor WHERE email = ? AND inv_id != ?");
    $checkEmail->bind_param("si", $email, $inv_id);
    $checkEmail->execute();
    $emailResult = $checkEmail->get_result();
    /* التحقق من السجل التجاري إذا كان مستخدمًا من مستثمر آخر */
    $checkCcr = $conn->prepare("SELECT inv_id FROM investor WHERE ccr_number = ? AND inv_id != ?");
    $checkCcr->bind_param("si", $ccr, $inv_id);
    $checkCcr->execute();
    $ccrResult = $checkCcr->get_result();
    /* التحقق من صحة البيانات الأساسية */
    if ($name == "" || $ccr == "" || $phone == "" || $email == "") {
        $msg = "يرجى تعبئة جميع الحقول الأساسية.";
        $type = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $ccr)) {
        $msg = "رقم السجل التجاري يجب أن يكون 10 أرقام.";
        $type = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $msg = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
        $type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "يرجى إدخال بريد إلكتروني صحيح.";
        $type = "error";
    } elseif ($emailResult->num_rows > 0) {
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
    } elseif ($ccrResult->num_rows > 0) {
        $msg = "رقم السجل التجاري مسجل مسبقًا.";
        $type = "error";
    } else {
        /* تحديث البيانات الأساسية */
        $update = $conn->prepare("
            UPDATE investor
            SET inv_name = ?, ccr_number = ?, inv_number = ?, email = ?
            WHERE inv_id = ?
        ");
        $update->bind_param("ssssi", $name, $ccr, $phone, $email, $inv_id);
        $update->execute();
        $update->close();
        /* إذا أراد المستخدم تغيير كلمة المرور */
        if ($currentPass != "" || $newPass != "" || $confirmPass != "") {
            /* جلب كلمة المرور الحالية من قاعدة البيانات */
            $getPass = $conn->prepare("SELECT password FROM investor WHERE inv_id = ?");
            $getPass->bind_param("i", $inv_id);
            $getPass->execute();
            $passResult = $getPass->get_result();
            $user = $passResult->fetch_assoc();
            $storedPass = $user["password"];
            $getPass->close();
            /* التحقق من صحة تغيير كلمة المرور */
            if ($currentPass == "" || $newPass == "" || $confirmPass == "") {
                $msg = "لتغيير كلمة المرور يجب تعبئة جميع حقول كلمة المرور.";
                $type = "error";   } elseif (!password_verify($currentPass, $storedPass)) {
                $msg = "كلمة المرور الحالية غير صحيحة.";
                $type = "error";   } elseif ($newPass != $confirmPass) {
                $msg = "كلمتا المرور الجديدتان غير متطابقتين.";
               $type = "error";  } else {
                /* تشفير كلمة المرور الجديدة ثم حفظها */
                $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
                $updatePass = $conn->prepare("UPDATE investor SET password = ? WHERE inv_id = ?");
                $updatePass->bind_param("si", $hashedPass, $inv_id);
                $updatePass->execute();
                $updatePass->close();
                $msg = "تم تحديث البيانات وكلمة المرور بنجاح.";
                $type = "success";
            }
        } else {
            /* إذا لم يغيّر كلمة المرور */
            $msg = "تم تحديث البيانات بنجاح.";
                  $type = "success";   }   }
    /* إغلاق استعلامات التحقق */
    $checkEmail->close();
    $checkCcr->close();
}
/* جلب بيانات المستثمر الحالية لعرضها في النموذج */
$stmt = $conn->prepare("
    SELECT inv_name, ccr_number, inv_number, email
    FROM investor
    WHERE inv_id = ?
");
$stmt->bind_param("i", $inv_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $investor = $result->fetch_assoc();
} else {
    die("لم يتم العثور على بيانات المستثمر");
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تعديل البيانات</title>
<!-- الخطوط والملفات المشتركة -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">
<link rel="stylesheet" href="Style.css">
<style>
/* مسافة داخل الصفحة */
.page{padding:40px;}
/* عرض الصندوق الرئيسي */
.box{width:760px;}
/* ملاحظة صغيرة تحت الحقول */
.note{font-size:12px;color:#666; margin-top:5px;}
</style>
</head>
<body>
<div class="layout">
    <!-- الشريط الجانبي -->
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="نورين">
            </div>
            <!-- روابط صفحات المستثمر -->
            <ul class="sidebar-menu">
                <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
                <li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
                <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
                <li><a href="#">المدفوعات</a></li>
            </ul>
        </div>
        <!-- زر تسجيل الخروج -->
        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>
    <div class="main-content">
        <!-- الهيدر -->
        <header class="header">
            <div class="page-title">تعديل البيانات</div>
            <!-- قائمة الإعدادات -->
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="القائمة">
                    <div class="dropdown-menu">
                        <a href="Inv02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>
        <div class="page">
            <div class="box">
                <!-- عنوان النموذج -->
                <h2>تعديل <span>بيانات المستثمر</span></h2>
                <!-- رسالة نجاح أو خطأ -->
                <?php if($msg != ""){ ?>
                <div class="message <?php echo $type; ?>">
                    <?php echo $msg; ?>
                </div>
                <?php } ?>
                <!-- نموذج تعديل البيانات -->
                <form method="post">
                    <!-- الصف الأول -->
                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> اسم الجهة</label>
                            <input type="text" name="orgName" value="<?php echo htmlspecialchars($investor['inv_name']); ?>">
                        </div>
                        <div class="field">
                            <label><span class="star">*</span> رقم السجل التجاري</label>
                            <input type="text" name="commercial" value="<?php echo htmlspecialchars($investor['ccr_number']); ?>">
                        </div>
                    </div>
                    <!-- الصف الثاني -->
                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> رقم الهاتف</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($investor['inv_number']); ?>">
                        </div>

                        <div class="field">
                            <label><span class="star">*</span> البريد الإلكتروني</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($investor['email']); ?>">
                        </div>
                    </div>
                    <!-- حقول تغيير كلمة المرور -->
                    <div class="row">
                        <div class="field">
                            <label>كلمة المرور الحالية</label>
                            <div class="passBox">
                                <input type="password" id="currentPass" name="currentPassword" autocomplete="off">
                                <span class="eye" onclick="showPass('currentPass')">👁</span>
                            </div>
                        </div>
                        <div class="field">
                            <label>كلمة المرور الجديدة</label>
                            <div class="passBox">
                                <input type="password" id="newPass" name="newPassword" autocomplete="new-password">
                                <span class="eye" onclick="showPass('newPass')">👁</span>
                            </div>
                        </div>
                    </div>
                    <!-- تأكيد كلمة المرور الجديدة -->
                    <div class="row">
                        <div class="field">
                            <label>تأكيد كلمة المرور الجديدة</label>
                            <div class="passBox">
                                <input type="password" id="confirmPass" name="confirmPassword" autocomplete="new-password">
                                <span class="eye" onclick="showPass('confirmPass')">👁</span>
                            </div>
                            <div class="note">اتركي حقول كلمة المرور فارغة إذا كنتِ لا تريدين تغييرها.</div>
                        </div>

                        <div class="field"></div>
                    </div>
                    <!-- أزرار الحفظ والإلغاء -->
                    <div class="center">
                        <button type="submit" class="btn" name="save">حفظ التعديلات</button>

                        <button type="button" class="btn"
                        onclick="window.location.href='Inv02_Profile.php'"
                        style="background:#888;margin-right:10px;">
                        إلغاء
                        </button>
                    </div>

                </form>
            </div>
        </div>
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
</script>
</body>
</html>