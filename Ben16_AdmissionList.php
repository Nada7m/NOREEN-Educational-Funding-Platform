<?php
session_start();

/* التحقق من تسجيل دخول المستفيد */
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

/* جلب طلبات إصدار القبول الخاصة بالمستفيد */
$sql = "SELECT 
            ar.request_id,
            ar.Submit_date,
            ar.request_status,
            ar.result_notes,
            ar.Result_status,
            co.office_name
        FROM admission_request ar
        LEFT JOIN consulting_office co ON ar.office_id = co.office_id
        WHERE ar.bnf_id = $bnf_id
        ORDER BY ar.request_id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>طلبات إصدار القبول</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
.page{ padding:18px 30px 30px; }
.content-box{ width:100%; max-width:1100px; margin:20px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }
.requests-table-wrap{ width:100%; overflow-x:auto; }
.requests-table{ width:100%; border-collapse:collapse; min-width:850px; }
.requests-table th{ background:#F5F3F7; color:#3E2454; padding:14px 12px; font-size:15px; font-weight:700; text-align:center; border-bottom:1px solid #ddd; font-family:'Noto Kufi Arabic', sans-serif; }
.requests-table td{ background:#fff; padding:14px 12px; font-size:14px; color:#444; text-align:center; border-bottom:1px solid #eee; font-family:'Noto Kufi Arabic', sans-serif; vertical-align:middle; }
.requests-table tr:hover td{ background:#fcfbfd; }
.req-code{ color:#6f6f6f; font-weight:700; }
.status-box{ display:inline-block; min-width:110px; padding:8px 14px; border-radius:10px; font-size:13px; font-weight:700; }
.status-processing{ background:#F3D48A; color:#7A5A00; }
.status-done{ background:#8FD0A5; color:#185C31; }
.status-rejected{ background:#F2B6B6; color:#8A1F1F; }
.details-btn{ display:inline-block; padding:8px 16px; background:#fff; color:#3E2454; border:1.5px solid #B9B0C6; border-radius:10px; text-decoration:none; font-size:13px; font-weight:700; transition:0.3s; font-family:'Noto Kufi Arabic', sans-serif; }
.details-btn:hover{ background:#f6f2fa; }
.empty-box{ text-align:center; padding:50px 20px; color:#777; font-size:16px; font-family:'Noto Kufi Arabic', sans-serif; }
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
                <li><a href="Ben16_AdmissionRequests.php" class="active">طلبات إصدار القبول</a></li>
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
                <div class="page-description">صفحة عرض ومتابعة طلبات إصدار القبول</div>
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
            <div class="content-box">

                <?php if ($result && $result->num_rows > 0) { ?>
                    <div class="requests-table-wrap">
                        <table class="requests-table">

                            <tr>
                                <th>رقم الطلب</th>
                                <th>المكتب</th>
                                <th>تاريخ التقديم</th>
                                <th>حالة الطلب</th>
                                <th>حالة النتيجة</th>
                                <th>الإجراءات</th>
                            </tr>

                            <?php while ($row = $result->fetch_assoc()) { ?>

                                <?php
                                /* حالة الطلب */
                                $request_status = trim($row['request_status']);

                                if ($request_status == "" || $request_status == "في الانتظار") {
                                    $requestStatusText = "في الانتظار";
                                    $requestStatusClass = "status-processing";
                                } elseif ($request_status == "مرفوض") {
                                    $requestStatusText = "مرفوض";
                                    $requestStatusClass = "status-rejected";
                                } else {
                                    $requestStatusText = "مقبول";
                                    $requestStatusClass = "status-done";
                                }

                                /* حالة النتيجة */
                                $result_status = trim($row['Result_status']);

                                if ($result_status == "" || $result_status == "قيد المعالجة") {
                                    $resultStatusText = "قيد المعالجة";
                                    $resultStatusClass = "status-processing";
                                } elseif ($result_status == "مرفوض" || $result_status == "مرفوضة") {
                                    $resultStatusText = "مرفوض";
                                    $resultStatusClass = "status-rejected";
                                } else {
                                    $resultStatusText = $result_status;
                                    $resultStatusClass = "status-done";
                                }
                                ?>

                                <tr>
                                    <td class="req-code">UA<?php echo $row['request_id']; ?></td>

                                    <td>
                                        <?php echo $row['office_name']; ?>
                                    </td>

                                    <td>
                                        <?php echo $row['Submit_date']; ?>
                                    </td>

                                    <td>
                                        <div class="status-box <?php echo $requestStatusClass; ?>">
                                            <?php echo $requestStatusText; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="status-box <?php echo $resultStatusClass; ?>">
                                            <?php echo $resultStatusText; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <a href="Ben17_AdmissionDetails.php?id=<?php echo $row['request_id']; ?>" class="details-btn">
                                            عرض تفاصيل الطلب
                                        </a>
                                    </td>
                                </tr>

                            <?php } ?>

                        </table>
                    </div>

                <?php } else { ?>
                    <div class="empty-box">
                        لا توجد لديك طلبات إصدار قبول حتى الآن.
                    </div>
                <?php } ?>

            </div>
        </div>

    </div>
</div>

</body>
</html>