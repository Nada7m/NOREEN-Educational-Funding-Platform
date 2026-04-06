<?php
session_start();

$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

$bnf_id = (int) $_SESSION['bnf_id'];
$request_id = isset($_GET['request_id']) ? (int) $_GET['request_id'] : 0;

$successMsg = "";
if (isset($_GET['success']) && $_GET['success'] == "1") {
    $successMsg = "تم تقديم طلبك بنجاح";
}

$request = null;

/* إذا جاء request_id من صفحة التقديم نعرضه مباشرة */
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
            c.contract_id
        FROM scholarship_requests sr
        INNER JOIN scholarship_opps s
            ON sr.scholarship_id = s.scholarship_id
        LEFT JOIN e_contract c
            ON sr.request_id = c.request_id
        WHERE sr.request_id = ? AND sr.bnf_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $bnf_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $request = $result->fetch_assoc();
    }
}

/* إذا لم يوجد request_id أو لم نجد الطلب، نعرض آخر طلب */
if (!$request) {
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
            c.contract_id
        FROM scholarship_requests sr
        INNER JOIN scholarship_opps s
            ON sr.scholarship_id = s.scholarship_id
        LEFT JOIN e_contract c
            ON sr.request_id = c.request_id
        WHERE sr.bnf_id = ?
        ORDER BY sr.request_id DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bnf_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $request = $result->fetch_assoc();
    }
}

/* تحديد حالة الأزرار */
$contractEnabled = false;
$reportsEnabled = false;
$statusText = "";
$statusClass = "";

if ($request) {
    $dbStatus = $request['request_status'];

    if ($dbStatus === "مقبول") {
        $statusText = "مقبول";
        $statusClass = "st-accepted";

        if (!empty($request['contract_id'])) {
            $contractEnabled = true;
        }

        if ($request['approval_status'] === 'تمت الموافقة') {
            $reportsEnabled = true;
        }

    } elseif ($dbStatus === "مرفوض") {
        $statusText = "مرفوض";
        $statusClass = "st-rejected";
    } else {
        $statusText = "في انتظار المراجعة";
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
.track-wrap{ padding:30px 20px; }

.track-card{ background:#FFFFFF; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); padding:28px 22px; max-width:900px; margin:auto; border:1px solid #ececec; }

.track-top{ display:flex; justify-content:space-between; align-items:flex-start; gap:30px; flex-wrap:wrap; }

.status-box{ min-width:240px; max-width:240px; height:40px; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#FFFFFF; font-size:16px; font-weight:700; }

.st-pending{ background:#E6BC6A; }

.st-accepted{ background:#69B38A; }

.st-rejected{ background:#C96B6B; }

.track-info{ flex:1; min-width:280px; }

.track-title{ font-size:18px; font-weight:700; color:#222222; margin-bottom:14px; }

.info-line{ margin-bottom:8px; font-size:15px; color:#777777; line-height:1.9; }

.info-line b{ color:#8EB4C2; font-size:16px; margin-left:6px; }

.track-divider{ border:none; border-top:1px solid #dddddd; margin:24px 0 18px; }

.track-actions{ display:flex; gap:14px; flex-wrap:wrap; }

.track-btn{ min-width:180px; height:40px; border:none; border-radius:4px; font-family:"Noto Kufi Arabic", sans-serif; font-size:15px; font-weight:700; cursor:pointer; text-decoration:none; display:flex; align-items:center; justify-content:center; transition:0.2s; }

.btn-disabled{ background:#A9A9A9; color:#FFFFFF; pointer-events:none; cursor:default; }

.btn-contact{ background:#C9ADD8; color:#FFFFFF; }

.btn-contract{ background:#5A2D74; color:#FFFFFF; }

.btn-reports{ background:#70A0AF; color:#FFFFFF; }

.no-request{ max-width:900px; margin:auto; background:#FFFFFF; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); padding:30px; text-align:center; color:#666666; font-size:16px; border:1px solid #ececec; }

.msg.success{ max-width:900px; margin:0 auto 15px; background:#edf8ee; color:#256b2a; border:1px solid #b7dfba; padding:12px; border-radius:6px; text-align:center; font-family:'Noto Kufi Arabic', sans-serif; }
  </style>
</head>
<body>
  <div class="layout">
    <aside class="sidebar">
      <div class="sidebar-top">
        <div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
        <ul class="sidebar-menu">
          <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
          <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
          <li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
          <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
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
          <div class="page-title">متابعة المنح</div>
          <div class="page-description">صفحة متابعة طلبات المنح الحالية</div>
        </div>

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

      <div class="page">
        <div class="track-wrap">

          <?php if (!empty($successMsg)) { ?>
            <div class="msg success"><?php echo $successMsg; ?></div>
          <?php } ?>

          <?php if ($request) { ?>
            <div class="track-card">
              <div class="track-top">
                <div class="track-info">
                  <div class="track-title">تفاصيل المنحة الحالية</div>
                  <div class="info-line"><b>رقم الطلب:</b> <?php echo $request['request_id']; ?></div>
                  <div class="info-line"><b>المنحة:</b> <?php echo $request['sch_name']; ?></div>
                  <div class="info-line"><b>التخصص:</b> <?php echo $request['major_name']; ?></div>
                  <div class="info-line"><b>الجامعة:</b> <?php echo $request['univ_name']; ?></div>
                </div>

                <div class="status-box <?php echo $statusClass; ?>">
                  <?php echo $statusText; ?>
                </div>
              </div>

              <hr class="track-divider">

              <div class="track-actions">
                <a href="Ben10_InvestorContact.php?inv_id=<?php echo $request['inv_id']; ?>" class="track-btn btn-contact">التواصل</a>

                <?php if ($reportsEnabled) { ?>
                  <a href="Ben11_ReportsPayments.php?request_id=<?php echo $request['request_id']; ?>" class="track-btn btn-reports">التقارير والدفعات</a>
                <?php } else { ?>
                  <a href="#" class="track-btn btn-disabled">التقارير والدفعات</a>
                <?php } ?>

                <?php if ($contractEnabled) { ?>
                  <a href="Ben12_EContract.php?request_id=<?php echo $request['request_id']; ?>" class="track-btn btn-contract">العقد الإلكتروني</a>
                <?php } else { ?>
                  <a href="#" class="track-btn btn-disabled">العقد الإلكتروني</a>
                <?php } ?>
              </div>
            </div>
          <?php } else { ?>
            <div class="no-request">لا يوجد طلب منحة حالي لعرضه.</div>
          <?php } ?>

        </div>
      </div>
    </div>
  </div>
</body>
</html>