<?php
session_start();

/* التحقق من دخول الأدمن */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost","root","","noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con,"utf8mb4");

/* تنفيذ إنهاء العقد */
if(isset($_POST['end_contract'])){
    $contract_id = $_POST['contract_id'];
    $request_id  = $_POST['request_id'];

    mysqli_query($con,"UPDATE e_contract SET ctr_status='ملغي' WHERE contract_id='$contract_id'");
    mysqli_query($con,"UPDATE scholarship_requests SET request_status='منتهي' WHERE request_id='$request_id'");

    header("Location: Admin3_Contracts.php");
    exit();
}

/* جلب بيانات العقود */
$sql = "
SELECT 
    c.contract_id,
    c.ctr_status,
    c.request_id,
    i.inv_name,
    CONCAT(b.f_name,' ',b.l_name) AS beneficiary_name
FROM e_contract c
JOIN scholarship_requests r ON c.request_id = r.request_id
JOIN beneficiary b ON r.bnf_id = b.bnf_id
JOIN scholarship_opps s ON r.scholarship_id = s.scholarship_id
JOIN investor i ON s.inv_id = i.inv_id
ORDER BY c.contract_id DESC
";

$result = mysqli_query($con,$sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إدارة العقود</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS_AdminLayout.css?v=3">

<style>

/* الحاوية العامة */
.page-wrapper{ padding:40px; }


/* صندوق الجدول */
.table-box{ width:100%; max-width:1050px; margin:0 auto; background:#FFFFFF; border:1px solid #E6E0E6; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }


/* الجدول */
table{ width:100%; border-collapse:collapse; table-layout:fixed; background:#FFFFFF; }


/* صف العناوين */
.table-head th{ padding:15px 12px; background:#FAFAFA; border-bottom:1px solid #DDDDDD; font-size:15px; font-weight:700; color:#3E2454; text-align:center; }


/* خلايا الجدول */
table td{ padding:16px 12px; border-bottom:1px solid #EEEEEE; text-align:center; vertical-align:middle; font-size:14px; font-weight:500; color:#595959; background:#FFFFFF; }


/* حالة العقد */
.status{ display:inline-flex; align-items:center; justify-content:center; width:100px; height:42px; border-radius:12px; color:#FFFFFF; font-size:14px; font-weight:600; }


/* حالة العقد النشط */
.status-active{ background:#2E8B57; }


/* حالة العقد الملغي */
.status-cancel{ background:#C4474F; }


/* زر عام */
.btn{ width:100px; height:42px; border:none; border-radius:12px; cursor:pointer; font-size:14px; font-family:"Noto Kufi Arabic",sans-serif; font-weight:600; }


/* زر إنهاء العقد */
.btn-delete{ background:#A53A3A; color:#FFFFFF; }


/* نص منتهي */
.ended-text{ color:#999999; font-size:14px; font-weight:500; }


/* صف فاضي */
.empty-row td{ height:62px; background:#FFFFFF; border-bottom:1px solid #EEEEEE; }


/* نموذج الإجراء */
.action-form{ margin:0; display:flex; justify-content:center; }

</style>
</head>
<body>

<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-logo">
        <img src="شعار نورين بنفسجي.svg" alt="شعار نورين">
      </div>

      <ul class="sidebar-menu">
        <li><a href="Admin2_EntitiesApproval.php">اعتماد الجهات</a></li>
        <li><a href="Admin3_Contracts.php" class="active">إدارة العقود</a></li>
        <li><a href="Admin4_UsersManage.php">إدارة المستخدمين</a></li>
        <li><a href="Admin5_Complaints.php">الشكاوى والاستفسارات</a></li>
      </ul>
    </div>

    <div class="sidebar-bottom">
      <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">
          <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
          تسجيل الخروج
        </button>
      </form>
    </div>
  </aside>

  <div class="main-content">

    <header class="header">
      <div class="page-heading">
        <div class="page-title">إدارة العقود</div>
        <div class="page-description">عرض العقود الحالية ومتابعة حالتها وإجراءاتها</div>
      </div>

      <div class="header-left">
        <a href="Admin1_profile.php" class="profile-btn">لوحة التحكم</a>
      </div>
    </header>

    <div class="page">
      <div class="page-wrapper">

        <div class="table-box">
          <table>

            <tr class="table-head">
              <th>رقم الطلب</th>
              <th>رقم العقد</th>
              <th>اسم المستثمر</th>
              <th>اسم المستفيد</th>
              <th>حالة العقد</th>
              <th>الإجراءات</th>
            </tr>

            <?php
            /* عرض جميع العقود */
            while($row = mysqli_fetch_assoc($result)){

              /* تحديد شكل الحالة */
              $status = $row['ctr_status'];
              $class = "status-active";

              if($status == "ملغي"){
                  $class = "status-cancel";
              }
            ?>
            <tr>
              <td><?= $row['request_id'] ?></td>
              <td><?= $row['contract_id'] ?></td>
              <td><?= $row['inv_name'] ?></td>
              <td><?= $row['beneficiary_name'] ?></td>

              <td>
                <div class="status <?= $class ?>"><?= $status ?></div>
              </td>

              <td>
                <?php
                /* إذا لم يكن العقد ملغيًا يظهر زر الإنهاء */
                if($status != 'ملغي'){
                ?>
                <form method="POST" class="action-form">
                  <input type="hidden" name="contract_id" value="<?= $row['contract_id'] ?>">
                  <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                  <button type="submit" name="end_contract" class="btn btn-delete">إنهاء العقد</button>
                </form>
                <?php } else { ?>
                <div class="ended-text">منتهي</div>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>

            <tr class="empty-row"><td colspan="6"></td></tr>
            <tr class="empty-row"><td colspan="6"></td></tr>
            <tr class="empty-row"><td colspan="6"></td></tr>

          </table>
        </div>

      </div>
    </div>

  </div>
</div>

</body>
</html>