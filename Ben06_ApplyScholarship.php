<?php
session_start();

/** التحقق من تسجيل دخول المستفيد قبل فتح صفحة التقديم **/
if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = new mysqli("localhost", "root", "", "noreen");

if ($con->connect_error) {
    die("فشل الاتصال بالقاعدة: " . $con->connect_error);
}

/* دعم اللغة العربية */
$con->set_charset("utf8mb4");

/* جلب رقم المستفيد الحالي */
$bnf_id = (int) $_SESSION['bnf_id'];

/* جلب رقم المنحة من الرابط */
$sch_id = isset($_GET['sch_id']) ? (int) $_GET['sch_id'] : 0;

/** التأكد من صحة رقم المنحة قبل عرض النموذج **/
if ($sch_id <= 0) {
    die("رقم المنحة غير صحيح");
}

/* جلب بيانات المستفيد */
$sql_user = "
    SELECT
        f_name,
        l_name,
        phone_num,
        sch_field,
        degree_level,
        email
    FROM beneficiary
    WHERE bnf_id = ?
    LIMIT 1
";

$stmt_user = $con->prepare($sql_user);
$stmt_user->bind_param("i", $bnf_id);
$stmt_user->execute();
$res_user = $stmt_user->get_result();

/** التأكد من وجود بيانات المستفيد قبل إكمال التقديم **/
if ($res_user->num_rows == 0) {
    die("لم يتم العثور على بيانات المستفيد");
}

$userData = $res_user->fetch_assoc();
$stmt_user->close();

/** دالة رفع المستند وحفظ بياناته في جدول مستندات الطلب **/
function uploadDoc($con, $req_id, $input, $type, $dir) {

    if (!empty($_FILES[$input]['name'])) {

        $fName = time() . "_" . basename($_FILES[$input]['name']);
        $targetPath = $dir . $fName;

        if (move_uploaded_file($_FILES[$input]['tmp_name'], $targetPath)) {

            $stmt_doc = $con->prepare("
                INSERT INTO scholarship_request_documents
                (request_id, doc_type, file_name, file)
                VALUES (?, ?, ?, ?)
            ");

            $stmt_doc->bind_param("isss", $req_id, $type, $fName, $fName);
            $stmt_doc->execute();
            $stmt_doc->close();
        }
    }
}

/* معالجة إرسال نموذج طلب المنحة */
if (isset($_POST['submit_request'])) {

    /* استقبال بيانات الجامعة والتخصص */
    $univ = trim($_POST['university']);
    $major = trim($_POST['major']);
    $today = date("Y-m-d");

    /** إنشاء طلب منحة جديد بحالة تحت المراجعة **/
    $stmt_req = $con->prepare("
        INSERT INTO scholarship_requests
        (scholarship_id, bnf_id, Submit_date, request_status, major_name, univ_name)
        VALUES (?, ?, ?, 'تحت المراجعة', ?, ?)
    ");

    $stmt_req->bind_param("iisss", $sch_id, $bnf_id, $today, $major, $univ);

    if ($stmt_req->execute()) {

        /* رقم الطلب الذي تم إنشاؤه */
        $req_id = $con->insert_id;

        /* مجلد رفع الملفات */
        $upload_dir = "uploads/";

        /** حفظ المستندات المرتبطة بطلب المنحة **/
        uploadDoc($con, $req_id, 'cv_file', 'CV', $upload_dir);
        uploadDoc($con, $req_id, 'cert_file', 'Certificate', $upload_dir);
        uploadDoc($con, $req_id, 'rec_file', 'Recommendation', $upload_dir);
        uploadDoc($con, $req_id, 'accept_file', 'Acceptance', $upload_dir);

        echo "<script>alert('تم إرسال طلبك بنجاح'); window.location.href='Ben09_TrackScholarship.php';</script>";
        exit();
    }

    $stmt_req->close();
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
/* مساحة الصفحة */
.page{ background-color:#F8F8F8; padding:20px 40px; position:relative; }

/* الشريط العلوي داخل الصفحة */
.page-top{ display:flex; justify-content:flex-end; padding:0 0 10px 0; margin:0; }

/* زر الرجوع */
.back-btn-details{ display:inline-block; cursor:pointer; text-decoration:none; background:none; border:none; margin-left:150px; }

/* أيقونة الرجوع */
.back-btn-details img{ width:38px; height:38px; display:block; }

/* صندوق النموذج */
.form-box{ max-width:1100px; margin:auto; }

/* تنسيق البطاقة الرئيسية للنموذج */
.scholarship-details-box{ width:100%; max-width:1000px; margin:0 auto; padding:30px; border:0.5px solid #D4D4D4; border-radius:12px; box-shadow:none; background:#FFFFFF; }

/* عنوان النموذج */
.main-title{ text-align:center; color:#3E2454; }

/* عنوان القسم */
.form-subtitle{ text-align:center; margin-bottom:25px; color:#3E2454; font-weight:bold; }

/* عنوان قسم المستندات */
.docs-title{ text-align:center; margin-top:40px; color:#3E2454; font-weight:bold; }

/* تقسيم المعلومات الشخصية */
.personal-info-grid{ display:grid; grid-template-columns:1fr 1fr; gap:40px; background:#F9F9F9; padding:25px; border-radius:10px; margin-bottom:30px; }

/* البيانات الثابتة */
.static-data{ order:1; }

/* نصوص البيانات الثابتة */
.static-data p{ margin:12px 0; font-size:16px; color:#3E2454; display:flex; font-family:'Noto Kufi Arabic', sans-serif; }

/* عناوين البيانات الثابتة */
.static-data b{ color:#70A0AF; display:inline-block; width:140px; font-family:'Noto Kufi Arabic', sans-serif; }

/* حقول الإدخال */
.form-fields{ order:2; display:flex; flex-direction:column; gap:20px; }

/* قسم المستندات */
.docs-section{ display:grid; grid-template-columns:repeat(4, 1fr); gap:15px; margin-top:20px; }

/* عنصر المستند */
.doc-item{ text-align:right; }

/* عنوان المستند */
.doc-item label.title-label{ font-size:14px; font-weight:600; margin-bottom:10px; display:block; color:#333333; font-family:'Noto Kufi Arabic', sans-serif; }

/* صندوق رفع الملف */
.upload-wrapper{ background-color:#F8F8F8; height:30px; width:160px; border-radius:6px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:0.3s; border:2px solid; border-image-source:linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%); border-image-slice:1; }

/* تأثير المرور على حقل الرفع */
.upload-wrapper:hover{ border-color:#3E2454; background-color:#ECECEC; }

/* أيقونة الرفع */
.upload-img{ width:20px; height:auto; }

/* اسم الملف المختار */
.file-name-display{ font-size:11px; color:#70A0AF; margin-top:6px; word-break:break-word; }

/* حاوية زر الإرسال */
.center-btn{ text-align:center; margin-top:30px; }

/* النجمة الحمراء */
.star{ color:red; font-weight:bold; margin-left:3px; }

/* زر إرسال الطلب */
.form-submit-btn{ font-family:'Noto Kufi Arabic', sans-serif; font-size:16px; font-weight:700; background-color:#70A0AF; color:#FFFFFF; cursor:pointer; border:none; border-radius:4px; transition:0.3s; padding:8px 40px; }

/* تأثير المرور على زر الإرسال */
.form-submit-btn:hover{ opacity:0.9; transform:translateY(-2px); }
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

            <!-- زر الرجوع بنفس موضع صفحة تفاصيل المنحة -->
            <div class="page-top">
                <a href="Ben05_ScholarshipDetails.php?id=<?php echo $sch_id; ?>" class="back-btn-details">
                    <img src="سهم تراجع.svg" alt="رجوع">
                </a>
            </div>

            <div class="form-box">

                <div class="scholarship-details-box">

                    <h2 class="main-title">نموذج تقديم طلب منحة</h2>

                    <form method="POST" enctype="multipart/form-data">

                        <div class="form-subtitle">المعلومات الشخصية</div>

                        <div class="personal-info-grid">

                            <!-- بيانات المستفيد المسجلة مسبقًا -->
                            <div class="static-data">
                                <p><b>الاسم:</b> <?php echo htmlspecialchars($userData['f_name'] . " " . $userData['l_name']); ?></p>
                                <p><b>رقم الهاتف:</b> <span dir="ltr"><?php echo htmlspecialchars($userData['phone_num']); ?></span></p>
                                <p><b>المجال الدراسي:</b> <?php echo htmlspecialchars($userData['sch_field']); ?></p>
                                <p><b>المؤهل الدراسي:</b> <?php echo htmlspecialchars($userData['degree_level']); ?></p>
                                <p><b>البريد الإلكتروني:</b> <?php echo htmlspecialchars($userData['email']); ?></p>
                            </div>

                            <!-- بيانات يضيفها المستفيد لهذا الطلب -->
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

                        <div class="docs-title">المستندات المطلوبة</div>

                        <div class="docs-section">

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> السيرة الذاتية</label>
                                <label for="cv" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                    <input type="file" name="cv_file" id="cv" style="display:none;" required onchange="showName(this)">
                                </label>
                                <div class="file-name-display"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> شهادة آخر مؤهل</label>
                                <label for="cert" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                    <input type="file" name="cert_file" id="cert" style="display:none;" required onchange="showName(this)">
                                </label>
                                <div class="file-name-display"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> خطابات التوصية</label>
                                <label for="rec" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                    <input type="file" name="rec_file" id="rec" style="display:none;" required onchange="showName(this)">
                                </label>
                                <div class="file-name-display"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> خطاب القبول الجامعي</label>
                                <label for="accept" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                    <input type="file" name="accept_file" id="accept" style="display:none;" required onchange="showName(this)">
                                </label>
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
/* عرض اسم الملف المختار أسفل أيقونة الرفع */
function showName(input) {
    if (input.files && input.files[0]) {
        input.parentElement.nextElementSibling.innerHTML = input.files[0].name;
    }
}
</script>

</body>
</html>