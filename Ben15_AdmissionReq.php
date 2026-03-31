<?php
session_start();
if (empty($_SESSION['bnf_id'])) exit();
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

$office_id = (int) $_GET['id'];
$bnf_id = (int) $_SESSION['bnf_id'];

/* جلب بيانات المستفيد */
$sqlBnf = "SELECT bnf_id, f_name, l_name, phone_num, email, sch_field, degree_level 
           FROM beneficiary 
           WHERE bnf_id = $bnf_id";
$resBnf = $conn->query($sqlBnf);
$bnf = $resBnf->fetch_assoc();

/* جلب بيانات المكتب */
$sqlOffice = "SELECT office_id, office_name, Bachelor_fee, Masters_fee, Phd_fee
              FROM consulting_office
              WHERE office_id = $office_id";
$resOffice = $conn->query($sqlOffice);
$office = $resOffice->fetch_assoc();

/* تحديد البرنامج حسب مؤهل المستفيد */
$degree = $bnf['degree_level'];
$program = "";

if ($degree == "ثانوي") {
    $program = "bachelor";
} elseif ($degree == "بكالوريوس") {
    $program = "masters";
} elseif ($degree == "ماجستير") {
    $program = "phd";
}

/* تحديد الرسوم حسب البرنامج */
$defaultFee = 0;
if ($program == "bachelor") {
    $defaultFee = $office['Bachelor_fee'];
} elseif ($program == "masters") {
    $defaultFee = $office['Masters_fee'];
} elseif ($program == "phd") {
    $defaultFee = $office['Phd_fee'];
}

/* مصفوفة للملفات المشتركة المطلوبة لجميع البرامج بدون استثناء */
$commonDocs = [
    "cv_file" => "CV", 
    "passport_file" => "Passport", 
    "language_file" => "Language Certificate", 
    "recommendation_file" => "Recommendation Letters", 
    "other_file" => "Other Certificates" 
];

/*متغير لمصفوفات الملفات الخاصة بكل برنامج */
$programDocs = [

    "bachelor" => [ // متطلبات البكالوريوس
        "highschool_file" => "High School Certificate", 
        "intent_file" => "Letter of Intent" 
    ],

    "masters" => [ // متطلبات الماجستير
        "degree_file" => "University Degree Certificate", 
        "transcript_file" => "Academic Transcript", 
        "sop_file" => "Statement of Purpose" 
    ],

    "phd" => [ // متطلبات الدكتوراه
        "academic_file" => "Academic Certificates", 
        "research_file" => "Research Proposal", 
        "sop_file" => "Statement of Purpose" 
    ]
];
/* متغير ل قائمة المستندات المطلوبة حسب البرنامج */
$requiredDocs = [];

/* دمج مصفوفات الملفات المشتركة والملفات الخاصة بالبرنامج */
if ($program != "" && isset($programDocs[$program])) {
    $requiredDocs = array_merge($commonDocs, $programDocs[$program]);
}

/* دالة لرفع ملفات طلب إصدار القبول وحفظ بياناتها في قاعدة البيانات */
function uploadAdmissionFile($inputName, $requestId, $docType, $uploadDir, $conn)
{
    /* التحقق من وجود ملف مرفوع في حقل الإدخال */
    if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {
        return false;
    }

    /*---------------------------------------------------------------------------------------------------------------------*/ 
$tmpName = $_FILES[$inputName]['tmp_name']; // مكان الملف المؤقت
$oldName = $_FILES[$inputName]['name'];     // اسم الملف

/* تحقق أن الملف PDF */
if (!str_ends_with(strtolower($oldName), ".pdf")) {
    return false;
}
/* إنشاء اسم واضح للملف مرتبط برقم الطلب ونوع المستند */
$newName = $requestId . "_" . $inputName . ".pdf";

/* تحديد مسار حفظ الملف */
$filePath = $uploadDir . $newName;

/* نقل الملف إلى مجلد الحفظ */
if (move_uploaded_file($tmpName, $filePath)) {

    /* حفظ بيانات الملف في قاعدة البيانات */
    $sqlDoc = "INSERT INTO admission_request_documents (request_id, doc_type, file_name, file)
               VALUES ('$requestId', '$docType', '$oldName', '$filePath')";
    $conn->query($sqlDoc);

    return true;
}
    /* في حال فشل رفع الملف */
    return false;
}

/* معالجة إرسال نموذج طلب إصدار القبول */
if (isset($_POST['submit_request'])) {

}
/* معالجة الإرسال */
if (isset($_POST['submit_request'])) {

    $program = isset($_POST['program']) ? trim($_POST['program']) : "";
    $univ_name = isset($_POST['univ_name']) ? trim($_POST['univ_name']) : "";
    $major_name = isset($_POST['major_name']) ? trim($_POST['major_name']) : "";
    $payment_done = isset($_POST['payment_done']) ? trim($_POST['payment_done']) : "0";

    if ($program == "") {
        $msg = "لا يوجد برنامج متاح لك بناءً على مؤهلك الدراسي.";
        $type = "error";
    } elseif ($univ_name == "" || $major_name == "") {
        $msg = "جميع الحقول مطلوبة.";
        $type = "error";
    } elseif (!isset($docsMap[$program])) {
        $msg = "البرنامج المختار غير صحيح.";
        $type = "error";
    } elseif ($payment_done != "1") {
        $msg = "لا يمكن تقديم الطلب قبل إتمام الدفع.";
        $type = "error";
    } else {

        $requiredDocs = $docsMap[$program];
        $missingFile = false;
        $wrongType = false;

        foreach ($requiredDocs as $inputName => $docLabel) {
            if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {
                $missingFile = true;
                break;
            }

            $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
            if ($ext != "pdf") {
                $wrongType = true;
                break;
            }
        }

        if ($missingFile) {
            $msg = "يجب رفع جميع الملفات المطلوبة.";
            $type = "error";
        } elseif ($wrongType) {
            $msg = "جميع الملفات يجب أن تكون PDF فقط.";
            $type = "error";
        } else {

            $safeUniv = $conn->real_escape_string($univ_name);
            $safeMajor = $conn->real_escape_string($major_name);
            $today = date("Y-m-d");

            $sqlReq = "INSERT INTO admission_request 
                       (bnf_id, office_id, major_name, univ_name, Submit_date, result_notes, Result_status)
                       VALUES
                       ('$bnf_id', '$office_id', '$safeMajor', '$safeUniv', '$today', NULL, NULL)";

            if ($conn->query($sqlReq)) {

                $requestId = $conn->insert_id;
                $uploadDir = "uploads/admission_requests/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($requiredDocs as $inputName => $docType) {
                    uploadAdmissionFile($inputName, $requestId, $docType, $uploadDir, $conn);
                }

                echo "<script>
                        alert('تم تقديم طلبك بنجاح');
                        window.location.href='Ben16_AdmissionRequests.php';
                      </script>";
                exit();

            } else {
                $msg = "حدث خطأ أثناء حفظ الطلب.";
                $type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تقديم طلب إصدار قبول</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=2">

<style>
.title-center{ text-align:center; margin-bottom:30px; }
.center-text{ text-align:center; font-weight:bold; margin-bottom:25px; }
.section-space{ margin-top:40px; }
.form-box{ max-width:1100px; margin:auto; }
.info-section{ background:#F8F8F8; padding:30px; border-radius:12px; gap:50px; }
.info-data{ flex:1; }
.form-inputs{ flex:1; }
.info-item{ display:flex; gap:10px; margin-bottom:12px; }
.info-label{ min-width:120px; color:#70A0AF; }
.info-value{ color:#3E2454; }
.docs-row{ gap:15px; }
.doc{ text-align:center; }
.uploadBox{ border:1.5px solid #8fb4bf; background:#fff; padding:12px; border-radius:6px; cursor:pointer; color:#70A0AF; display:block; }
.big-btn{ padding:12px 80px; font-size:17px; font-weight:700; }
.scholarship-details-box{ width:100%; max-width:1000px; margin:20px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }
.personal-info-grid{ display:grid; grid-template-columns:1fr 1fr; gap:40px; background:#F9F9F9; padding:25px; border-radius:10px; margin-bottom:30px; }
.static-data{ order:1; }
.static-data p{ margin:12px 0; font-size:16px; color:#3E2454; display:flex; font-family:'Noto Kufi Arabic', sans-serif !important; }
.static-data b{ color:#70A0AF; display:inline-block; width:140px; font-family:'Noto Kufi Arabic', sans-serif !important; }
.form-fields{ order:2; display:flex; flex-direction:column; gap:20px; }
.docs-section{ display:grid; grid-template-columns:repeat(4, 1fr); gap:15px; margin-top:20px; }
.doc-item{ text-align:right; }
.doc-item .title-label{ font-size:14px; font-weight:600; margin-bottom:10px; display:block; color:#333; font-family:'Noto Kufi Arabic', sans-serif !important; min-height:46px; line-height:1.8; }
.upload-wrapper{ border:2px solid; border-image-source:linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%); border-image-slice:1; background-color:#F8F8F8; height:60px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:0.3s; }
.upload-wrapper:hover{ background-color:#ececec; }
.upload-img{ width:30px; height:auto; }
.center-btn{ text-align:center; margin-top:40px; }
.star{ color:red !important; font-weight:bold; margin-left:3px; }
.form-submit-btn{ font-family:'Noto Kufi Arabic', sans-serif !important; font-size:18px; font-weight:700; background-color:#70A0AF; color:#ffffff; cursor:pointer; border:none; border-radius:4px; transition:0.3s; }
.form-submit-btn:hover{ opacity:0.9; transform:translateY(-2px); }
.page{ padding:18px 30px 30px; }
.back-wrap{ display:flex; justify-content:flex-end; max-width:1000px; margin:0 auto 10px; }
.back-btn{ width:50px; height:50px; display:flex; align-items:center; justify-content:center; }
.back-icon{ width:46px; height:46px; object-fit:contain; }
.field{ margin-bottom:5px; }
.field label{ display:block; margin-bottom:8px; color:#333; font-size:15px; font-weight:600; font-family:'Noto Kufi Arabic', sans-serif !important; }
.field input{ width:100%; height:45px; border:1.5px solid #8FB4C9; background:#fff; padding:10px 12px; font-size:14px; outline:none; font-family:'Noto Kufi Arabic', sans-serif !important; border-radius:4px; }
.field input::placeholder{ color:#cfcfcf; }
.form-subtitle{ text-align:center; margin-bottom:25px; color:#3E2454; font-weight:bold; font-size:21px; font-family:'Noto Kufi Arabic', sans-serif !important; }
.file-name-display{ font-size:11px; color:#70A0AF; min-height:18px; margin-top:6px; text-align:center; word-break:break-word; font-family:'Noto Kufi Arabic', sans-serif !important; }
.form-section{ display:none; }
.form-section.active{ display:block; }
.pay-fee-box{ max-width:1000px; margin:0 auto 15px; display:flex; justify-content:center; }
.fee-box{ background:#F6F3EE; border:1px solid #d8d1c8; border-radius:6px; padding:14px 24px; color:#3E2454; font-size:18px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif !important; }
.form-submit-btn.big-btn{ min-width:320px; }
.msg{ max-width:1000px; margin:0 auto 14px; padding:12px; border-radius:6px; text-align:center; font-size:14px; font-family:'Noto Kufi Arabic', sans-serif !important; }
.msg.error{ background:#fff1f1; color:#b42318; border:1px solid #efb4b4; }
.pay-modal{ display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:3000; justify-content:center; align-items:center; }
.pay-card{ width:700px; max-width:92%; background:#fff; border-radius:10px; padding:30px 24px; box-shadow:0 8px 24px rgba(0,0,0,0.18); }
.pay-title{ text-align:center; color:#d46a6a; font-size:22px; font-weight:700; margin-bottom:18px; font-family:'Noto Kufi Arabic', sans-serif !important; }
.pay-info{ background:#F6F3EE; border:1px solid #d8d1c8; border-radius:6px; padding:16px; text-align:center; font-size:17px; font-weight:700; color:#3E2454; margin-bottom:22px; line-height:2; font-family:'Noto Kufi Arabic', sans-serif !important; }
.pay-box{ width:78%; margin:0 auto; border:1px solid #e0e0e0; border-radius:10px; padding:18px; }
.pay-box-title{ text-align:center; font-size:20px; font-weight:700; color:#333; margin-bottom:16px; font-family:'Noto Kufi Arabic', sans-serif !important; }
.pay-field{ margin-bottom:14px; }
.pay-field input{ width:100%; height:46px; border:1px solid #d1d1d1; border-radius:6px; padding:8px 12px; font-size:14px; font-family:'Noto Kufi Arabic', sans-serif !important; outline:none; }
.pay-row{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.pay-confirm{ width:100%; height:46px; border:none; border-radius:6px; background:#70A0AF; color:#fff; font-size:18px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif !important; cursor:pointer; margin-top:10px; }
.close-btn{ display:block; margin:12px auto 0; background:none; border:none; color:#777; font-size:14px; font-family:'Noto Kufi Arabic', sans-serif !important; cursor:pointer; }
.no-program{ text-align:center; color:#b42318; font-size:18px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif !important; padding:25px 0; }
</style>
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="نورين">
            </div>

            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php" class="active">المكاتب الاستشارية</a></li>
                <li><a href="#">طلبات إصدار القبول</a></li>
                <li><a href="#">الاستشارات</a></li>
            </ul>
        </div>

        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content">

        <header class="header">
            <div class="page-heading">
                <div class="page-title">المكاتب الاستشارية</div>
                <div class="page-description">صفحة تقديم طلب إصدار قبول</div>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben03_EditProfile.php">تعديل الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page">

            <div class="back-wrap">
                <a href="Ben14_OfficeDetails.php?id=<?php echo $office_id; ?>" class="back-btn">
                    <img src="سهم تراجع.svg" class="back-icon" alt="رجوع">
                </a>
            </div>

            <?php if (!empty($msg)) { ?>
                <div class="msg <?php echo $type; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php } ?>

            <div class="scholarship-details-box">

                <?php if ($program != "") { ?>
                    <form method="POST" enctype="multipart/form-data" id="admissionForm">
                        <input type="hidden" name="program" id="programInput" value="<?php echo $program; ?>">
                        <input type="hidden" name="payment_done" id="payment_done" value="0">

                        <div class="form-section active" id="section-bachelor" <?php if ($program != "bachelor") echo 'style="display:none;"'; ?>>
                            <div class="form-subtitle">نموذج تقديم طلب إصدار القبول الجامعي – بكالوريوس</div>

                            <div class="personal-info-grid">
                                <div class="static-data">
                                    <p><b>الاسم:</b> <?php echo $bnf['f_name'] . " " . $bnf['l_name']; ?></p>
                                    <p><b>رقم الهاتف:</b> <?php echo $bnf['phone_num']; ?></p>
                                    <p><b>المؤهل الدراسي:</b> <?php echo $bnf['degree_level']; ?></p>
                                    <p><b>المجال الدراسي:</b> <?php echo $bnf['sch_field']; ?></p>
                                    <p><b>البريد الإلكتروني:</b> <?php echo $bnf['email']; ?></p>
                                </div>

                                <div class="form-fields">
                                    <div class="field">
                                        <label><b class="star">*</b> اسم الجامعة المرغوبة</label>
                                        <input type="text" name="univ_name" placeholder="ادخل اسم الجامعة" required>
                                    </div>

                                    <div class="field">
                                        <label><b class="star">*</b> التخصص الدراسي المرغوب</label>
                                        <input type="text" name="major_name" placeholder="ادخل التخصص الدراسي المرغوب" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-subtitle">رفع المستندات</div>

                            <div class="docs-section">
                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> السيرة الذاتية</label>
                                    <label for="b_cv_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="cv_file" id="b_cv_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> شهادة الثانوية العامة</label>
                                    <label for="b_highschool_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="highschool_file" id="b_highschool_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> خطاب النوايا</label>
                                    <label for="b_intent_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="intent_file" id="b_intent_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> جواز السفر</label>
                                    <label for="b_passport_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="passport_file" id="b_passport_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> شهادة اللغة</label>
                                    <label for="b_language_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="language_file" id="b_language_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> خطابات التوصية</label>
                                    <label for="b_recommendation_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="recommendation_file" id="b_recommendation_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> شهادات أخرى</label>
                                    <label for="b_other_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="other_file" id="b_other_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section active" id="section-masters" <?php if ($program != "masters") echo 'style="display:none;"'; ?>>
                            <div class="form-subtitle">نموذج تقديم طلب إصدار القبول الجامعي – ماجستير</div>

                            <div class="personal-info-grid">
                                <div class="static-data">
                                    <p><b>الاسم:</b> <?php echo $bnf['f_name'] . " " . $bnf['l_name']; ?></p>
                                    <p><b>رقم الهاتف:</b> <?php echo $bnf['phone_num']; ?></p>
                                    <p><b>المؤهل الدراسي:</b> <?php echo $bnf['degree_level']; ?></p>
                                    <p><b>المجال الدراسي:</b> <?php echo $bnf['sch_field']; ?></p>
                                    <p><b>البريد الإلكتروني:</b> <?php echo $bnf['email']; ?></p>
                                </div>

                                <div class="form-fields">
                                    <div class="field">
                                        <label><b class="star">*</b> اسم الجامعة المرغوبة</label>
                                        <input type="text" name="univ_name" placeholder="ادخل اسم الجامعة" required>
                                    </div>

                                    <div class="field">
                                        <label><b class="star">*</b> التخصص الدراسي المرغوب</label>
                                        <input type="text" name="major_name" placeholder="ادخل التخصص الدراسي المرغوب" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-subtitle">رفع المستندات</div>

                            <div class="docs-section">
                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> السيرة الذاتية</label>
                                    <label for="m_cv_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="cv_file" id="m_cv_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> الشهادة الجامعية</label>
                                    <label for="m_degree_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="degree_file" id="m_degree_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> السجل الأكاديمي</label>
                                    <label for="m_transcript_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="transcript_file" id="m_transcript_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> جواز السفر</label>
                                    <label for="m_passport_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="passport_file" id="m_passport_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> شهادة اللغة</label>
                                    <label for="m_language_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="language_file" id="m_language_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> بيان الغرض الدراسي</label>
                                    <label for="m_sop_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="sop_file" id="m_sop_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> خطابات التوصية</label>
                                    <label for="m_recommendation_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="recommendation_file" id="m_recommendation_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> شهادات أخرى</label>
                                    <label for="m_other_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="other_file" id="m_other_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section active" id="section-phd" <?php if ($program != "phd") echo 'style="display:none;"'; ?>>
                            <div class="form-subtitle">نموذج تقديم طلب إصدار القبول الجامعي – دكتوراه</div>

                            <div class="personal-info-grid">
                                <div class="static-data">
                                    <p><b>الاسم:</b> <?php echo $bnf['f_name'] . " " . $bnf['l_name']; ?></p>
                                    <p><b>رقم الهاتف:</b> <?php echo $bnf['phone_num']; ?></p>
                                    <p><b>المؤهل الدراسي:</b> <?php echo $bnf['degree_level']; ?></p>
                                    <p><b>المجال الدراسي:</b> <?php echo $bnf['sch_field']; ?></p>
                                    <p><b>البريد الإلكتروني:</b> <?php echo $bnf['email']; ?></p>
                                </div>

                                <div class="form-fields">
                                    <div class="field">
                                        <label><b class="star">*</b> اسم الجامعة المرغوبة</label>
                                        <input type="text" name="univ_name" placeholder="ادخل اسم الجامعة" required>
                                    </div>

                                    <div class="field">
                                        <label><b class="star">*</b> التخصص الدراسي المرغوب</label>
                                        <input type="text" name="major_name" placeholder="ادخل التخصص الدراسي المرغوب" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-subtitle">رفع المستندات</div>

                            <div class="docs-section">
                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> السيرة الذاتية</label>
                                    <label for="p_cv_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="cv_file" id="p_cv_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> الشهادات الأكاديمية</label>
                                    <label for="p_academic_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="academic_file" id="p_academic_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> المقترح البحثي</label>
                                    <label for="p_research_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="research_file" id="p_research_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> جواز السفر</label>
                                    <label for="p_passport_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="passport_file" id="p_passport_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> شهادة اللغة</label>
                                    <label for="p_language_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="language_file" id="p_language_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> بيان الغرض الدراسي</label>
                                    <label for="p_sop_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="sop_file" id="p_sop_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> خطابات التوصية</label>
                                    <label for="p_recommendation_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="recommendation_file" id="p_recommendation_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>

                                <div class="doc-item">
                                    <label class="title-label"><b class="star">*</b> شهادات أخرى</label>
                                    <label for="p_other_file" class="upload-wrapper">
                                        <img src="upload.png" class="upload-img" alt="رفع">
                                    </label>
                                    <input type="file" name="other_file" id="p_other_file" accept=".pdf" style="display:none;" onchange="showFileName(this)">
                                    <div class="file-name-display"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pay-fee-box">
                            <div class="fee-box" id="feeBox">
                                المبلغ المطلوب: <?php echo $defaultFee; ?> ريال
                            </div>
                        </div>

                        <div class="center-btn">
                            <button type="button" id="openPaymentBtn" class="form-submit-btn big-btn">حفظ وتقديم الطلب</button>
                        </div>

                        <button type="submit" name="submit_request" id="realSubmitBtn" style="display:none;"></button>
                    </form>
                <?php } else { ?>
                    <div class="no-program">لا يوجد برنامج متاح لك بناءً على مؤهلك الدراسي.</div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<div class="pay-modal" id="paymentModal">
    <div class="pay-card">
        <div class="pay-title">لاعتماد الطلب ومتابعة الإجراءات يرجى إتمام عملية الدفع</div>

        <div class="pay-info" id="modalPaymentInfo">
            اسم المستفيد <?php echo $bnf['f_name'] . " " . $bnf['l_name']; ?> &nbsp;&nbsp; المبلغ المطلوب <?php echo $defaultFee; ?> ريال
        </div>

        <div class="pay-box">
            <div class="pay-box-title">بيانات تأكيد الدفع</div>

            <div class="pay-field">
                <input type="text" id="cardName" placeholder="NAME ON CARD">
            </div>

            <div class="pay-field">
                <input type="text" id="cardNumber" placeholder="CARD NUMBER">
            </div>

            <div class="pay-row">
                <div class="pay-field">
                    <input type="text" id="expDate" placeholder="MM/YY">
                </div>
                <div class="pay-field">
                    <input type="text" id="cvv" placeholder="CVV">
                </div>
            </div>

            <button type="button" class="pay-confirm" id="confirmPaymentBtn">اضغط لتأكيد الدفع</button>
            <button type="button" class="close-btn" id="closePaymentBtn">إغلاق</button>
        </div>
    </div>
</div>

<script>
const fees = {
    bachelor: "<?php echo $office['Bachelor_fee']; ?>",
    masters: "<?php echo $office['Masters_fee']; ?>",
    phd: "<?php echo $office['Phd_fee']; ?>"
};

const studentName = "<?php echo $bnf['f_name'] . ' ' . $bnf['l_name']; ?>";
const currentProgram = "<?php echo $program; ?>";

const programInput = document.getElementById("programInput");
const feeBox = document.getElementById("feeBox");
const modalPaymentInfo = document.getElementById("modalPaymentInfo");

const openPaymentBtn = document.getElementById("openPaymentBtn");
const paymentModal = document.getElementById("paymentModal");
const closePaymentBtn = document.getElementById("closePaymentBtn");
const confirmPaymentBtn = document.getElementById("confirmPaymentBtn");
const paymentDone = document.getElementById("payment_done");
const realSubmitBtn = document.getElementById("realSubmitBtn");

function updateFee(program) {
    if (!program || !fees[program]) {
        return;
    }

    feeBox.innerHTML = "المبلغ المطلوب: " + fees[program] + " ريال";
    modalPaymentInfo.innerHTML = "اسم المستفيد " + studentName + " &nbsp;&nbsp; المبلغ المطلوب " + fees[program] + " ريال";
}

function showFileName(input) {
    const fileBox = input.parentElement.nextElementSibling;
    if (input.files && input.files[0] && fileBox) {
        fileBox.innerHTML = input.files[0].name;
    }
}

function validateVisibleSection() {
    const activeProgram = programInput.value;
    const activeSection = document.getElementById("section-" + activeProgram);

    if (!activeSection) {
        alert("لا يوجد نموذج متاح.");
        return false;
    }

    const univField = activeSection.querySelector('input[name="univ_name"]');
    const majorField = activeSection.querySelector('input[name="major_name"]');

    if (!univField || !majorField || univField.value.trim() === "" || majorField.value.trim() === "") {
        alert("يرجى تعبئة اسم الجامعة والتخصص الدراسي.");
        return false;
    }

    const fileInputs = activeSection.querySelectorAll('input[type="file"]');

    for (let i = 0; i < fileInputs.length; i++) {
        if (fileInputs[i].files.length === 0) {
            alert("يرجى رفع جميع الملفات المطلوبة.");
            return false;
        }

        const fileName = fileInputs[i].files[0].name.toLowerCase();
        if (!fileName.endsWith(".pdf")) {
            alert("جميع الملفات يجب أن تكون PDF فقط.");
            return false;
        }
    }

    return true;
}

if (openPaymentBtn) {
    openPaymentBtn.addEventListener("click", function() {
        if (validateVisibleSection()) {
            paymentModal.style.display = "flex";
        }
    });
}

if (closePaymentBtn) {
    closePaymentBtn.addEventListener("click", function() {
        paymentModal.style.display = "none";
    });
}

if (confirmPaymentBtn) {
    confirmPaymentBtn.addEventListener("click", function() {
        const cardName = document.getElementById("cardName").value.trim();
        const cardNumber = document.getElementById("cardNumber").value.trim();
        const expDate = document.getElementById("expDate").value.trim();
        const cvv = document.getElementById("cvv").value.trim();

        if (cardName === "" || cardNumber === "" || expDate === "" || cvv === "") {
            alert("يرجى تعبئة جميع بيانات الدفع.");
            return;
        }

        paymentDone.value = "1";
        paymentModal.style.display = "none";
        realSubmitBtn.click();
    });
}

window.addEventListener("click", function(e) {
    if (e.target === paymentModal) {
        paymentModal.style.display = "none";
    }
});

updateFee(currentProgram);
</script>

</body>
</html>