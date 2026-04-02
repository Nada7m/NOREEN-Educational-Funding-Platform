<?php
session_start();

$con = new mysqli("localhost", "root", "", "noreen");
if ($con->connect_error) { die("فشل الاتصال بالقاعدة: " . $con->connect_error); }

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// جلب رقم المستخم
$user_id = $_SESSION['user_id'];

//جلب رقم المنحة من الرابط
$sch_id = isset($_GET['sch_id']) ? intval($_GET['sch_id']) : 1;

// جلب بيانات المستخدم 
$sql_user = "SELECT * FROM beneficiary WHERE bnf_id = $user_id ";
$res_user = $con->query($sql_user);
$userData = $res_user->fetch_assoc();

// معالجة إرسال النموذج (الضغط على إرسال الطلب)
if(isset($_POST['submit_request'])){
    $univ = $con->real_escape_string($_POST['university']);
    $major = $con->real_escape_string($_POST['major']);
    $today = date("Y-m-d");

    // إدخال الطلب في جدول scholarship_requests
    $sql_req = "INSERT INTO scholarship_requests (scholarship_id, bnf_id, Submit_date, request_status, major_name, univ_name) 
                VALUES ('$sch_id', '$user_id', '$today','$major', '$univ')";
    
    if($con->query($sql_req)){
        $req_id = $con->insert_id;
        $upload_dir = "upload/";
         }

        // دالة رفع المستندات وحفظها في جدول scholarship_request_documents
        function uploadDoc($con, $req_id, $input, $type, $dir) {
            if(!empty($_FILES[$input]['name'])) {
                $fName = time() . "_" . $_FILES[$input]['name'];
                if(move_uploaded_file($_FILES[$input]['tmp_name'], $dir . $fName))
                     
                   { $sql_d = "INSERT INTO scholarship_request_documents (request_id, doc_type, file_name, file) 
                              VALUES ('$req_id', '$type', '$fName', '$fName')";
                    $con->query($sql_d);}
                }
            
        }

        uploadDoc($con, $req_id, 'cv_file', 'CV', $upload_dir);
        uploadDoc($con, $req_id, 'cert_file', 'Certificate', $upload_dir);
        uploadDoc($con, $req_id, 'rec_file', 'Recommendation', $upload_dir);
        uploadDoc($con, $req_id, 'accept_file', 'Acceptance', $upload_dir);

        echo "<script>alert('✅ تم إرسال طلبك بنجاح!'); window.location.href='Ben09_TrackScholarship.php';</script>";
    }

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التقديم على المنح</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css?v=3">
    <link rel="stylesheet" href="Style.css"> </head>

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
                <li><a href="#">الاستشارات</a></li>
            </ul>
        </div>
        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn"><img src="ايقونة تسجيل الخروج.png" class="logout-icon"><b>تسجيل الخروج</b></button>
            </form>
        </div>
    </aside>

    <div class="main-content">
        <header class="header">
            <div class="page-heading">
                <div class="page-title">التقديم على المنح</div>
                <div class="page-description">صفحة نموذج التقديم على المنح المعروضة </div>
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
            <div class="back-wrap" style="display: flex; justify-content: flex-end;">
    <a href="Ben04_BrowseScholarships.php?id=<?php echo $sch_id; ?>" class="back-btn">
        <img src="سهم تراجع.svg" class="back-icon" alt="رجوع" style="width: 45px; height: 45px;">
    </a>
</div>

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
                                    <input type="file" name="cv_file" id="cv" style="display:none;" required onchange="showName(this)">
                                </label>
                                <div class="file-name-display" style="font-size: 11px; color: #70A0AF;"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> شهادة آخر مؤهل</label>
                                <label for="cert" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                    <input type="file" name="cert_file" id="cert" style="display:none;" required onchange="showName(this)">
                                </label>
                                <div class="file-name-display" style="font-size: 11px; color: #70A0AF;"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> خطابات التوصية</label>
                                <label for="rec" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                    <input type="file" name="rec_file" id="rec" style="display:none;" required onchange="showName(this)">
                                </label>
                                <div class="file-name-display" style="font-size: 11px; color: #70A0AF;"></div>
                            </div>

                            <div class="doc-item">
                                <label class="title-label"><span class="star">*</span> خطاب القبول الجامعي</label>
                                <label for="accept" class="upload-wrapper">
                                    <img src="upload.png" class="upload-img" alt="رفع">
                                    <input type="file" name="accept_file" id="accept" style="display:none;" required onchange="showName(this)">
                                </label>
                                <div class="file-name-display" style="font-size: 11px; color: #70A0AF;"></div>
                            </div>
                        </div>

                        <div class="center-btn" style="text-align: center; margin-top: 30px;">
                            <button type="submit" name="submit_request" class="form-submit-btn">إرسال الطلب</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// وظيفة بسيطة لإظهار اسم الملف المختار تحت أيقونة الرفع
function showName(input) {
    if (input.files && input.files[0]) {
        input.parentElement.nextElementSibling.innerHTML = "✅ " + input.files[0].name;
    }
}
</script>

</body>
</html>