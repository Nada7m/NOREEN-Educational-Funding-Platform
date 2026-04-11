<?php
session_start();

$con = new mysqli("localhost", "root", "", "noreen");
if ($con->connect_error) {
    die("فشل الاتصال بالقاعدة: " . $con->connect_error);
}
$con->set_charset("utf8mb4");

if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

/* رقم المستفيد الحالي */
$bnf_id = (int) $_SESSION['bnf_id'];

/* رقم المنحة من الرابط */
$sch_id = isset($_GET['sch_id']) ? (int) $_GET['sch_id'] : 0;

if ($sch_id <= 0) {
    die("رقم المنحة غير صحيح");
}

/* جلب بيانات المستفيد */
$sql_user = "SELECT * FROM beneficiary WHERE bnf_id = $bnf_id";
$res_user = $con->query($sql_user);
$userData = $res_user ? $res_user->fetch_assoc() : null;

if (!$userData) {
    die("لم يتم العثور على بيانات المستفيد");
}

/* دالة رفع المستندات */
function uploadDoc($con, $req_id, $input, $type, $dir)
{
    if (!isset($_FILES[$input]) || empty($_FILES[$input]['name'])) {
        return false;
    }

    $oldName = $_FILES[$input]['name'];
    $tmpName = $_FILES[$input]['tmp_name'];

    if (!str_ends_with(strtolower($oldName), ".pdf")) {
        return false;
    }

    $newName = $req_id . "_" . $input . ".pdf";
    $fullPath = $dir . $newName;

    if (move_uploaded_file($tmpName, $fullPath)) {
        $safeOldName = $con->real_escape_string($oldName);
        $safePath = $con->real_escape_string($fullPath);
        $safeType = $con->real_escape_string($type);

        $sql_d = "INSERT INTO scholarship_request_documents (request_id, doc_type, file_name, file)
                  VALUES ('$req_id', '$safeType', '$safeOldName', '$safePath')";
        $con->query($sql_d);

        return true;
    }

    return false;
}

/* معالجة الإرسال */
if (isset($_POST['submit_request'])) {

    $univ = isset($_POST['university']) ? trim($_POST['university']) : "";
    $major = isset($_POST['major']) ? trim($_POST['major']) : "";
    $today = date("Y-m-d");
    $msg = "";

    if ($univ == "" || $major == "") {
        $msg = "يرجى تعبئة جميع الحقول المطلوبة.";
    } else {

        $requiredFiles = ['cv_file', 'cert_file', 'rec_file', 'accept_file'];
        $missingFile = false;
        $wrongType = false;

        foreach ($requiredFiles as $fileInput) {
            if (!isset($_FILES[$fileInput]) || empty($_FILES[$fileInput]['name'])) {
                $missingFile = true;
                break;
            }

            $fileName = strtolower($_FILES[$fileInput]['name']);
            if (!str_ends_with($fileName, ".pdf")) {
                $wrongType = true;
                break;
            }
        }

        if ($missingFile) {
            $msg = "يرجى رفع جميع المستندات المطلوبة.";
        } elseif ($wrongType) {
            $msg = "جميع الملفات يجب أن تكون PDF فقط.";
        } else {

            $safeUniv = $con->real_escape_string($univ);
            $safeMajor = $con->real_escape_string($major);

            $sql_req = "INSERT INTO scholarship_requests
                        (scholarship_id, bnf_id, Submit_date, request_status, major_name, univ_name)
                        VALUES
                        ('$sch_id', '$bnf_id', '$today', 'قيد المراجعة', '$safeMajor', '$safeUniv')";

            if ($con->query($sql_req)) {

                $req_id = $con->insert_id;
                $upload_dir = "uploads/scholarship_requests/";

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                uploadDoc($con, $req_id, 'cv_file', 'CV', $upload_dir);
                uploadDoc($con, $req_id, 'cert_file', 'Certificate', $upload_dir);
                uploadDoc($con, $req_id, 'rec_file', 'Recommendation', $upload_dir);
                uploadDoc($con, $req_id, 'accept_file', 'Acceptance', $upload_dir);

                header("Location: Ben09_TrackScholarship.php?request_id=" . $req_id . "&success=1");
                exit();

            } else {
                $msg = "حدث خطأ أثناء حفظ الطلب.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التقديم على المنح</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css?v=4">
    <link rel="stylesheet" href="Style.css">
    <style>
.page { background-color: #f8f8f8; }

.title-center { text-align: center; margin-bottom: 30px; }

.center-text { text-align: center; font-weight: bold; margin-bottom: 25px; }

.section-space { margin-top: 40px; }

.form-box { max-width: 1100px; margin: auto; }

.big-btn { padding: 12px 80px; font-size: 17px; font-weight: 700; }

.scholarship-details-box { width: 100%; max-width: 1000px; margin: 0px auto; padding: 30px; border: 0.5px solid #d4d4d4; border-radius: 12px; box-shadow: none; background: #fff; }

.back-wrap { display: flex; justify-content: flex-end; margin-bottom: -110px; padding-left: 60px; }

.personal-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: #F9F9F9; padding: 25px; border-radius: 10px; margin-bottom: 30px; }

.static-data { order: 1; }

.static-data p { margin: 12px 0; font-size: 16px; color: #3E2454; display: flex; font-family: 'Noto Kufi Arabic', sans-serif !important; }

.static-data b { color: #70A0AF; display: inline-block; width: 140px; font-family: 'Noto Kufi Arabic', sans-serif !important; }

.form-fields { order: 2; display: flex; flex-direction: column; gap: 20px; }

.field label { display: block; margin-bottom: 6px; color: #333; font-size: 14px; font-weight: 600; font-family: 'Noto Kufi Arabic', sans-serif !important; }

.field input { width: 100%; height: 40px; border: 1.5px solid #8FB4C9; background: #fff; padding: 8px 10px; font-size: 13px; color: #000; outline: none; font-family: 'Noto Kufi Arabic', sans-serif !important; border-radius: 4px; }

.docs-section { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 20px; }

.doc-item { text-align: right; }

.doc-item label.title-label { font-size: 14px; font-weight: 600; margin-bottom: 10px; display: block; color: #333; font-family: 'Noto Kufi Arabic', sans-serif !important; }

.upload-wrapper { border: 1.5px solid #8FB4C9; background-color: #F8F8F8; height: 30px; width: 160px; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; border: 2px solid; border-image-source: linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%); border-image-slice: 1; }

.upload-wrapper:hover { border-color: #3E2454; background-color: #ececec; }

.upload-img { width: 20px; height: auto; }

.center-btn { text-align: center; margin-top: 40px; }

.star { color: red !important; font-weight: bold; margin-left: 3px; }

.form-submit-btn { font-family: 'Noto Kufi Arabic', sans-serif !important; font-size: 16px; font-weight: 700; background-color: #70A0AF; color: #ffffff; cursor: pointer; border: none; border-radius: 4px; transition: 0.3s; padding: 8px 40px; }

.form-submit-btn:hover { opacity: 0.9; transform: translateY(-2px); }

.msg { max-width: 1000px; margin: 0 auto 15px; padding: 12px; border-radius: 6px; text-align: center; font-size: 14px; font-family: 'Noto Kufi Arabic', sans-serif !important; }

.msg.error { background: #fff1f1; color: #b42318; border: 1px solid #efb4b4; }

.file-name-display { font-size: 11px; color: #70A0AF; margin-top: 8px; min-height: 18px; }
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php" class="active">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
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
        </div>
    </aside>

    <div class="main-content">
        <header class="header">
            <div class="page-heading">
                <div class="page-title">التقديم على المنح</div>
                <div class="page-description">صفحة نموذج التقديم على المنح المعروضة</div>
            </div>
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page">
            <div class="back-wrap">
                <a href="Ben05_ScholarshipDetails.php" class="back-btn">
                    <img src="سهم تراجع.svg" class="back-icon" alt="رجوع" style="width: 45px; height: 45px;">
                </a>
            </div>

            <?php if (!empty($msg)) { ?>
                <div class="msg error"><?php echo $msg; ?></div>
            <?php } ?>

            <div class="container">
                <div class="scholarship-details-box">
                    <h2 class="main-title" style="text-align: center; color: #3E2454;">نموذج تقديم طلب منحة</h2>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-subtitle" style="text-align: center; margin-bottom: 25px; color: #3E2454; font-weight: bold;">المعلومات الشخصية</div>

                        <div class="personal-info-grid">
                            <div class="static-data">
                                <p><b>الاسم:</b> <?php echo $userData['f_name'] . " " . $userData['l_name']; ?></p>
                                <p><b>رقم الهاتف:</b> <span dir="ltr"><?php echo $userData['phone_num']; ?></span></p>
                                <p><b>المجال الدراسي:</b> <?php echo $userData['sch_field']; ?></p>
                                <p><b>المؤهل الدراسي:</b> <?php echo $userData['degree_level']; ?></p>
                                <p><b>البريد الإلكتروني:</b> <?php echo $userData['email']; ?></p>
                            </div>

                            <div class="form-fields">
                                <div class="field">
                                    <label><span class="star">*</span> اسم الجامعة المرغوبة</label>
                                    <input type="text" name="university" placeholder="ادخل اسم الجامعة" required>
                                </div>

                                <div class="field">
                                    <label><span class="star">*</span> التخصص الدراسي المرغوب</label>
                                    <input type="text" name="major" placeholder="ادخل التخصص الدراسي" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-subtitle" style="text-align: center; margin-top: 40px; color: #3E2454; font-weight: bold;">المستندات المطلوبة</div>

                        <div class="docs-section">
                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> السيرة الذاتية</label>
                                <label for="cv" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                </label>
                                <input type="file" name="cv_file" id="cv" style="display:none;" required onchange="showName(this)">
                                <div class="file-name-display"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> شهادة آخر مؤهل</label>
                                <label for="cert" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                </label>
                                <input type="file" name="cert_file" id="cert" style="display:none;" required onchange="showName(this)">
                                <div class="file-name-display"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> خطابات التوصية</label>
                                <label for="rec" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                </label>
                                <input type="file" name="rec_file" id="rec" style="display:none;" required onchange="showName(this)">
                                <div class="file-name-display"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> خطاب القبول الجامعي</label>
                                <label for="accept" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                </label>
                                <input type="file" name="accept_file" id="accept" style="display:none;" required onchange="showName(this)">
                                <div class="file-name-display"></div>
                            </div>
                        </div>

                        <div class="center-btn">
                            <button type="submit" name="submit_request" class="form-submit-btn">إرسال الطلب</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showName(input) {
    if (input.files && input.files[0]) {
        const docItem = input.closest(".doc-item");
        const fileBox = docItem.querySelector(".file-name-display");
        fileBox.innerHTML = input.files[0].name;
    }
}
</script>
</body>
</html>


