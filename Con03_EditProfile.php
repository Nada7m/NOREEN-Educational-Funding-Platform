<?php
session_start();
/* التحقق من تسجيل دخول المكتب */
if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}
/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");
/* رقم المكتب الحالي */
$office_id = $_SESSION['office_id'];
$msg = "";
$type = "";
// عند الضغط على زر حفظ التعديلات
if (isset($_POST["save"])) {
    // استقبال البيانات من الفورم
    $office_name = trim($_POST["office_name"]);
    $ccr_number  = trim($_POST["ccr_number"]);
    $phone       = trim($_POST["phone"]);
    $email       = trim($_POST["email"]);
    $description = trim($_POST["office_description"]);
    $bachelor_fee = trim($_POST["bachelor_fee"]);
    $masters_fee  = trim($_POST["masters_fee"]);
    $phd_fee      = trim($_POST["phd_fee"]);
    $countries_text = trim($_POST["countries"]);
    $currentPass = trim($_POST["currentPassword"]);
    $newPass     = trim($_POST["newPassword"]);
    $confirmPass = trim($_POST["confirmPassword"]);
    // التحقق من عدم تكرار البريد الإلكتروني
    $checkEmail = $conn->prepare("SELECT office_id FROM consulting_office WHERE email = ? AND office_id != ?");
    $checkEmail->bind_param("si", $email, $office_id);
    $checkEmail->execute();
    $emailResult = $checkEmail->get_result();
    // التحقق من عدم تكرار السجل التجاري
    $checkCcr = $conn->prepare("SELECT office_id FROM consulting_office WHERE ccr_number = ? AND office_id != ?");
    $checkCcr->bind_param("si", $ccr_number, $office_id);
    $checkCcr->execute();
    $ccrResult = $checkCcr->get_result();
    // التحقق من تعبئة الحقول الأساسية
    if ($office_name == "" || $ccr_number == "" || $phone == "" || $email == "" || $description == "") {
        $msg = "يرجى تعبئة جميع الحقول الأساسية.";
        $type = "error";
    // التحقق من السجل التجاري
    } elseif (!preg_match("/^[0-9]{10}$/", $ccr_number)) {
        $msg = "رقم السجل التجاري يجب أن يكون 10 أرقام.";
        $type = "error";
     // التحقق من رقم الهاتف
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $msg = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
        $type = "error";
     // التحقق من البريد الإلكتروني
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "يرجى إدخال بريد إلكتروني صحيح.";
        $type = "error";
     // التحقق من صحة الرسوم
    } elseif (!is_numeric($bachelor_fee) || $bachelor_fee < 0 || !is_numeric($masters_fee) || $masters_fee < 0 || !is_numeric($phd_fee) || $phd_fee < 0) {
        $msg = "يرجى إدخال رسوم صحيحة.";
        $type = "error";
     // إذا البريد مستخدم مسبقًا
    } elseif ($emailResult->num_rows > 0) {
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
     // إذا السجل التجاري مستخدم مسبقًا
    } elseif ($ccrResult->num_rows > 0) {
        $msg = "رقم السجل التجاري مستخدم مسبقًا.";
        $type = "error";
    } else {
        // تحديث البيانات الأساسية
        $update = $conn->prepare("
            UPDATE consulting_office
            SET office_name = ?, ccr_number = ?, email = ?, office_description = ?, Bachelor_fee = ?, Masters_fee = ?, Phd_fee = ?, phone = ?
            WHERE office_id = ?
        ");
        $update->bind_param(
            "ssssdddsi",
            $office_name,
            $ccr_number,
            $email,
            $description,
            $bachelor_fee,
            $masters_fee,
            $phd_fee,
            $phone,
            $office_id
        );
      // تنفيذ التحديث
        $update->execute();
     // إغلاق الاستعلام
        $update->close();
        // حذف الدول القديمة
        $deleteCountries = $conn->prepare("DELETE FROM office_country WHERE office_id = ?");
        $deleteCountries->bind_param("i", $office_id);
        $deleteCountries->execute();
        $deleteCountries->close();
           // إضافة الدول الجديدة
          if ($countries_text != "") {
           // تحويل النص إلى مصفوفة
            $countries_array = explode(",", $countries_text);
            // تجهيز استعلام الإدخال
            $insertCountry = $conn->prepare("INSERT INTO office_country (office_id, con_name) VALUES (?, ?)");
             // إدخال كل دولة
            foreach ($countries_array as $country) {
                $country = trim($country);
                if ($country != "") {
                    $insertCountry->bind_param("is", $office_id, $country);
                    $insertCountry->execute();
                }
            }
            $insertCountry->close();
        }
        // إذا أراد المستخدم تغيير كلمة المرور
        if ($currentPass != "" || $newPass != "" || $confirmPass != "") {
            // جلب كلمة المرور الحالية
            $getPass = $conn->prepare("SELECT password FROM consulting_office WHERE office_id = ?");
            $getPass->bind_param("i", $office_id);
            $getPass->execute();
            $passResult = $getPass->get_result();
            $user = $passResult->fetch_assoc();
            $storedPass = $user["password"];
            $getPass->close();
            // التحقق من تعبئة جميع حقول كلمة المرور
            if ($currentPass == "" || $newPass == "" || $confirmPass == "") {
                $msg = "لتغيير كلمة المرور يجب تعبئة جميع حقول كلمة المرور.";
                $type = "error"; } 
                // التحقق من كلمة المرور الحالية
                elseif (!password_verify($currentPass, $storedPass)) {
                $msg = "كلمة المرور الحالية غير صحيحة.";
                $type = "error"; } 
                 // التحقق من تطابق كلمة المرور الجديدة
                elseif ($newPass != $confirmPass) {
                $msg = "كلمتا المرور الجديدتان غير متطابقتين.";
                $type = "error";     }
                 else {           
                // تشفير كلمة المرور الجديدة
                $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
                // تحديث كلمة المرور
                $updatePass = $conn->prepare("UPDATE consulting_office SET password = ? WHERE office_id = ?");
                $updatePass->bind_param("si", $hashedPass, $office_id);
                $updatePass->execute();
                $updatePass->close();

                $msg = "تم تحديث البيانات وكلمة المرور بنجاح.";
                $type = "success";
            }  } else {   
             // إذا لم يتم تغيير كلمة المرور
            $msg = "تم تحديث البيانات بنجاح.";
            $type = "success";     }   }
    $checkEmail->close();
    $checkCcr->close();
}
/* جلب بيانات المكتب الحالية */
$stmt = $conn->prepare("
    SELECT office_name, ccr_number, email, office_description, Bachelor_fee, Masters_fee, Phd_fee, phone
    FROM consulting_office
    WHERE office_id = ?");
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();
// إذا تم العثور على بيانات
if ($result->num_rows > 0) {
    $office = $result->fetch_assoc();
} else {
    // إذا لم يتم العثور على بيانات
    die("لم يتم العثور على بيانات المكتب")}
$stmt->close();
/* جلب الدول الحالية */
$countryStmt = $conn->prepare("SELECT con_name
    FROM office_country WHERE office_id = ?");
$countryStmt->bind_param("i", $office_id);
$countryStmt->execute();
$countryResult = $countryStmt->get_result();
// مصفوفة لتخزين الدول
$countries = [];
// إضافة الدول داخل المصفوفة
while ($row = $countryResult->fetch_assoc()) {
    // تحويل الدول إلى نص
    $countries[] = $row['con_name'];}

$countryStmt->close();

$countries_text = implode(", ", $countries);

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تعديل البيانات</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=6">
<link rel="stylesheet" href="Style.css">

<style>.page{padding:40px;}.box{width:760px;}.note{font-size:12px;color:#666; margin-top:5px;}
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="شعار نورين">
            </div>
            <ul class="sidebar-menu">
                <li><a href="Con00_MainPage.php">الرئيسية</a></li>
                <li><a href="Con04_AdmissionReq.php">إدارة طلبات القبول</a></li>
                <li><a href="Con03_Consultations.php">الاستشارات</a></li>
                <li><a href="Con08_ReqRating.php">تقييمات المستفيدين</a></li>
            </ul>
        </div>
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
        <header class="header">
            <div class="page-title">تعديل البيانات</div>
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="القائمة">
                    <div class="dropdown-menu">
                        <a href="Off02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page">
            <div class="box">
                <h2>تعديل <span>بيانات المكتب الاستشاري</span></h2>
                <?php if($msg != ""){ ?>
                <div class="message <?php echo $type; ?>">
                    <?php echo $msg; ?>
                </div>
                <?php } ?>

                <form method="post">

                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> اسم المكتب</label>
                            <input type="text" name="office_name" value="<?php echo htmlspecialchars($office['office_name']); ?>">
                        </div>

                        <div class="field">
                            <label><span class="star">*</span> السجل التجاري</label>
                            <input type="text" name="ccr_number" value="<?php echo htmlspecialchars($office['ccr_number']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> رقم الهاتف</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($office['phone']); ?>">
                        </div>

                        <div class="field">
                            <label><span class="star">*</span> البريد الإلكتروني</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($office['email']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> وصف المكتب</label>
                            <input type="text" name="office_description" value="<?php echo htmlspecialchars($office['office_description']); ?>">
                        </div>

                        <div class="field">
                            <label>الدول المتاحة</label>
                            <input type="text" name="countries" value="<?php echo htmlspecialchars($countries_text); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> رسوم البكالوريوس</label>
                            <input type="number" step="0.01" name="bachelor_fee" value="<?php echo htmlspecialchars($office['Bachelor_fee']); ?>">
                        </div>

                        <div class="field">
                            <label><span class="star">*</span> رسوم الماجستير</label>
                            <input type="number" step="0.01" name="masters_fee" value="<?php echo htmlspecialchars($office['Masters_fee']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> رسوم الدكتوراه</label>
                            <input type="number" step="0.01" name="phd_fee" value="<?php echo htmlspecialchars($office['Phd_fee']); ?>">
                        </div>

                        <div class="field"></div>
                    </div>

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

                    <div class="row">
                        <div class="field">
                            <label>تأكيد كلمة المرور الجديدة</label>
                            <div class="passBox">
                                <input type="password" id="confirmPass" name="confirmPassword" autocomplete="new-password">
                                <span class="eye" onclick="showPass('confirmPass')">👁</span>
                            </div>
                            <div class="note">اترك حقول كلمة المرور فارغة إذا كنت لا تريد تغييرها.</div>
                        </div>

                        <div class="field"></div>
                    </div>

                    <div class="center">
                        <button type="submit" class="btn" name="save">حفظ التعديلات</button>

                        <button type="button" class="btn"
                        onclick="window.location.href='Off02_Profile.php'"
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
function showPass(id){// إظهار أو إخفاء كلمة المرور
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