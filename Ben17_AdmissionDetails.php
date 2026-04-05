<?php
session_start();

if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}

$conn->set_charset("utf8mb4");

$bnf_id = (int) $_SESSION['bnf_id'];
$request_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$msg = "";
$type = "";

/* جلب بيانات الطلب */
$sqlRequest = "SELECT 
                  ar.request_id,
                  ar.bnf_id,
                  ar.office_id,
                  ar.major_name,
                  ar.univ_name,
                  ar.Submit_date,
                  ar.result_notes,
                  ar.Result_status,
                  ar.result
               FROM admission_request ar
               WHERE ar.request_id = $request_id
               AND ar.bnf_id = $bnf_id";

$resultRequest = $conn->query($sqlRequest);

if (!$resultRequest || $resultRequest->num_rows == 0) {
    die("الطلب غير موجود");
}

$request = $resultRequest->fetch_assoc();
$office_id = (int) $request['office_id'];
$status = trim($request['Result_status']);

/* التحقق هل يوجد تقييم سابق لهذا الطلب */
$hasRating = false;
$lastComment = "";

$sqlLastRating = "SELECT rating_id, comment_text
                  FROM rating
                  WHERE request_id = $request_id
                  AND bnf_id = $bnf_id
                  ORDER BY rating_id DESC
                  LIMIT 1";

$resultLastRating = $conn->query($sqlLastRating);

if ($resultLastRating && $resultLastRating->num_rows > 0) {
    $lastRating = $resultLastRating->fetch_assoc();
    $lastComment = $lastRating['comment_text'];
    $hasRating = true;
}

/* حفظ تقييم المكتب */
if (isset($_POST['send_rating'])) {
    $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : "";

    if ($status == "" || $status == "قيد المعالجة") {
        $msg = "لا يمكن إرسال التقييم قبل صدور النتيجة.";
        $type = "error";
    } elseif ($hasRating) {
        $msg = "تم إرسال تقييمك مسبقًا لهذا الطلب.";
        $type = "error";
    } elseif ($comment_text == "") {
        $msg = "يرجى كتابة التقييم أولاً.";
        $type = "error";
    } else {
        $safeComment = $conn->real_escape_string($comment_text);

        $sqlInsertRating = "INSERT INTO rating (bnf_id, office_id, request_id, comment_text)
                            VALUES ('$bnf_id', '$office_id', '$request_id', '$safeComment')";

        if ($conn->query($sqlInsertRating)) {
            $msg = "تم استلام تقييمك بنجاح.";
            $type = "success";
            $lastComment = $comment_text;
            $hasRating = true;
        } else {
            $msg = "حدث خطأ أثناء إرسال التقييم.";
            $type = "error";
        }
    }
}

/* تنسيق الحالة */
$statusText = "";
$statusClass = "";
$statusMessage = "";

if ($status == "" || $status == "قيد المعالجة") {
    $statusText = "في انتظار إصدار النتيجة";
    $statusClass = "processing";
    $statusMessage = "طلبك ما زال تحت مراجعة المكتب، وسيتم تحديث هذه الخانة عند صدور النتيجة.";
} elseif ($status == "مرفوض" || $status == "مرفوضة" || $status == "رفض") {
    $statusText = "تم رفض الطلب";
    $statusClass = "rejected";
    $statusMessage = "تمت مراجعة الطلب من قبل المكتب ولم تتم الموافقة عليه.";
} else {
    $statusText = "تم إصدار النتيجة";
    $statusClass = "done";
    $statusMessage = "تمت معالجة الطلب ويمكنك الاطلاع على ملاحظات المكتب والتفاصيل أدناه.";
}

$requestCode = "UA" . $request['request_id'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تفاصيل طلب إصدار القبول</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
body{
    font-family:'Noto Kufi Arabic', sans-serif;
}

.page{
    padding:18px 24px 30px;
}

.content-box{
    width:100%;
    max-width:930px;
    margin:18px auto;
    background:#fff;
    border:1px solid #ece7f1;
    border-radius:16px;
    overflow:hidden;
}

.top-card{
    padding:20px 26px 16px;
    background:#FCFBFD;
    border-bottom:1px solid #ECE7F1;
}

.top-row{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:14px;
}

.badge{
    min-width:205px;
    text-align:center;
    color:#fff;
    font-size:15px;
    font-weight:700;
    padding:10px 16px;
    border-radius:10px;
}

.badge.processing{
    background:#E5C06A;
    color:#5C4400;
}

.badge.done{
    background:#71BC93;
}

.badge.rejected{
    background:#E6A9A9;
    color:#7A1F1F;
}

.top-title{
    color:#2E1D45;
    font-size:17px;
    font-weight:700;
    text-align:right;
}

.request-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:16px;
    align-items:start;
}

.request-item{
    text-align:right;
}

.request-label{
    color:#8AAFC0;
    font-size:13px;
    font-weight:700;
    margin-bottom:4px;
}

.request-value{
    color:#444;
    font-size:14px;
    line-height:1.8;
}

.section-box{
    padding:20px 26px;
    border-bottom:1px solid #ECE7F1;
}

.section-box:last-child{
    border-bottom:none;
}

.section-title{
    color:#2E1D45;
    font-size:18px;
    font-weight:700;
    margin-bottom:10px;
}

.section-note{
    color:#7A7A7A;
    font-size:13px;
    margin-top:-4px;
    margin-bottom:12px;
}

.result-box{
    flex:1;
    background:#EEF6F7;
    border:1px solid #D6E6E8;
    border-radius:12px;
    padding:16px 18px;
    line-height:2;
    color:#555;
    font-size:14px;
}

.result-box.processing{
    background:#FFF8E8;
    border:1px solid #F0D89A;
}

.result-box.rejected{
    background:#FFF1F1;
    border:1px solid #E8C1C1;
}

.result-box.done{
    background:#EEF6F7;
    border:1px solid #D6E6E8;
}

.result-row{
    display:flex;
    align-items:center;
    gap:18px;
    margin-top:14px;
    flex-direction:row-reverse;
}

.file-action-side{
    flex-shrink:0;
}

.file-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:190px;
    height:68px;
    text-align:center;
    background:#3E2454;
    color:#fff;
    border:none;
    border-radius:12px;
    padding:12px 18px;
    font-size:15px;
    font-weight:700;
    text-decoration:none;
    font-family:'Noto Kufi Arabic', sans-serif;
    transition:0.3s;
}

.file-btn:hover{
    background:#274E99;
}

.rating-form{
    display:flex;
    flex-direction:column;
    gap:14px;
}

.rating-textarea{
    width:100%;
    min-height:110px;
    resize:none;
    border:1px solid #D7E5E8;
    border-radius:12px;
    background:#F4FBFC;
    padding:14px;
    font-size:14px;
    outline:none;
    color:#444;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.rating-textarea::placeholder{
    color:#95AAB0;
}

.rating-btn-wrap{
    display:flex;
    justify-content:flex-end;
}

.rating-btn{
    min-width:170px;
    text-align:center;
    background:#4F9FB8;
    color:#fff;
    border:none;
    border-radius:12px;
    padding:12px 18px;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
    font-family:'Noto Kufi Arabic', sans-serif;
    transition:0.3s;
}

.rating-btn:hover{
    background:#428AA0;
}

.rating-view{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:18px;
    margin-top:10px;
}

.rating-comment-box{
    flex:1;
    background:#FFFFFF;
    border:1px solid #E5E7EB;
    border-radius:12px;
    padding:18px 20px;
    text-align:right;
    color:#6B7280;
    font-size:15px;
    line-height:2;
}

.rating-success-box{
    flex-shrink:0;
    background:#EAFBF1;
    border:1px solid #8BE0A8;
    border-radius:10px;
    padding:16px 18px;
    min-width:205px;
    text-align:center;
    color:#31B66A;
    font-size:15px;
    font-weight:700;
}

.msg{
    max-width:930px;
    margin:0 auto 14px;
    padding:12px;
    border-radius:8px;
    text-align:center;
    font-size:14px;
}

.msg.success{
    background:#EEF8F0;
    color:#1E6B35;
    border:1px solid #B9DFC2;
}

.msg.error{
    background:#FFF1F1;
    color:#B42318;
    border:1px solid #E8B4B4;
}

.back-btn{
    width:50px;
    height:50px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.back-wrap{
    display:flex;
    justify-content:flex-end;
    max-width:930px;
    margin:0 auto 10px;
}

.back-icon{
    width:46px;
    height:46px;
    object-fit:contain;
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
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php" class="active">طلبات إصدار القبول</a></li>
                <li><a href="Ben19_Consultations.php">الاستشارات</a></li>
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
                <div class="page-title">طلبات إصدار القبول</div>
                <div class="page-description">صفحة عرض تفاصيل طلب إصدار القبول</div>
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
                <a href="Ben16_AdmissionList.php" class="back-btn">
                    <img src="سهم تراجع.svg" class="back-icon" alt="رجوع">
                </a>
            </div>

            <?php if (!empty($msg)) { ?>
                <div class="msg <?php echo $type; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php } ?>

            <div class="content-box">

                <div class="top-card">
                    <div class="top-row">
                        <div class="top-title">تفاصيل طلب إصدار القبول</div>

                        <div class="badge <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </div>
                    </div>

                    <div class="request-grid">
                        <div class="request-item">
                            <div class="request-label">رقم الطلب:</div>
                            <div class="request-value"><?php echo $requestCode; ?></div>
                        </div>

                        <div class="request-item">
                            <div class="request-label">التخصص:</div>
                            <div class="request-value"><?php echo htmlspecialchars($request['major_name']); ?></div>
                        </div>

                        <div class="request-item">
                            <div class="request-label">الجامعة:</div>
                            <div class="request-value"><?php echo htmlspecialchars($request['univ_name']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="section-box">
                    <div class="section-title">نتيجة القبول</div>
                    <div class="section-note">ملاحظات المكتب</div>

                    <div class="result-row">
                        <?php if ($status == "أصدرت" && !empty($request['result'])) { ?>
                            <div class="file-action-side">
                                <a href="<?php echo htmlspecialchars($request['result']); ?>" target="_blank" class="file-btn">
                                    تحميل النتيجة
                                </a>
                            </div>
                        <?php } ?>

                        <div class="result-box <?php echo $statusClass; ?>">
                            <?php
                            if (!empty(trim($request['result_notes']))) {
                                echo nl2br(htmlspecialchars($request['result_notes']));
                            } else {
                                echo $statusMessage;
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="section-box">
                    <div class="section-title">تقييم المكتب</div>

                    <?php if ($hasRating) { ?>
                        <div class="rating-view">
                            <div class="rating-comment-box">
                                <?php echo nl2br(htmlspecialchars($lastComment)); ?>
                            </div>

                            <div class="rating-success-box">
                                تم استلام تقييمك بنجاح
                            </div>
                        </div>
                    <?php } else { ?>
                        <form method="POST" class="rating-form">
                            <textarea name="comment_text" class="rating-textarea" placeholder="اكتب تقييمك لخدمة المكتب هنا"></textarea>

                            <div class="rating-btn-wrap">
                                <button type="submit" name="send_rating" class="rating-btn">إرسال التقييم</button>
                            </div>
                        </form>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>