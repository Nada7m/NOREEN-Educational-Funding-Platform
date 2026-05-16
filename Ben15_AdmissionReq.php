<?php
/* بدء الجلسة */
session_start();

/** التحقق من تسجيل دخول المستفيد **/
if (empty($_SESSION['bnf_id'])) exit();

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

/* بيانات المكتب والمستفيد */
$office_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$bnf_id = (int) $_SESSION['bnf_id'];

/* جلب بيانات المكتب */
$sqlOffice = "SELECT office_id, office_name, Bachelor_fee, Masters_fee, Phd_fee
              FROM consulting_office
              WHERE office_id = $office_id";
$resOffice = $conn->query($sqlOffice);
$office = $resOffice ? $resOffice->fetch_assoc() : null;

/* متغيرات الصفحة */
$program = "";
$univ_name = "";
$major_name = "";
$msg = "";
$type = "";

/* عناوين البرامج */
$programTitles = [
    "bachelor" => "نموذج تقديم طلب إصدار القبول الجامعي – بكالوريوس",
    "masters" => "نموذج تقديم طلب إصدار القبول الجامعي – ماجستير",
    "phd" => "نموذج تقديم طلب إصدار القبول الجامعي – دكتوراه"];

/* المستندات المشتركة المطلوبة */
$commonDocs = [
    "cv_file" => ["type" => "CV", "label" => "السيرة الذاتية"],
    "passport_file" => ["type" => "Passport", "label" => "جواز السفر"],
    "language_file" => ["type" => "Language Certificate", "label" => "شهادة اللغة"],
    "recommendation_file" => ["type" => "Recommendation Letters", "label" => "خطابات التوصية"]];

/* المستند الاختياري */
$optionalDocs = [
    "other_file" => ["type" => "Other Certificates", "label" => "شهادات أخرى"]];

/* المستندات الخاصة بكل برنامج */
$programDocs = [
    "bachelor" => [
        "highschool_file" => ["type" => "High School Certificate", "label" => "شهادة الثانوية العامة"],
        "intent_file" => ["type" => "Letter of Intent", "label" => "خطاب النوايا"]],
    "masters" => [
        "degree_file" => ["type" => "University Degree Certificate", "label" => "الشهادة الجامعية"],
        "transcript_file" => ["type" => "Academic Transcript", "label" => "السجل الأكاديمي"],
        "sop_file" => ["type" => "Statement of Purpose", "label" => "بيان الغرض الدراسي"]],
    "phd" => [
        "academic_file" => ["type" => "Academic Certificates", "label" => "الشهادات الأكاديمية"],
        "research_file" => ["type" => "Research Proposal", "label" => "المقترح البحثي"],
        "sop_file" => ["type" => "Statement of Purpose", "label" => "بيان الغرض الدراسي"]]];

/* دالة رفع الملفات */
function uploadAdmissionFile($inputName, $requestId, $docType, $uploadDir, $conn)
{
    /** التحقق من وجود الملف **/
    if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {  return false;}

    $tmpName = $_FILES[$inputName]['tmp_name'];
    $oldName = $_FILES[$inputName]['name'];

    /** قبول ملفات PDF فقط **/
    if (!str_ends_with(strtolower($oldName), ".pdf")) {  return false;    }

    /* تجهيز اسم ومسار الملف */
    $newName = $requestId . "_" . $inputName . ".pdf";
    $filePath = $uploadDir . $newName;

    /* نقل الملف وحفظه */
    if (move_uploaded_file($tmpName, $filePath)) {
        $safeOldName = $conn->real_escape_string($oldName);
        $safeFilePath = $conn->real_escape_string($filePath);
        $safeDocType = $conn->real_escape_string($docType);

        /* حفظ بيانات المستند */
        $sqlDoc = "INSERT INTO admission_request_documents (request_id, doc_type, file_name, file)
                   VALUES ('$requestId', '$safeDocType', '$safeOldName', '$safeFilePath')";
        $conn->query($sqlDoc); return true;   }return false;}

/* معالجة إرسال النموذج */
if (isset($_POST['submit_request'])) {

    /* استقبال بيانات النموذج */
    $program = isset($_POST['program']) ? trim($_POST['program']) : "";
    $univ_name = isset($_POST['univ_name']) ? trim($_POST['univ_name']) : "";
    $major_name = isset($_POST['major_name']) ? trim($_POST['major_name']) : "";

    /** التحقق من نوع البرنامج **/
    if ($program == "") {
        $msg = "يرجى اختيار نوع البرنامج.";
        $type = "error"; } elseif (!isset($programDocs[$program])) {
        $msg = "البرنامج المختار غير صحيح.";
        $type = "error"; } elseif ($univ_name == "" || $major_name == "") {
        $msg = "جميع الحقول مطلوبة.";
        $type = "error"; } else {

        /* الملفات المطلوبة فقط */
        $requiredDocs = array_merge($commonDocs, $programDocs[$program]);
        $missingFile = false;
        $wrongType = false;

        /* التحقق من الملفات المطلوبة */
        foreach ($requiredDocs as $inputName => $docData) {
            if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {
                $missingFile = true;
                break;  }

            $fileName = strtolower($_FILES[$inputName]['name']);
            if (!str_ends_with($fileName, ".pdf")) {
                $wrongType = true;
                break;  }   }

        /* التحقق من الملف الاختياري إذا تم رفعه */
        if (isset($_FILES["other_file"]) && !empty($_FILES["other_file"]["name"])) {
            $otherFileName = strtolower($_FILES["other_file"]["name"]);
            if (!str_ends_with($otherFileName, ".pdf")) {
                $wrongType = true; }  }

        /** رسائل التحقق **/
        if ($missingFile) {
            $msg = "يجب تعبئة جميع الحقول ورفع جميع الملفات المطلوبة.";
            $type = "error"; } elseif ($wrongType) {
            $msg = "جميع الملفات يجب أن تكون PDF فقط.";
            $type = "error"; } else {

            /* تجهيز بيانات الطلب */
            $safeProgram = $conn->real_escape_string($program);
            $safeUniv = $conn->real_escape_string($univ_name);
            $safeMajor = $conn->real_escape_string($major_name);
            $today = date("Y-m-d");

            /* حفظ طلب إصدار القبول */
            $sqlReq = "INSERT INTO admission_request
                       (bnf_id, office_id, program_type, major_name, univ_name, Submit_date, result_notes, Result_status, request_status)
                       VALUES
                       ('$bnf_id', '$office_id', '$safeProgram', '$safeMajor', '$safeUniv', '$today', '', 'قيد المعالجة', 'في الانتظار')";

            if ($conn->query($sqlReq)) {
                $requestId = $conn->insert_id;
                $uploadDir = "uploads/admission_requests/";

                /* إنشاء مجلد الرفع */
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);  }

                /* رفع الملفات المطلوبة */
                foreach ($requiredDocs as $inputName => $docData) {
                    uploadAdmissionFile($inputName, $requestId, $docData["type"], $uploadDir, $conn);  }

                /* رفع شهادات أخرى إذا وُجدت */
                uploadAdmissionFile("other_file", $requestId, "Other Certificates", $uploadDir, $conn);

                /* الانتقال لقائمة الطلبات */
                header("Location: Ben16_AdmissionList.php?success=1");
                exit();

            } else {
                $msg = "حدث خطأ أثناء حفظ الطلب.";
                $type = "error";
            }
        }
    }
}

/* بيانات للجافاسكربت */
$jsPrograms = [];
foreach ($programDocs as $programKey => $docs) {
    $mergedDocs = array_merge($commonDocs, $docs, $optionalDocs);
    $docList = [];

    /* تجهيز أسماء المستندات */
    foreach ($mergedDocs as $inputName => $docData) {
        $docList[] = [
            "name" => $inputName,
            "label" => $docData["label"]
        ];
    }

    $jsPrograms[$programKey] = [
        "title" => $programTitles[$programKey],
        "docs" => $docList
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تقديم طلب إصدار قبول</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
/* صندوق النموذج */
.scholarship-details-box{ width:100%; max-width:850px; margin:20px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }

/* حقول النموذج */
.form-fields{ display:flex; flex-direction:column; gap:14px; }

/* الحقل */
.field{ margin-bottom:12px; }

/* عنوان الحقل */
.field label{ display:block; margin-bottom:6px; color:#333; font-size:14px; font-weight:600; font-family:'Noto Kufi Arabic'; }

/* الإدخال */
.field input{ width:100%; height:40px; border:1.5px solid #8FB4C9; background:#fff; padding:8px 10px; font-size:13px; color:#000; outline:none; font-family:'Noto Kufi Arabic'; border-radius:4px; }

/* النص التوضيحي */
.field input::placeholder{ color:#9b9b9b; }

/* القائمة */
.field select{ width:100%; height:40px; border:1.5px solid #8FB4C9; background:#fff; padding:8px 10px; font-size:13px; color:#000; outline:none; font-family:'Noto Kufi Arabic'; border-radius:4px; }

/* خيارات القائمة */
.field select option{ color:#000; }

/* عنوان النموذج */
.form-subtitle{ text-align:center; margin-bottom:18px; color:#3E2454; font-weight:bold; font-size:18px; font-family:'Noto Kufi Arabic'; }

/* عنوان المستندات */
.docs-title{ text-align:center; margin-bottom:18px; color:#3E2454; font-weight:bold; font-size:18px; font-family:'Noto Kufi Arabic'; }

/* قسم المستندات */
.docs-section{ display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-top:14px; }

/* مستند */
.doc-item{ text-align:right; }

/* عنوان المستند */
.doc-item .title-label{ font-size:13px; font-weight:600; margin-bottom:8px; display:block; color:#333; font-family:'Noto Kufi Arabic'; min-height:38px; line-height:1.6; }

/* مربع الرفع */
.upload-wrapper{ border:2px solid; border-image-source:linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%); border-image-slice:1; background-color:#F8F8F8; height:34px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:0.3s; }

/* مرور على الرفع */
.upload-wrapper:hover{ background-color:#ececec; }

/* أيقونة الرفع */
.upload-img{ width:20px; height:auto; }

/* اسم الملف */
.file-name-display{ display:none; font-size:10px; color:#70A0AF; height:34px; padding:6px 8px; margin-top:0; text-align:center; word-break:break-word; font-family:'Noto Kufi Arabic'; border:2px solid; border-image-source:linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%); border-image-slice:1; background:#F8F8F8; align-items:center; justify-content:center; cursor:pointer; }

/* زر كبير */
.big-btn{ padding:10px 55px; font-size:15px; font-weight:700; }

/* توسيط الزر */
.center-btn{ text-align:center; margin-top:30px; }

/* رسالة */
.msg{ max-width:850px; margin:0 auto 14px; padding:12px; border-radius:6px; text-align:center; font-size:14px; font-family:'Noto Kufi Arabic'; }

/* رسالة خطأ */
.msg.error{ background:#fff1f1; color:#b42318; border:1px solid #efb4b4; }

/* الصفحة */
.page{ padding:18px 30px 30px; }

/* منطقة الرجوع */
.back-wrap{ display:flex; justify-content:flex-end; max-width:850px; margin:0 auto 10px; }

/* زر الرجوع */
.back-btn{ width:50px; height:50px; display:flex; align-items:center; justify-content:center; }

/* أيقونة الرجوع */
.back-icon{ width:46px; height:46px; object-fit:contain; }

/* النجمة */
.star{ color:red !important; font-weight:bold; margin-left:3px; }

/* زر الإرسال */
.form-submit-btn{ font-family:'Noto Kufi Arabic'; font-size:18px; font-weight:700; background-color:#70A0AF; color:#ffffff; cursor:pointer; border:none; border-radius:4px; }

/* إخفاء */
.hidden{ display:none; }
</style>
</head>
<body>

<!-- التخطيط العام -->
<div class="layout">
    <!-- القائمة الجانبية -->
    <aside class="sidebar">
        <div class="sidebar-top">
            <!-- الشعار -->
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="نورين">
            </div>

            <!-- روابط القائمة -->
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php" class="active">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
                <li><a href="Ben19_Consultations.php">الاستشارات</a></li>
            </ul>
        </div>

        <!-- تسجيل الخروج -->
        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">

        <!-- الهيدر -->
        <header class="header">
            <div class="page-heading">
                <div class="page-title">المكاتب الاستشارية</div>
                <div class="page-description">صفحة تقديم طلب إصدار قبول</div>
            </div>

            <!-- الإعدادات -->
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- الصفحة -->
        <div class="page">

            <!-- زر الرجوع -->
            <div class="back-wrap">
                <a href="Ben14_OfficeDetails.php?id=<?php echo $office_id; ?>" class="back-btn">
                    <img src="سهم تراجع.svg" class="back-icon" alt="رجوع">
                </a>
            </div>

            <!-- رسالة الخطأ -->
            <?php if (!empty($msg)) { ?>
                <div class="msg <?php echo $type; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php } ?>

            <!-- صندوق النموذج -->
            <div class="scholarship-details-box">

                <!-- نموذج الطلب -->
                <form method="POST" enctype="multipart/form-data" id="admissionForm">

                    <!-- نوع البرنامج -->
                    <div class="field">
                        <label><b class="star">*</b> نوع البرنامج</label>
                        <select name="program" id="programSelect" required>
                            <option value="">اختر نوع البرنامج</option>
                            <option value="bachelor" <?php if ($program == "bachelor") echo "selected"; ?>>بكالوريوس</option>
                            <option value="masters" <?php if ($program == "masters") echo "selected"; ?>>ماجستير</option>
                            <option value="phd" <?php if ($program == "phd") echo "selected"; ?>>دكتوراه</option>
                        </select>
                    </div>

                    <!-- بيانات البرنامج -->
                    <div id="programArea" class="<?php echo ($program == "") ? 'hidden' : ''; ?>">
                        <div class="form-subtitle" id="programTitle"></div>

                        <div class="personal-info-grid">
                            <div class="form-fields">

                                <!-- اسم الجامعة -->
                                <div class="field">
                                    <label><b class="star">*</b> اسم الجامعة المرغوبة</label>
                                    <input type="text" name="univ_name" id="univ_name" placeholder="ادخل اسم الجامعة" value="<?php echo htmlspecialchars($univ_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <!-- التخصص -->
                                <div class="field">
                                    <label><b class="star">*</b> التخصص الدراسي المرغوب</label>
                                    <input type="text" name="major_name" id="major_name" placeholder="ادخل التخصص الدراسي المرغوب" value="<?php echo htmlspecialchars($major_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- المستندات -->
                        <div class="docs-title">رفع المستندات</div>
                        <div class="docs-section" id="docsSection"></div>
                    </div>

                    <!-- زر الحفظ -->
                    <div class="center-btn">
                        <button type="button" id="submitBtn" class="form-submit-btn big-btn">حفظ وتقديم الطلب</button>
                    </div>

                    <!-- زر الإرسال الفعلي -->
                    <button type="submit" name="submit_request" id="realSubmitBtn" style="display:none;"></button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
/* عناصر الصفحة */
const programSelect = document.getElementById("programSelect");
const programArea = document.getElementById("programArea");
const programTitle = document.getElementById("programTitle");
const docsSection = document.getElementById("docsSection");
const submitBtn = document.getElementById("submitBtn");
const realSubmitBtn = document.getElementById("realSubmitBtn");

/* بيانات البرامج */
const programsData = <?php echo json_encode($jsPrograms, JSON_UNESCAPED_UNICODE); ?>;

/* بناء حقول المستندات */
function buildDocs(program){
  docsSection.innerHTML = "";

  if(!program || !programsData[program]){
    programArea.classList.add("hidden");
    return; }

  programArea.classList.remove("hidden");
  programTitle.textContent = programsData[program].title;

  programsData[program].docs.forEach(function(doc){
    const docItem = document.createElement("div");
    docItem.className = "doc-item";

    let star = '<b class="star">*</b> ';
    if(doc.name === "other_file"){
      star = ""; }

    docItem.innerHTML =
      '<label class="title-label">' + star + doc.label + '</label>' +
      '<label for="' + doc.name + '" class="upload-wrapper"><img src="upload.png" class="upload-img" alt="رفع"></label>' +
      '<input type="file" name="' + doc.name + '" id="' + doc.name + '" accept=".pdf" style="display:none;" onchange="showFileName(this)">' +
      '<div class="file-name-display" onclick="openSelectedFile(this)"></div>';

    docsSection.appendChild(docItem);  });}

/* عرض اسم الملف */
function showFileName(input){
  const container = input.closest(".doc-item");
  const fileBox = container.querySelector(".file-name-display");
  const uploadBox = container.querySelector(".upload-wrapper");

  if(input.files && input.files[0]){
    fileBox.innerHTML = input.files[0].name;
    fileBox.style.display = "flex";
    uploadBox.style.display = "none";}}

/* إعادة اختيار الملف */
function openSelectedFile(fileBox){
  const container = fileBox.closest(".doc-item");
  const input = container.querySelector('input[type="file"]');

  if(input){
    input.click(); }}

/* التحقق قبل الإرسال */
function validateVisibleSection(){
  const activeProgram = programSelect.value;
  const univField = document.getElementById("univ_name");
  const majorField = document.getElementById("major_name");

  if(!activeProgram){
    alert("يرجى اختيار نوع البرنامج.");
    return false; }

  if(!univField || !majorField || univField.value.trim() === "" || majorField.value.trim() === ""){
    alert("يرجى تعبئة اسم الجامعة والتخصص الدراسي.");
    return false; }

  const fileInputs = docsSection.querySelectorAll('input[type="file"]');

  for(let i = 0; i < fileInputs.length; i++){
    const inputName = fileInputs[i].name;

    /** الملف الاختياري **/
    if(inputName === "other_file"){
      if(fileInputs[i].files.length > 0){
        const optionalFileName = fileInputs[i].files[0].name.toLowerCase();

        if(!optionalFileName.endsWith(".pdf")){
          alert("جميع الملفات يجب أن تكون PDF فقط.");
          return false;  }   }   continue; }

    /** الملفات المطلوبة **/
    if(fileInputs[i].files.length === 0){
      alert("يرجى رفع جميع الملفات المطلوبة.");
      return false;
    }
  }

  return true;
}

/* تغيير البرنامج */
if(programSelect){
  programSelect.addEventListener("change", function(){
    buildDocs(this.value);
  });
}

/* إرسال النموذج */
if(submitBtn){
  submitBtn.addEventListener("click", function(){
    if(validateVisibleSection()){
      realSubmitBtn.click();
    }
  });
}

/* بناء النموذج أوليًا */
buildDocs(programSelect.value);
</script>

</body>
</html>