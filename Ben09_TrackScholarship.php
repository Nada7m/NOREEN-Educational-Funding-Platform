<?php
session_start();

/** التحقق من تسجيل دخول المستفيد قبل عرض الصفحة **/
if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* دعم اللغة العربية */
$conn->set_charset("utf8mb4");

/* رقم المستفيد الحالي */
$bnf_id = (int) $_SESSION['bnf_id'];

/* رقم الطلب إذا تم إرساله في الرابط */
$request_id = isset($_GET['request_id']) ? (int) $_GET['request_id'] : 0;

/* تحديد التبويب الحالي */
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'current';

/* تحديث الطلبات الملغية حسب حالة العقد */
$sqlCancel = "
    UPDATE scholarship_requests sr
    INNER JOIN e_contract c
        ON sr.request_id = c.request_id
    SET sr.request_status = 'ملغي'
    WHERE sr.bnf_id = ?
    AND c.ctr_status = 'ملغي'
";

$stmtCancel = $conn->prepare($sqlCancel);
$stmtCancel->bind_param("i", $bnf_id);
$stmtCancel->execute();
$stmtCancel->close();

/* تحديث المنح المنتهية بعد دفع كل الدفعات */
$sqlEnd = "
    UPDATE scholarship_requests sr
    INNER JOIN e_contract c
        ON sr.request_id = c.request_id
    SET sr.request_status = 'منتهي', c.ctr_status = 'منتهي'
    WHERE sr.bnf_id = ?
    AND sr.request_status = 'مقبول'
    AND c.ctr_status = 'نشط'
    AND (
        SELECT COUNT(*)
        FROM payments p
        WHERE p.contract_id = c.contract_id
    ) > 0
    AND (
        SELECT COUNT(*)
        FROM payments p
        WHERE p.contract_id = c.contract_id
        AND p.payment_status = 'تم الدفع'
    ) = (
        SELECT COUNT(*)
        FROM payments p
        WHERE p.contract_id = c.contract_id
    )
";

$stmtEnd = $conn->prepare($sqlEnd);
$stmtEnd->bind_param("i", $bnf_id);
$stmtEnd->execute();
$stmtEnd->close();

/* رسالة نجاح بعد التقديم */
$successMsg = "";

if (isset($_GET['success']) && $_GET['success'] == "1") {
    $successMsg = "تم تقديم طلبك بنجاح";
}

/* متغيرات الطلبات */
$current_request = null;
$previous_requests = [];

/* جلب طلب محدد إذا جاء رقم الطلب من الرابط */
if ($request_id > 0) {

    $sql = "
        SELECT
            sr.request_id,
            sr.scholarship_id,
            sr.bnf_id,
            sr.request_status,
            sr.major_name,
            sr.univ_name,
            s.inv_id,
            s.sch_name,
            c.approval_status,
            c.contract_id,
            c.ctr_status
        FROM scholarship_requests sr
        INNER JOIN scholarship_opps s
            ON sr.scholarship_id = s.scholarship_id
        LEFT JOIN e_contract c
            ON sr.request_id = c.request_id
        WHERE sr.request_id = ?
        AND sr.bnf_id = ?
        AND sr.request_status IN ('تحت المراجعة', 'مقبول')
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $bnf_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $current_request = $result->fetch_assoc();
    }

    $stmt->close();
}

/* جلب آخر طلب نشط إذا لم يتم العثور على طلب محدد */
if (!$current_request) {

    $sql = "
        SELECT
            sr.request_id,
            sr.scholarship_id,
            sr.bnf_id,
            sr.request_status,
            sr.major_name,
            sr.univ_name,
            s.inv_id,
            s.sch_name,
            c.approval_status,
            c.contract_id,
            c.ctr_status
        FROM scholarship_requests sr
        INNER JOIN scholarship_opps s
            ON sr.scholarship_id = s.scholarship_id
        LEFT JOIN e_contract c
            ON sr.request_id = c.request_id
        WHERE sr.bnf_id = ?
        AND sr.request_status IN ('تحت المراجعة', 'مقبول')
        ORDER BY sr.request_id DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bnf_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $current_request = $result->fetch_assoc();
    }

    $stmt->close();
}

/* جلب سجل الطلبات السابقة */
$sql_previous = "
    SELECT
        sr.request_id,
        sr.scholarship_id,
        sr.request_status,
        sr.major_name,
        sr.univ_name,
        s.inv_id,
        s.sch_name
    FROM scholarship_requests sr
    INNER JOIN scholarship_opps s
        ON sr.scholarship_id = s.scholarship_id
    WHERE sr.bnf_id = ?
    AND sr.request_status IN ('مرفوض', 'منتهي', 'ملغي')
    ORDER BY sr.request_id DESC
";

$stmt_previous = $conn->prepare($sql_previous);
$stmt_previous->bind_param("i", $bnf_id);
$stmt_previous->execute();
$result_previous = $stmt_previous->get_result();

while ($row = $result_previous->fetch_assoc()) {
    $previous_requests[] = $row;
}

$stmt_previous->close();

/* حالة الأزرار */
$contactEnabled = false;
$contractEnabled = false;
$reportsEnabled = false;
$statusText = "";
$statusClass = "";

/** تفعيل الأزرار حسب حالة الطلب **/
if ($current_request) {
    $dbStatus = $current_request['request_status'];

    if ($dbStatus === "مقبول") {
        $statusText = "مقبول";
        $statusClass = "st-accepted";
        $contactEnabled = true;

        if (!empty($current_request['contract_id'])) {
            $contractEnabled = true;
        }

        if ($current_request['approval_status'] === 'تمت الموافقة') {
            $reportsEnabled = true;
        }

    } elseif ($dbStatus === "تحت المراجعة") {
        $statusText = "تحت المراجعة";
        $statusClass = "st-pending";

    } elseif ($dbStatus === "مرفوض") {
        $statusText = "مرفوض";
        $statusClass = "st-rejected";

    } elseif ($dbStatus === "منتهي") {
        $statusText = "منتهي";
        $statusClass = "st-ended";

    } elseif ($dbStatus === "ملغي") {
        $statusText = "ملغي";
        $statusClass = "st-cancelled";

    } else {
        $statusText = $dbStatus;
        $statusClass = "st-pending";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<title>متابعة المنح</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
/* الحاوية العامة */
.track-wrap{ padding:30px; }

/* رسالة النجاح */
.msg.success{ width:100%; max-width:1250px; margin:0 auto 15px; background:#EDF8EE; color:#256B2A; border:1px solid #B7DFBA; padding:12px; border-radius:6px; text-align:center; font-family:'Noto Kufi Arabic', sans-serif; }

/* التبويبات */
.track-tabs{ width:100%; max-width:1250px; margin:0 auto 22px; display:grid; grid-template-columns:1fr 1fr; border:1px solid #D9D9D9; border-radius:12px; overflow:hidden; }

/* زر التبويب */
.track-tab{ height:50px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:18px; font-weight:700; color:#3E2454; border-left:1px solid #D9D9D9; background-color:#FFFFFF; }

/* التبويب النشط */
.track-tab.active{ background:#F8F5FB; }

/* لوحة المحتوى */
.track-panel{ width:100%; max-width:1250px; margin:auto; border:none; }

/* بطاقة الطلب النشط */
.track-card{ width:100%; background:#FFFFFF; border-radius:12px; border:0.5px solid #C5C3C3; padding:26px 32px; }

/* الجزء العلوي من البطاقة */
.track-top{ display:flex; justify-content:space-between; align-items:flex-start; gap:30px; flex-wrap:wrap; }

/* صندوق الحالة */
.status-box{ min-width:220px; max-width:220px; height:42px; border-radius:20px; display:flex; align-items:center; justify-content:center; color:#FFFFFF; font-size:15px; font-weight:700; }

/* حالة تحت المراجعة */
.st-pending{ background:#E9BE66; }

/* حالة مقبول */
.st-accepted{ background:#63B68B; }

/* حالة مرفوض */
.st-rejected{ background:#D96C6C; }

/* حالة منتهي */
.st-ended{ background:#8D8D8D; }

/* حالة ملغي */
.st-cancelled{ background:#7A7A7A; }

/* بيانات الطلب */
.track-info{ flex:1; min-width:280px; }

/* عنوان البطاقة */
.track-title{ font-size:18px; font-weight:700; color:#222222; margin-bottom:14px; }

/* سطر البيانات */
.info-line{ margin-bottom:8px; font-size:15px; color:#777777; line-height:1.9; }

/* عنوان سطر البيانات */
.info-line b{ color:#8EB4C2; font-size:16px; margin-left:6px; }

/* الخط الفاصل */
.track-divider{ border:none; border-top:1px solid #DDDDDD; margin:24px 0 18px; }

/* أزرار الإجراءات */
.track-actions{ display:flex; gap:14px; flex-wrap:wrap; justify-content:center; }

/* الزر العام */
.track-btn{ min-width:180px; height:40px; border:none; border-radius:6px; font-family:'Noto Kufi Arabic', sans-serif; font-size:15px; font-weight:700; cursor:pointer; text-decoration:none; display:flex; align-items:center; justify-content:center; transition:0.2s; }

/* الزر غير المتاح */
.btn-disabled{ background:#A9A9A9; color:#FFFFFF; pointer-events:none; cursor:default; }

/* زر التواصل */
.btn-contact{ background:#C9ADD8; color:#FFFFFF; }

/* زر العقد */
.btn-contract{ background:#5A2D74; color:#FFFFFF; }

/* زر التقارير */
.btn-reports{ background:#70A0AF; color:#FFFFFF; }

/* صندوق عدم وجود بيانات */
.empty-box{ width:100%; background:#FFFFFF; border-radius:12px; padding:30px; text-align:center; color:#666666; font-size:16px; border:0.5px solid #C5C3C3; }

/* قائمة الطلبات السابقة */
.old-requests-list{ display:grid; gap:14px; }

/* بطاقة طلب سابق */
.old-request-card{ width:100%; background:#FFFFFF; border-radius:12px; padding:22px 26px; border:0.5px solid #C5C3C3; }

/* أعلى بطاقة الطلب السابق */
.old-request-top{ display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap; }

/* بيانات الطلب السابق */
.old-request-info{ flex:1; min-width:250px; }

/* عنوان الطلب السابق */
.old-request-title{ font-size:16px; font-weight:700; color:#222222; margin-bottom:10px; }

/* حالة الطلب السابق */
.old-request-status{ min-width:170px; height:36px; border-radius:20px; display:flex; align-items:center; justify-content:center; color:#FFFFFF; font-size:14px; font-weight:700; }
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
                <li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
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
                <div class="page-title">متابعة المنح</div>
                <div class="page-description">صفحة عرض الطلب النشط وسجل الطلبات</div>
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

            <div class="track-wrap">

                <?php if (!empty($successMsg)) { ?>
                    <div class="msg success"><?php echo $successMsg; ?></div>
                <?php } ?>

                <div class="track-tabs">
                    <a href="Ben09_TrackScholarship.php?tab=current" class="track-tab <?php echo ($tab === 'current') ? 'active' : ''; ?>">الطلب النشط</a>
                    <a href="Ben09_TrackScholarship.php?tab=previous" class="track-tab <?php echo ($tab === 'previous') ? 'active' : ''; ?>">سجل الطلبات</a>
                </div>

                <div class="track-panel">

                    <?php if ($tab === 'previous') { ?>

                        <?php if (!empty($previous_requests)) { ?>

                            <div class="old-requests-list">

                                <?php foreach ($previous_requests as $old_request) { ?>

                                    <?php
                                    /* تحديد لون حالة الطلب السابق */
                                    $oldStatusText = $old_request['request_status'];

                                    if ($old_request['request_status'] === 'مرفوض') {
                                        $oldStatusClass = 'st-rejected';
                                    } elseif ($old_request['request_status'] === 'منتهي') {
                                        $oldStatusClass = 'st-ended';
                                    } else {
                                        $oldStatusClass = 'st-cancelled';
                                    }
                                    ?>

                                    <div class="old-request-card">

                                        <div class="old-request-top">

                                            <div class="old-request-info">
                                                <div class="old-request-title"><?php echo htmlspecialchars($old_request['sch_name']); ?></div>
                                                <div class="info-line"><b>رقم الطلب:</b> <?php echo htmlspecialchars($old_request['request_id']); ?></div>
                                                <div class="info-line"><b>التخصص:</b> <?php echo htmlspecialchars($old_request['major_name']); ?></div>
                                                <div class="info-line"><b>الجامعة:</b> <?php echo htmlspecialchars($old_request['univ_name']); ?></div>
                                            </div>

                                            <div class="old-request-status <?php echo $oldStatusClass; ?>">
                                                <?php echo htmlspecialchars($oldStatusText); ?>
                                            </div>

                                        </div>

                                    </div>

                                <?php } ?>

                            </div>

                        <?php } else { ?>

                            <div class="empty-box">لا يوجد سجل طلبات</div>

                        <?php } ?>

                    <?php } else { ?>

                        <?php if ($current_request) { ?>

                            <div class="track-card">

                                <div class="track-top">

                                    <div class="track-info">
                                        <div class="track-title">تفاصيل الطلب النشط</div>
                                        <div class="info-line"><b>رقم الطلب:</b> <?php echo htmlspecialchars($current_request['request_id']); ?></div>
                                        <div class="info-line"><b>المنحة:</b> <?php echo htmlspecialchars($current_request['sch_name']); ?></div>
                                        <div class="info-line"><b>التخصص:</b> <?php echo htmlspecialchars($current_request['major_name']); ?></div>
                                        <div class="info-line"><b>الجامعة:</b> <?php echo htmlspecialchars($current_request['univ_name']); ?></div>
                                    </div>

                                    <div class="status-box <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($statusText); ?>
                                    </div>

                                </div>

                                <hr class="track-divider">

                                <div class="track-actions">

                                    <?php if ($contactEnabled) { ?>
                                        <a href="Ben10_InvestorContact.php?inv_id=<?php echo htmlspecialchars($current_request['inv_id']); ?>" class="track-btn btn-contact">التواصل</a>
                                    <?php } else { ?>
                                        <a href="#" class="track-btn btn-disabled">التواصل</a>
                                    <?php } ?>

                                    <?php if ($reportsEnabled) { ?>
                                        <a href="Ben11_ReportsPayments.php?request_id=<?php echo htmlspecialchars($current_request['request_id']); ?>" class="track-btn btn-reports">التقارير والدفعات</a>
                                    <?php } else { ?>
                                        <a href="#" class="track-btn btn-disabled">التقارير والدفعات</a>
                                    <?php } ?>

                                    <?php if ($contractEnabled) { ?>
                                        <a href="Ben12_EContract.php?request_id=<?php echo htmlspecialchars($current_request['request_id']); ?>" class="track-btn btn-contract">العقد الإلكتروني</a>
                                    <?php } else { ?>
                                        <a href="#" class="track-btn btn-disabled">العقد الإلكتروني</a>
                                    <?php } ?>

                                </div>

                            </div>

                        <?php } else { ?>

                            <div class="empty-box">لا يوجد طلب نشط حاليًا</div>

                        <?php } ?>

                    <?php } ?>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>