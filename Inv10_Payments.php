<?php
session_start();
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}
$con = mysqli_connect("localhost", "root", "", "noreen");
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}
mysqli_set_charset($con, "utf8mb4");
$inv_id = $_SESSION['inv_id'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'active';

/* المستفيدون النشطون */
$active_sql = "SELECT
                 scholarship_requests.request_id,
                 beneficiary.f_name,
                 beneficiary.l_name,
                 scholarship_opps.sch_name,
                 e_contract.ctr_status
               FROM scholarship_requests
               INNER JOIN scholarship_opps
                 ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
               INNER JOIN beneficiary
                 ON scholarship_requests.bnf_id = beneficiary.bnf_id
               INNER JOIN e_contract
                 ON scholarship_requests.request_id = e_contract.request_id
               WHERE scholarship_opps.inv_id = ?
                 AND scholarship_requests.request_status = 'مقبول'
               ORDER BY scholarship_requests.request_id DESC";
$active_stmt = mysqli_prepare($con, $active_sql);
mysqli_stmt_bind_param($active_stmt, "i", $inv_id);
mysqli_stmt_execute($active_stmt);
$active_result = mysqli_stmt_get_result($active_stmt);
$active_rows = [];
while ($row = mysqli_fetch_assoc($active_result)) {
    $active_rows[] = $row;
}

/* المستفيدون السابقون */
$ended_sql = "SELECT
                scholarship_requests.request_id,
                beneficiary.f_name,
                beneficiary.l_name,
                scholarship_opps.sch_name,
                e_contract.ctr_status
              FROM scholarship_requests
              INNER JOIN scholarship_opps
                ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
              INNER JOIN beneficiary
                ON scholarship_requests.bnf_id = beneficiary.bnf_id
              INNER JOIN e_contract
                ON scholarship_requests.request_id = e_contract.request_id
              WHERE scholarship_opps.inv_id = ?
                AND scholarship_requests.request_status IN ('مقبول','منتهي')
                AND e_contract.ctr_status = 'منتهي'
              ORDER BY scholarship_requests.request_id DESC";
$ended_stmt = mysqli_prepare($con, $ended_sql);
mysqli_stmt_bind_param($ended_stmt, "i", $inv_id);
mysqli_stmt_execute($ended_stmt);
$ended_result = mysqli_stmt_get_result($ended_stmt);
$ended_rows = [];
while ($row = mysqli_fetch_assoc($ended_result)) {
    $ended_rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>المدفوعات</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
.track-wrap{padding:30px;}
.track-tabs{max-width:1200px;margin:auto;display:grid;grid-template-columns:1fr 1fr;border:1px solid #ddd;border-radius:10px;overflow:hidden;margin-bottom:20px}
.track-tab{height:50px;display:flex;align-items:center;justify-content:center;font-weight:700;text-decoration:none;color:#3E2454;background:#fff;border-left:1px solid #ddd}
.track-tab.active{background:#F4EFF8}
.track-panel{max-width:1200px;margin:auto}
.track-card,.old-request-card{background:#fff;border-radius:12px;border:1px solid #ddd;padding:20px;margin-bottom:15px}
.track-top,.old-request-top{display:flex;justify-content:space-between;align-items:center}
.track-title{font-weight:700;margin-bottom:10px}
.info-line{color:#666;font-size:14px;margin-bottom:5px}
.status-box{min-width:150px;height:40px;border-radius:20px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
.st-active{background:#63B68B}
.st-ended{background:#8D8D8D}
.st-cancelled{background:#7A7A7A}
.track-actions{margin-top:15px;text-align:center}
.track-btn{padding:8px 20px;border-radius:6px;text-decoration:none;font-weight:700}
.btn-main{background:#5A2D74;color:#fff}
.btn-disabled{background:#aaa;color:#fff;pointer-events:none}
.empty-box{text-align:center;padding:30px;background:#fff;border-radius:10px;border:1px solid #ddd}
</style>
</head>

<body>

<div class="layout">

<aside class="sidebar">
<div class="sidebar-top">
<div class="sidebar-logo"><img src="شعار نورين.png"></div>
<ul class="sidebar-menu">
<li><a href="Inv00_MainPage.php">الرئيسية</a></li>
<li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
<li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
<li><a href="Inv10_Payments.php" class="active">المدفوعات</a></li>
</ul>

<div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
</div>
</aside>

<div class="main-content">

<header class="header">
<div class="page-heading">
<div class="page-title">المدفوعات</div>
<div class="page-description">إدارة المستفيدين</div>
</div>
  <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
                    <div class="dropdown-menu">
                        <a href="Inv02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>

</header>

<div class="track-wrap">

<div class="track-tabs">
<a href="?tab=active" class="track-tab <?php echo $tab=='active'?'active':''; ?>">المستفيدين النشطين</a>
<a href="?tab=others" class="track-tab <?php echo $tab=='others'?'active':''; ?>">المستفيدين السابقين</a>
</div>

<div class="track-panel">

<?php if($tab=="others"){ ?>

<?php if(count($ended_rows)>0){ ?>
<?php foreach($ended_rows as $row){ ?>

<div class="old-request-card">
<div class="old-request-top">

<div>
<div class="track-title"><?php echo $row['sch_name']; ?></div>
<div class="info-line"><?php echo $row['f_name']." ".$row['l_name']; ?></div>
</div>

<div class="status-box st-ended">منتهية</div>

</div>
</div>

<?php } ?>
<?php } else { ?>
<div class="empty-box">لا يوجد مستفيدين</div>
<?php } ?>

<?php } else { ?>

<?php if(count($active_rows)>0){ ?>
<?php foreach($active_rows as $row){ ?>

<div class="track-card">

<div class="track-top">

<div>
<div class="track-title"><?php echo $row['sch_name']; ?></div>
<div class="info-line"><?php echo $row['f_name']." ".$row['l_name']; ?></div>
</div>

<?php if($row['ctr_status']=="ملغي"){ ?>
<div class="status-box st-cancelled">ملغية</div>

<?php } elseif($row['ctr_status']=="منتهي"){ ?>
<div class="status-box st-ended">منتهية</div>

<?php } else { ?>
<div class="status-box st-active">نشط</div>
<?php } ?>

</div>

<div class="track-actions">

<?php if($row['ctr_status']=="نشط"){ ?>
<a href="Inv11_PaymentDetails.php?request_id=<?php echo $row['request_id']; ?>" class="track-btn btn-main">عرض التفاصيل</a>
<?php } else { ?>
<a href="#" class="track-btn btn-disabled">غير متاح</a>
<?php } ?>

</div>

</div>

<?php } ?>
<?php } else { ?>
<div class="empty-box">لا يوجد مستفيدين</div>
<?php } ?>

<?php } ?>

</div>
</div>

</div>
</div>

</body>
</html>