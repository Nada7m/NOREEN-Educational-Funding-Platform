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

$sqlTotal = "SELECT COUNT(*) AS total_requests
             FROM admission_request
             WHERE office_id = $office_id";
$resTotal = mysqli_query($con, $sqlTotal);
$rowTotal = mysqli_fetch_assoc($resTotal);
$totalRequests = $rowTotal['total_requests'];

$sqlProcessing = "SELECT COUNT(*) AS processing_requests
                  FROM admission_request
                  WHERE office_id = $office_id
                  AND (Result_status = 'قيد المعالجة' OR Result_status = '' OR Result_status IS NULL)";
$resProcessing = mysqli_query($con, $sqlProcessing);
$rowProcessing = mysqli_fetch_assoc($resProcessing);
$processingRequests = $rowProcessing['processing_requests'];

$sqlFinished = "SELECT COUNT(*) AS finished_requests
                FROM admission_request
                WHERE office_id = $office_id
                AND Result_status IS NOT NULL
                AND Result_status <> ''
                AND Result_status <> 'قيد المعالجة'";
$resFinished = mysqli_query($con, $sqlFinished);
$rowFinished = mysqli_fetch_assoc($resFinished);
$finishedRequests = $rowFinished['finished_requests'];

$sqlRequests = "SELECT 
                    ar.request_id,
                    ar.request_status,
                    ar.Result_status,
                    ar.Submit_date,
                    b.f_name,
                    b.l_name
                FROM admission_request ar
                INNER JOIN beneficiary b ON ar.bnf_id = b.bnf_id
                WHERE ar.office_id = $office_id
                ORDER BY ar.request_id DESC";
$resRequests = mysqli_query($con, $sqlRequests);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إدارة طلبات القبول</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
.dashboard-section{padding:30px;}
.stats-boxes{display:flex; justify-content:center; gap:25px; flex-wrap:wrap; margin-bottom:30px;}
.stat-card{width:190px; background:#fff; border-radius:12px; padding:28px 20px; text-align:center; box-shadow:0 4px 14px rgba(0,0,0,0.08);}
.stat-number{font-size:22px; font-weight:700; margin-bottom:8px; font-family:'Noto Kufi Arabic', sans-serif;}
.stat-total{color:#3E2454;}
.stat-processing{color:#E0B25C;}
.stat-finished{color:#63B68B;}
.stat-label{font-size:15px; font-weight:600; color:#4b3d5c; font-family:'Noto Kufi Arabic', sans-serif;}
.table-box{background:#fff; border-radius:12px; overflow:hidden; max-width:1100px; margin:0 auto;}
.requests-table{width:100%; border-collapse:collapse; text-align:center; font-family:'Noto Kufi Arabic', sans-serif;}
.requests-table tr:first-child th{background:#f8f8f8; color:#3E2454; font-size:15px; font-weight:700; padding:14px 10px; border-bottom:1px solid #ddd;}
.requests-table td{padding:14px 10px; border-bottom:1px solid #eee; font-size:14px; color:#333;}
.requests-table tr:last-child td{border-bottom:none;}
.status-badge{display:inline-block; min-width:120px; padding:7px 14px; border-radius:20px; color:#fff; font-size:13px; font-weight:700; font-family:'Noto Kufi Arabic', sans-serif;}
.status-processing{background:#E9BE66;}
.status-finished{background:#63B68B;}
.status-rejected{background:#D96C6C;}
.details-btn{display:inline-block; padding:8px 18px; border:1px solid #999; border-radius:10px; background:#fff; color:#3E2454; text-decoration:none; font-size:13px; font-weight:600; font-family:'Noto Kufi Arabic', sans-serif; transition:0.3s;}
.details-btn:hover{background:#f4f0f7;}
.empty-msg{text-align:center; padding:30px; color:#777; font-size:15px; font-family:'Noto Kufi Arabic', sans-serif;}
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
                <li><a href="Con0_BeneficiaryRatings.php">تقييمات المستفيدين</a></li>
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
                <div class="page-description">صفحة متابعة طلبات القبول المقدمة</div>
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

        <div class="dashboard-section">

            <div class="stats-boxes">
                <div class="stat-card">
                    <div class="stat-number stat-total"><?php echo $totalRequests; ?></div>
                    <div class="stat-label">إجمالي الطلبات</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number stat-processing"><?php echo $processingRequests; ?></div>
                    <div class="stat-label">قيد المعالجة</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number stat-finished"><?php echo $finishedRequests; ?></div>
                    <div class="stat-label">تم إصدار النتيجة</div>
                </div>
            </div>

            <div class="table-box">
                <table class="requests-table">
                    <tr>
                        <th>رقم الطلب</th>
                        <th>اسم المستفيد</th>
                        <th>تاريخ الطلب</th>
                        <th>حالة الطلب</th>
                        <th>حالة النتيجة</th>
                        <th>الإجراءات</th>
                    </tr>

                    <?php if ($resRequests && mysqli_num_rows($resRequests) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($resRequests)) { ?>

                            <?php
                            $fullName = $row['f_name'] . " " . $row['l_name'];

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
                                $requestStatusClass = "status-finished";
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
                                $resultStatusClass = "status-finished";
                            }
                            ?>

                            <tr>
                                <td><?php echo $row['request_id']; ?></td>
                                <td><?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['Submit_date'], ENT_QUOTES, 'UTF-8'); ?></td>

                                <td>
                                    <div class="status-badge <?php echo $requestStatusClass; ?>">
                                        <?php echo htmlspecialchars($requestStatusText, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="status-badge <?php echo $resultStatusClass; ?>">
                                        <?php echo htmlspecialchars($resultStatusText, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>

                                <td>
                                    <a href="Con05_AdmissiontDetails.php?request_id=<?php echo $row['request_id']; ?>" class="details-btn">
                                        عرض البيانات
                                    </a>
                                </td>
                            </tr>

                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="empty-msg">لا توجد طلبات قبول حالياً</td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>