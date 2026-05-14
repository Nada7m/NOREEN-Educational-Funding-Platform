<?php
session_start();

/* التحقق من تسجيل دخول المستفيد */
if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

/* رقم المستفيد الحالي */
$bnf_id = $_SESSION['bnf_id'];

$msg = "";
$type = "";
/* عند حفظ التعديلات */
if (isset($_POST["save"])) {

    $first = trim($_POST["firstName"]);
    $last = trim($_POST["lastName"]);
    $degree = trim($_POST["degree"]);
    $major = trim($_POST["major"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);

    $currentPass = trim($_POST["currentPassword"]);
    $newPass = trim($_POST["newPassword"]);
    $confirmPass = trim($_POST["confirmPassword"]);

    /* التحقق من البريد إذا كان مستخدمًا من مستفيد آخر */
    $checkEmail = $conn->prepare("SELECT bnf_id FROM beneficiary WHERE email = ? AND bnf_id != ?");
    $checkEmail->bind_param("si", $email, $bnf_id);
    $checkEmail->execute();
    $emailResult = $checkEmail->get_result();

    if ($emailResult->num_rows > 0) {
        $msg = "البريد الإلكتروني مستخدم مسبقًا.";
        $type = "error";
    } elseif ($degree == "اختر المؤهل الدراسي من القائمة أدناه") {
        $msg = "يرجى اختيار المؤهل الدراسي.";
        $type = "error";
    } elseif ($major == "اختر المجال الدراسي من القائمة أدناه") {
        $msg = "يرجى اختيار المجال الدراسي.";
        $type = "error";
    } elseif ($first == "" || $last == "" || $phone == "" || $email == "") {
        $msg = "يرجى تعبئة جميع الحقول الأساسية.";
        $type = "error";
    } elseif (strlen($phone) != 10 || !ctype_digit($phone)) {
        $msg = "يرجى إدخال رقم هاتف مكوّن من 10 أرقام.";
        $type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "يرجى إدخال بريد إلكتروني صحيح.";
        $type = "error";
    } else {

        /* تحديث البيانات الأساسية */
        $update = $conn->prepare("
            UPDATE beneficiary
            SET f_name = ?, l_name = ?, degree_level = ?, sch_field = ?, phone_num = ?, email = ?
            WHERE bnf_id = ?
        ");
        $update->bind_param("ssssssi", $first, $last, $degree, $major, $phone, $email, $bnf_id);
        $update->execute();
        $update->close();

        /* إذا أراد تغيير كلمة المرور */
        if ($currentPass != "" || $newPass != "" || $confirmPass != "") {

            $getPass = $conn->prepare("SELECT password FROM beneficiary WHERE bnf_id = ?");
            $getPass->bind_param("i", $bnf_id);
            $getPass->execute();
            $passResult = $getPass->get_result();
            $user = $passResult->fetch_assoc();
            $storedPass = $user["password"];
            $getPass->close();

            if ($currentPass == "" || $newPass == "" || $confirmPass == "") {
                $msg = "لتغيير كلمة المرور يجب تعبئة جميع حقول كلمة المرور.";
                $type = "error";
            } elseif (!password_verify($currentPass, $storedPass)) {
                $msg = "كلمة المرور الحالية غير صحيحة.";
                $type = "error";
            } elseif ($newPass != $confirmPass) {
                $msg = "كلمتا المرور الجديدتان غير متطابقتين.";
                $type = "error";
            } else {
                $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);

                $updatePass = $conn->prepare("UPDATE beneficiary SET password = ? WHERE bnf_id = ?");
                $updatePass->bind_param("si", $hashedPass, $bnf_id);
                $updatePass->execute();
                $updatePass->close();

                $msg = "تم تحديث البيانات وكلمة المرور بنجاح.";
                $type = "success";
            }

        } else {
            $msg = "تم تحديث البيانات بنجاح.";
            $type = "success";
        }
    }

    $checkEmail->close();
}

/* جلب بيانات المستفيد الحالية */
$stmt = $conn->prepare("
    SELECT f_name, l_name, degree_level, sch_field, phone_num, email
    FROM beneficiary
    WHERE bnf_id = ?
");
$stmt->bind_param("i", $bnf_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $beneficiary = $result->fetch_assoc();
} else {
    die("لم يتم العثور على بيانات المستفيد");
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تعديل البيانات</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4"><link rel="stylesheet" href="Style.css">

<style>
.page{padding:40px;}
.box{width:760px;}
.note{font-size:12px;color:#666; margin-top:5px;}
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
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                 <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
          <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="#">المكاتب الاستشارية</a></li>
                 <li><a href="#Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
                <li><a href="Ben19_Consultations.php">الاستشارات</a></li>
            </ul>
        </div>
   <div class="sidebar-bottom">
  <form action="logout.php" method="post">
    <button type="submit" class="logout-btn">
      <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
      <b>تسجيل الخروج</b>
    </button>
  </form>
</div>    </aside>
    <div class="main-content">
        <header class="header">
            <div class="page-title">تعديل البيانات</div>
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="القائمة">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                                      <a href="Ben03_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="#">التواصل والدعم</a>
                    </div>
                </div>
            </div>
        </header>
        <div class="page">
            <div class="box">
                <h2>تعديل <span>بيانات المستفيد</span></h2>
                <?php if($msg != ""){ ?>
                <div class="message <?php echo $type; ?>">
                    <?php echo $msg; ?>
                </div>
                <?php } ?>
                <form method="post">
                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> الاسم الأول</label>
                            <input type="text" name="firstName" value="<?php echo  ($beneficiary['f_name']); ?>">
                        </div>
                        <div class="field">
                            <label><span class="star">*</span> الاسم الأخير</label>
                            <input type="text" name="lastName" value="<?php echo  ($beneficiary['l_name']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="field">
                            <label><span class="star">*</span> المؤهل الدراسي</label>
                            <select name="degree">
                                <option <?php if($beneficiary['degree_level']=="اختر المؤهل الدراسي من القائمة أدناه") echo "selected"; ?>>اختر المؤهل الدراسي من القائمة أدناه</option>
                                <option <?php if($beneficiary['degree_level']=="ثانوي") echo "selected"; ?>>ثانوي</option>
                                <option <?php if($beneficiary['degree_level']=="بكالوريوس") echo "selected"; ?>>بكالوريوس</option>
                                <option <?php if($beneficiary['degree_level']=="ماجستير") echo "selected"; ?>>ماجستير</option>
                                <option <?php if($beneficiary['degree_level']=="دكتوراه") echo "selected"; ?>>دكتوراه</option>
                            </select>
                        </div>

                        <div class="field">
                            <label><span class="star">*</span> المجال الدراسي</label>
                        <select name="major">
    <option <?php if($beneficiary['sch_field']=="اختر المجال الدراسي من القائمة أدناه") echo "selected"; ?>>اختر المجال الدراسي من القائمة أدناه</option>
    <option <?php if($beneficiary['sch_field']=="تقني وحاسوبي") echo "selected"; ?>>تقني وحاسوبي</option>
    <option <?php if($beneficiary['sch_field']=="علوم طبيعية") echo "selected"; ?>>علوم طبيعية</option>
    <option <?php if($beneficiary['sch_field']=="صناعي وتشغيلي") echo "selected"; ?>>صناعي وتشغيلي</option>
    <option <?php if($beneficiary['sch_field']=="اداري") echo "selected"; ?>>اداري</option>
    <option <?php if($beneficiary['sch_field']=="قانوني") echo "selected"; ?>>قانوني</option>
    <option <?php if($beneficiary['sch_field']=="اجتماعي وانساني") echo "selected"; ?>>اجتماعي وانساني</option>
    <option <?php if($beneficiary['sch_field']=="تصميمي") echo "selected"; ?>>تصميمي</option>
    <option <?php if($beneficiary['sch_field']=="اقتصادي") echo "selected"; ?>>اقتصادي</option>
    <option <?php if($beneficiary['sch_field']=="إعلامي") echo "selected"; ?>>إعلامي</option>
    <option <?php if($beneficiary['sch_field']=="بيئي") echo "selected"; ?>>بيئي</option>
    <option <?php if($beneficiary['sch_field']=="لوجيستي") echo "selected"; ?>>لوجيستي</option>
    <option <?php if($beneficiary['sch_field']=="صحي") echo "selected"; ?>>صحي</option>
    <option <?php if($beneficiary['sch_field']=="لا يوجد") echo "selected"; ?>>لا يوجد</option>
</select>
                        </div>

                    </div>

                    <div class="row">

                        <div class="field">
                            <label><span class="star">*</span> رقم الهاتف</label>
                            <input type="text" name="phone" value="<?php echo  ($beneficiary['phone_num']); ?>">
                        </div>

                        <div class="field">
                            <label><span class="star">*</span> البريد الإلكتروني</label>
                            <input type="email" name="email" value="<?php echo  ($beneficiary['email']); ?>">
                        </div>

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
        <div class="note">يمكنك ترك حقول كلمة المرور فارغة عند عدم الرغبة في تعديلها .</div>
    </div>

    <div class="field"></div>

</div>

          <div class="center">
    <button type="submit" class="btn" name="save">حفظ التعديلات</button>

    <button type="button" class="btn" 
    onclick="window.location.href='Ben02_Profile.php'" 
    style="background:#888;margin-right:10px;">
    إلغاء
    </button>
</div>
                    </div>

                </form>

            </div>
        </div>

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
</script>
</body>
</html>