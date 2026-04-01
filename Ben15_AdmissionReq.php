<?php
session_start();
if (empty($_SESSION['bnf_id'])) exit();

$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

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
    "phd" => "نموذج تقديم طلب إصدار القبول الجامعي – دكتوراه"
];

/* المستندات المشتركة */
$commonDocs = [
    "cv_file" => ["type" => "CV", "label" => "السيرة الذاتية"],
    "passport_file" => ["type" => "Passport", "label" => "جواز السفر"],
    "language_file" => ["type" => "Language Certificate", "label" => "شهادة اللغة"],
    "recommendation_file" => ["type" => "Recommendation Letters", "label" => "خطابات التوصية"],
    "other_file" => ["type" => "Other Certificates", "label" => "شهادات أخرى"]
];

/* المستندات الخاصة بكل برنامج */
$programDocs = [
    "bachelor" => [
        "highschool_file" => ["type" => "High School Certificate", "label" => "شهادة الثانوية العامة"],
        "intent_file" => ["type" => "Letter of Intent", "label" => "خطاب النوايا"]
    ],
    "masters" => [
        "degree_file" => ["type" => "University Degree Certificate", "label" => "الشهادة الجامعية"],
        "transcript_file" => ["type" => "Academic Transcript", "label" => "السجل الأكاديمي"],
        "sop_file" => ["type" => "Statement of Purpose", "label" => "بيان الغرض الدراسي"]
    ],
    "phd" => [
        "academic_file" => ["type" => "Academic Certificates", "label" => "الشهادات الأكاديمية"],
        "research_file" => ["type" => "Research Proposal", "label" => "المقترح البحثي"],
        "sop_file" => ["type" => "Statement of Purpose", "label" => "بيان الغرض الدراسي"]
    ]
];

/* دالة رفع الملفات */
function uploadAdmissionFile($inputName, $requestId, $docType, $uploadDir, $conn)
{
    if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {
        return false;
    }

    $tmpName = $_FILES[$inputName]['tmp_name'];
    $oldName = $_FILES[$inputName]['name'];

    if (!str_ends_with(strtolower($oldName), ".pdf")) {
        return false;
    }

    $newName = $requestId . "_" . $inputName . ".pdf";
    $filePath = $uploadDir . $newName;

    if (move_uploaded_file($tmpName, $filePath)) {
        $safeOldName = $conn->real_escape_string($oldName);
        $safeFilePath = $conn->real_escape_string($filePath);
        $safeDocType = $conn->real_escape_string($docType);

        $sqlDoc = "INSERT INTO admission_request_documents (request_id, doc_type, file_name, file)
                   VALUES ('$requestId', '$safeDocType', '$safeOldName', '$safeFilePath')";
        $conn->query($sqlDoc);

        return true;
    }

    return false;
}

/* معالجة إرسال النموذج */
if (isset($_POST['submit_request'])) {

    $program = isset($_POST['program']) ? trim($_POST['program']) : "";
    $univ_name = isset($_POST['univ_name']) ? trim($_POST['univ_name']) : "";
    $major_name = isset($_POST['major_name']) ? trim($_POST['major_name']) : "";
    $payment_done = isset($_POST['payment_done']) ? trim($_POST['payment_done']) : "0";

    if ($program == "") {
        $msg = "يرجى اختيار نوع البرنامج.";
        $type = "error";
    } elseif (!isset($programDocs[$program])) {
        $msg = "البرنامج المختار غير صحيح.";
        $type = "error";
    } elseif ($univ_name == "" || $major_name == "") {
        $msg = "جميع الحقول مطلوبة.";
        $type = "error";
    } elseif ($payment_done != "1") {
        $msg = "لا يمكن تقديم الطلب قبل إتمام الدفع.";
        $type = "error";
    } else {

        $requiredDocs = array_merge($commonDocs, $programDocs[$program]);
        $missingFile = false;
        $wrongType = false;

        foreach ($requiredDocs as $inputName => $docData) {
            if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) {
                $missingFile = true;
                break;
            }

            $fileName = strtolower($_FILES[$inputName]['name']);
            if (!str_ends_with($fileName, ".pdf")) {
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
                       ('$bnf_id', '$office_id', '$safeMajor', '$safeUniv', '$today','', '')";

            if ($conn->query($sqlReq)) {

                $requestId = $conn->insert_id;
                $uploadDir = "uploads/admission_requests/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($requiredDocs as $inputName => $docData) {
                    uploadAdmissionFile($inputName, $requestId, $docData["type"], $uploadDir, $conn);
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

/* بيانات للجافاسكربت */
$jsPrograms = [];
foreach ($programDocs as $programKey => $docs) {
    $mergedDocs = array_merge($commonDocs, $docs);
    $docList = [];

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
<link rel="stylesheet" href="CSS01Layout.css?v=2">

<style>
.scholarship-details-box{ width:100%; max-width:850px; margin:20px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }
.form-fields{ display:flex; flex-direction:column; gap:14px; }
.field{ margin-bottom:12px; }
.field label{ display:block; margin-bottom:6px; color:#333; font-size:14px; font-weight:600; font-family:'Noto Kufi Arabic',   }
.field input{ width:100%; height:40px; border:1.5px solid #8FB4C9; background:#fff; padding:8px 10px; font-size:13px; color:#000; outline:none; font-family:'Noto Kufi Arabic',   border-radius:4px; }
.field input::placeholder{ color:#9b9b9b; }
.field select{ width:100%; height:40px; border:1.5px solid #8FB4C9; background:#fff; padding:8px 10px; font-size:13px; color:#000; outline:none; font-family:'Noto Kufi Arabic',   border-radius:4px; }
.field select option{ color:#000; }
.form-subtitle{ text-align:center; margin-bottom:18px; color:#3E2454; font-weight:bold; font-size:18px; font-family:'Noto Kufi Arabic',   }
.docs-title{ text-align:center; margin-bottom:18px; color:#3E2454; font-weight:bold; font-size:18px; font-family:'Noto Kufi Arabic',   }
.docs-section{ display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-top:14px; }
.doc-item{ text-align:right; }
.doc-item .title-label{ font-size:13px; font-weight:600; margin-bottom:8px; display:block; color:#333; font-family:'Noto Kufi Arabic',   min-height:38px; line-height:1.6; }
.upload-wrapper{ border:2px solid; border-image-source:linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%); border-image-slice:1; background-color:#F8F8F8; height:34px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:0.3s; }
.upload-wrapper:hover{ background-color:#ececec; }
.upload-img{ width:20px; height:auto; }
.file-name-display{ display:none; font-size:10px; color:#70A0AF; height:34px; padding:6px 8px; margin-top:0; text-align:center; word-break:break-word; font-family:'Noto Kufi Arabic',   border:2px solid; border-image-source:linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%); border-image-slice:1; background:#F8F8F8; align-items:center; justify-content:center; cursor:pointer; }
.big-btn{ padding:10px 55px; font-size:15px; font-weight:700; }
.center-btn{ text-align:center; margin-top:30px; }
.msg{ max-width:850px; margin:0 auto 14px; padding:12px; border-radius:6px; text-align:center; font-size:14px; font-family:'Noto Kufi Arabic',   }
.msg.error{ background:#fff1f1; color:#b42318; border:1px solid #efb4b4; }
.page{ padding:18px 30px 30px; }
.back-wrap{ display:flex; justify-content:flex-end; max-width:850px; margin:0 auto 10px; }
.back-btn{ width:50px; height:50px; display:flex; align-items:center; justify-content:center; }
.back-icon{ width:46px; height:46px; object-fit:contain; }
.star{ color:red !important; font-weight:bold; margin-left:3px; }
.form-submit-btn{ font-family:'Noto Kufi Arabic', font-size:18px; font-weight:700; background-color:#70A0AF; color:#ffffff; cursor:pointer; border:none; border-radius:4px;  }
.pay-modal{ display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:3000; justify-content:center; align-items:center; }

.pay-card{ width:700px; max-width:92%; background:#fff; border-radius:10px; padding:30px 24px; box-shadow:0 8px 24px rgba(0,0,0,0.18); }

.pay-title{ text-align:center; color:#d46a6a; font-size:22px; font-weight:700; margin-bottom:18px; font-family:'Noto Kufi Arabic',   }

.pay-box{ width:78%; margin:0 auto; border:1px solid #e0e0e0; border-radius:10px; padding:18px; }

.pay-box-title{ text-align:center; font-size:20px; font-weight:700; color:#333; margin-bottom:16px; font-family:'Noto Kufi Arabic',   }

.pay-field{ margin-bottom:14px; }

.pay-field input{ width:100%; height:46px; border:1px solid #d1d1d1; border-radius:6px; padding:8px 12px; font-size:14px; font-family:'Noto Kufi Arabic',   outline:none; }

.pay-row{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }

.pay-confirm{ width:100%; height:46px; border:none; border-radius:6px; background:#70A0AF; color:#fff; font-size:18px; font-weight:700; font-family:'Noto Kufi Arabic',   cursor:pointer; margin-top:10px; }

.close-btn{ display:block; margin:12px auto 0; background:none; border:none; color:#777; font-size:14px; font-family:'Noto Kufi Arabic',   cursor:pointer; }

.hidden{ display:none; }
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
                 <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
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
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon">
<div class="dropdown-menu">
<a href="Ben02_Profile.php">الملف الشخصي</a>
<a href="#">محفظة منحتي</a>
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

                <form method="POST" enctype="multipart/form-data" id="admissionForm">

                    <div class="field">
                        <label><b class="star">*</b> نوع البرنامج</label>
                        <select name="program" id="programSelect" required>
                            <option value="">اختر نوع البرنامج</option>
                            <option value="bachelor" <?php if ($program == "bachelor") echo "selected"; ?>>بكالوريوس</option>
                            <option value="masters" <?php if ($program == "masters") echo "selected"; ?>>ماجستير</option>
                            <option value="phd" <?php if ($program == "phd") echo "selected"; ?>>دكتوراه</option>
                        </select>
                    </div>

                    <input type="hidden" name="payment_done" id="payment_done" value="0">

                    <div id="programArea" class="<?php echo ($program == "") ? 'hidden' : ''; ?>">
                        <div class="form-subtitle" id="programTitle"></div>

                        <div class="personal-info-grid">
                            <div class="form-fields">
                                <div class="field">
                                    <label><b class="star">*</b> اسم الجامعة المرغوبة</label>
                                    <input type="text" name="univ_name" id="univ_name" placeholder="ادخل اسم الجامعة" value="<?php echo ($univ_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <div class="field">
                                    <label><b class="star">*</b> التخصص الدراسي المرغوب</label>
                                    <input type="text" name="major_name" id="major_name" placeholder="ادخل التخصص الدراسي المرغوب" value="<?php echo ($major_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="docs-title">رفع المستندات</div>
                        <div class="docs-section" id="docsSection"></div>
                    </div>

                    <div class="center-btn">
                        <button type="button" id="openPaymentBtn" class="form-submit-btn big-btn">حفظ وتقديم الطلب</button>
                    </div>

                    <button type="submit" name="submit_request" id="realSubmitBtn" style="display:none;"></button>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="pay-modal" id="paymentModal">
    <div class="pay-card">
        <div class="pay-title">لاعتماد الطلب ومتابعة الإجراءات يرجى إتمام عملية الدفع</div>

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
const programSelect = document.getElementById("programSelect");
const programArea = document.getElementById("programArea");
const programTitle = document.getElementById("programTitle");
const docsSection = document.getElementById("docsSection");
const openPaymentBtn = document.getElementById("openPaymentBtn");
const paymentModal = document.getElementById("paymentModal");
const closePaymentBtn = document.getElementById("closePaymentBtn");
const confirmPaymentBtn = document.getElementById("confirmPaymentBtn");
const paymentDone = document.getElementById("payment_done");
const realSubmitBtn = document.getElementById("realSubmitBtn");

const programsData = <?php echo json_encode($jsPrograms, JSON_UNESCAPED_UNICODE); ?>;

function buildDocs(program){
  docsSection.innerHTML = "";

  if(!program || !programsData[program]){
    programArea.classList.add("hidden");
    return;
  }

  programArea.classList.remove("hidden");
  programTitle.textContent = programsData[program].title;

  programsData[program].docs.forEach(function(doc){
    const docItem = document.createElement("div");
    docItem.className = "doc-item";

    docItem.innerHTML =
      '<label class="title-label"><b class="star">*</b> ' + doc.label + '</label>' +
      '<label for="' + doc.name + '" class="upload-wrapper"><img src="upload.png" class="upload-img" alt="رفع"></label>' +
      '<input type="file" name="' + doc.name + '" id="' + doc.name + '" accept=".pdf" style="display:none;" onchange="showFileName(this)">' +
      '<div class="file-name-display" onclick="openSelectedFile(this)"></div>';

    docsSection.appendChild(docItem);
  });
}

function showFileName(input){
  const container = input.closest(".doc-item");
  const fileBox = container.querySelector(".file-name-display");
  const uploadBox = container.querySelector(".upload-wrapper");

  if(input.files && input.files[0]){
    fileBox.innerHTML = input.files[0].name;
    fileBox.style.display = "flex";
    uploadBox.style.display = "none";
  }
}

function openSelectedFile(fileBox){
  const container = fileBox.closest(".doc-item");
  const input = container.querySelector('input[type="file"]');
  if(input){
    input.click();
  }
}

function validateVisibleSection(){
  const activeProgram = programSelect.value;
  const univField = document.getElementById("univ_name");
  const majorField = document.getElementById("major_name");

  if(!activeProgram){
    alert("يرجى اختيار نوع البرنامج.");
    return false;
  }

  if(!univField || !majorField || univField.value.trim() === "" || majorField.value.trim() === ""){
    alert("يرجى تعبئة اسم الجامعة والتخصص الدراسي.");
    return false;
  }

  const fileInputs = docsSection.querySelectorAll('input[type="file"]');

  for(let i = 0; i < fileInputs.length; i++){
    if(fileInputs[i].files.length === 0){
      alert("يرجى رفع جميع الملفات المطلوبة.");
      return false;
    }

    const fileName = fileInputs[i].files[0].name.toLowerCase();
    if(!fileName.endsWith(".pdf")){
      alert("جميع الملفات يجب أن تكون PDF فقط.");
      return false;
    }
  }

  return true;
}

if(programSelect){
  programSelect.addEventListener("change", function(){
    buildDocs(this.value);
  });
}

if(openPaymentBtn){
  openPaymentBtn.addEventListener("click", function(){
    if(validateVisibleSection()){
      paymentModal.style.display = "flex";
    }
  });
}

if(closePaymentBtn){
  closePaymentBtn.addEventListener("click", function(){
    paymentModal.style.display = "none";
  });
}

if(confirmPaymentBtn){
  confirmPaymentBtn.addEventListener("click", function(){
    const cardName = document.getElementById("cardName").value.trim();
    const cardNumber = document.getElementById("cardNumber").value.trim();
    const expDate = document.getElementById("expDate").value.trim();
    const cvv = document.getElementById("cvv").value.trim();

    if(cardName === "" || cardNumber === "" || expDate === "" || cvv === ""){
      alert("يرجى تعبئة جميع بيانات الدفع.");
      return;
    }

    paymentDone.value = "1";
    paymentModal.style.display = "none";
    realSubmitBtn.click();
  });
}

window.addEventListener("click", function(e){
  if(e.target === paymentModal){
    paymentModal.style.display = "none";
  }
});

buildDocs(programSelect.value);
</script>

</body>
</html>