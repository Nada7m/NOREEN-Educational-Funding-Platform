<?php
session_start();

if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");

$office_id = (int) $_SESSION['office_id'];
$request_id = isset($_GET['request_id']) ? (int) $_GET['request_id'] : 0;

if ($request_id <= 0) {
    die("رقم الطلب غير صحيح");
}

$sql = "SELECT 
            ar.request_id,
            ar.request_status,
            ar.Result_status,
            ar.result_notes,
            ar.result,
            b.f_name,
            b.l_name
        FROM admission_request ar
        INNER JOIN beneficiary b ON ar.bnf_id = b.bnf_id
        WHERE ar.request_id = $request_id
        AND ar.office_id = $office_id";

$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("الطلب غير موجود");
}

$request = mysqli_fetch_assoc($result);

if ($request['request_status'] != 'مقبول') {
    die("لا يمكن رفع النتيجة قبل قبول الطلب");
}

$error = "";

if (isset($_POST['submit_result'])) {

    $result_notes = trim($_POST['result_notes']);
    $safe_notes = mysqli_real_escape_string($con, $result_notes);

    if (!isset($_FILES['result_file']) || empty($_FILES['result_file']['name'])) {
        $error = "يرجى رفع ملف النتيجة.";
    } else {

        $old_name = $_FILES['result_file']['name'];
        $tmp_name = $_FILES['result_file']['tmp_name'];
        $file_size = $_FILES['result_file']['size'];

        if (!str_ends_with(strtolower($old_name), ".pdf")) {
            $error = "يُسمح فقط برفع ملفات PDF.";
        } elseif ($file_size > 5 * 1024 * 1024) {
            $error = "حجم الملف كبير جدًا.";
        } else {

            $upload_dir = "uploads/admission_results/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_name = $request_id . "_result.pdf";
            $file_path = $upload_dir . $new_name;
            $safe_path = mysqli_real_escape_string($con, $file_path);

            if (move_uploaded_file($tmp_name, $file_path)) {

                $sqlUpdate = "UPDATE admission_request
                              SET result = '$safe_path',
                                  result_notes = '$safe_notes',
                                  Result_status = 'أصدرت'
                              WHERE request_id = $request_id
                              AND office_id = $office_id";

                if (mysqli_query($con, $sqlUpdate)) {
                    header("Location: Con05_AdmissiontDetails.php?request_id=" . $request_id);
                    exit();
                } else {
                    $error = "حدث خطأ أثناء حفظ النتيجة.";
                }

            } else {
                $error = "حدث خطأ أثناء رفع الملف.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>رفع خطاب النتيجة</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
.page-wrap{
    padding: 18px 30px 30px;
}

.back-wrap{
    display: flex;

    justify-content: flex-end;

    max-width: 1100px;

    margin: 22px auto 30px;
}

.back-btn{
    width: 50px;

    height: 50px;

    display: flex;

    align-items: center;

    justify-content: center;
}

.back-icon{
    width: 46px;

    height: 46px;

    object-fit: contain;
}

.upload-card{
    width: 100%;

    max-width: 1100px;

    margin: 0 auto;

    background: #ffffff;

    padding: 40px 45px;

    border-radius: 16px;

    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.card-head{
    display: flex;

    justify-content: center;

    align-items: center;

    gap: 12px;

    margin-bottom: 40px;

    flex-wrap: wrap;
}

.card-title{
    font-size: 24px;

    font-weight: 700;

    color: #222;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.student-name{
    font-size: 24px;

    font-weight: 700;

    color: #70A0AF;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.form-grid{
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 35px;
}

.form-group{
    display: flex;

    flex-direction: column;
}

.form-label{
    display: block;

    margin-bottom: 12px;

    color: #222;

    font-size: 15px;

    font-weight: 700;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.textarea-box{
    width: 100%;

    height: 160px;

    border: 1px solid #d9d9d9;

    border-radius: 4px;

    background: #fff;

    padding: 12px;

    font-size: 14px;

    color: #333;

    outline: none;

    resize: none;

    box-sizing: border-box;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.textarea-box::placeholder{
    color: #9b9b9b;
}

.upload-side{
    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: flex-start;
}

.upload-inner{
    width: 100%;
}

.upload-wrapper{
    border: 2px solid;

    border-image-source: linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%);

    border-image-slice: 1;

    background-color: #F8F8F8;

    width: 140px;

    height: 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    cursor: pointer;

    transition: 0.3s;

    margin: 0 auto;
}

.upload-wrapper:hover{
    background-color: #ececec;
}

.upload-img{
    width: 20px;

    height: auto;
}

.file-hidden{
    display: none;
}

.file-name-display{
    display: none;

    width: 220px;

    min-height: 40px;

    padding: 8px 10px;

    margin: 0 auto;

    text-align: center;

    word-break: break-word;

    font-size: 11px;

    color: #70A0AF;

    font-family: 'Noto Kufi Arabic', sans-serif;

    border: 2px solid;

    border-image-source: linear-gradient(90deg, #D6B7E2 0%, #3E2454 64%);

    border-image-slice: 1;

    background: #F8F8F8;

    align-items: center;

    justify-content: center;

    cursor: pointer;
}

.upload-note{
    margin-top: 14px;

    text-align: center;

    color: #777;

    font-size: 12px;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.current-file{
    margin-top: 14px;

    text-align: center;

    font-size: 13px;

    color: #555;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.current-file a{
    color: #4b6cb7;

    text-decoration: underline;
}

.submit-row{
    display: flex;

    justify-content: center;

    margin-top: 40px;
}

.submit-btn{
    min-width: 230px;

    padding: 14px 35px;

    background-color: #70A0AF;

    color: #ffffff;

    border: none;

    border-radius: 4px;

    cursor: pointer;

    font-size: 18px;

    font-weight: 700;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.msg{
    max-width: 1100px;

    margin: 0 auto 16px;

    padding: 12px;

    border-radius: 6px;

    text-align: center;

    font-size: 14px;

    font-family: 'Noto Kufi Arabic', sans-serif;
}

.msg.error{
    background: #fff1f1;

    color: #b42318;

    border: 1px solid #efb4b4;
}

@media (max-width: 900px){
    .form-grid{
        grid-template-columns: 1fr;
    }

    .upload-card{
        padding: 25px 20px;
    }

    .card-title{
        font-size: 20px;
    }

    .student-name{
        font-size: 20px;
    }
}
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
                <li><a href="Con00_MainPage.php">الرئيسية</a></li>
                <li><a href="Con04_AdmissionReq.php" class="active">إدارة طلبات القبول</a></li>
                <li><a href="Con0_Consultations.php">الاستشارات</a></li>
          <li><a href="Con08_ReqRating.php">تقييمات المستفيدين</a></li>
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
                <div class="page-title">إدارة طلبات القبول</div>
                <div class="page-description">رفع خطاب القبول أو النتيجة للمستفيد</div>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
                    <div class="dropdown-menu">
                        <a href="Con02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-wrap">

            <div class="back-wrap">
                <a href="Con05_AdmissiontDetails.php?request_id=<?php echo $request_id; ?>" class="back-btn">
                    <img src="سهم تراجع.svg" class="back-icon" alt="رجوع">
                </a>
            </div>

            <?php if (!empty($error)) { ?>
                <div class="msg error">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <div class="upload-card">

                <div class="card-head">
                    <div class="card-title">رفع خطاب النتيجة للمستفيد/ة</div>
                    <div class="student-name"><?php echo htmlspecialchars($request['f_name'] . " " . $request['l_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <form method="POST" enctype="multipart/form-data">

                    <div class="form-grid">

                        <div class="form-group">
                            <label class="form-label">ملاحظات:</label>
                            <textarea name="result_notes" class="textarea-box" placeholder="أضف ملاحظة إن وجدت"><?php echo htmlspecialchars($request['result_notes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="form-group upload-side">
                            <div class="upload-inner">
                                <label class="form-label">تقديم الخطاب:</label>

                                <label for="result_file" class="upload-wrapper" id="uploadBox">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                </label>

                                <input type="file" name="result_file" id="result_file" accept=".pdf" class="file-hidden" onchange="showFileName(this)">

                                <div class="file-name-display" id="fileNameBox" onclick="openSelectedFile()"></div>

                                <div class="upload-note">يُسمح فقط برفع ملف PDF</div>

                                <?php if (!empty($request['result'])) { ?>
                                    <div class="current-file">
                                        الملف الحالي:
                                        <a href="<?php echo htmlspecialchars($request['result'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">عرض الملف</a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                    </div>

                    <div class="submit-row">
                        <button type="submit" name="submit_result" class="submit-btn">إرسال النتيجة</button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</div>

<script>
function showFileName(input){

    const fileNameBox = document.getElementById("fileNameBox");
    const uploadBox = document.getElementById("uploadBox");

    if(input.files && input.files[0]){
        fileNameBox.innerHTML = input.files[0].name;
        fileNameBox.style.display = "flex";
        uploadBox.style.display = "none";
    }
}

function openSelectedFile(){
    document.getElementById("result_file").click();
}
</script>

</body>
</html>