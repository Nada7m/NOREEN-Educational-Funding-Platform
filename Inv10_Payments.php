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
                 scholarship_opps.sch_name
               FROM scholarship_requests
               INNER JOIN scholarship_opps
                 ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
               INNER JOIN beneficiary
                 ON scholarship_requests.bnf_id = beneficiary.bnf_id
               INNER JOIN e_contract
                 ON scholarship_requests.request_id = e_contract.request_id
               WHERE scholarship_opps.inv_id = ?
                 AND scholarship_requests.request_status = 'مقبول'
                 AND e_contract.ctr_status = 'نشط'
               ORDER BY scholarship_requests.request_id DESC";
$active_stmt = mysqli_prepare($con, $active_sql);
mysqli_stmt_bind_param($active_stmt, "i", $inv_id);
mysqli_stmt_execute($active_stmt);
$active_result = mysqli_stmt_get_result($active_stmt);
$active_rows = [];
while ($row = mysqli_fetch_assoc($active_result)) {
    $active_rows[] = $row;
}

/* المستفيدون الآخرون */
$ended_sql = "SELECT
                scholarship_requests.request_id,
                beneficiary.f_name,
                beneficiary.l_name,
                scholarship_opps.sch_name
              FROM scholarship_requests
              INNER JOIN scholarship_opps
                ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
              INNER JOIN beneficiary
                ON scholarship_requests.bnf_id = beneficiary.bnf_id
              INNER JOIN e_contract
                ON scholarship_requests.request_id = e_contract.request_id
              WHERE scholarship_opps.inv_id = ?
                AND scholarship_requests.request_status = 'مقبول'
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>المدفوعات</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">
<style>
.payments-page{
  padding:35px 30px 50px;
}
.inner-title{
  font-size:24px;
  font-weight:700;
  color:#4a2b63;
  text-align:right;
  margin-bottom:22px;
}
.track-tabs{
  width:100%;
  max-width:1000px;
  margin:0 auto 22px;
  display:grid;
  grid-template-columns:1fr 1fr;
  border:1px solid #d9d9d9;
  background:#fff;
}
.track-tab{
  height:56px;
  display:flex;
  align-items:center;
  justify-content:center;
  text-decoration:none;
  font-size:18px;
  font-weight:700;
  color:#3E2454;
  background:#FFFFFF;
  border-left:1px solid #d9d9d9;
}
.track-tab:last-child{
  border-left:none;
}
.track-tab.active{
  background:#F4EFF8;
}
.payments-box{
  width:100%;
  max-width:1000px;
  margin:0 auto;
  background:#FFFFFF;
  border:1px solid #e3e3e3;
  border-radius:10px;
  box-shadow:0 2px 10px rgba(0,0,0,0.06);
  overflow:hidden;
}
.table-head{
  display:grid;
  grid-template-columns:1fr 1fr 1fr;
  border-bottom:1px solid #d9d9d9;
}
.head-cell{
  padding:14px 18px;
  text-align:center;
  font-size:18px;
  font-weight:700;
  color:#4a2b63;
  border-left:1px solid #d9d9d9;
}
.head-cell:last-child{
  border-left:none;
}
.table-row{
  display:grid;
  grid-template-columns:1fr 1fr 1fr;
  align-items:center;
  min-height:86px;
  border-bottom:1px solid #d9d9d9;
}
.table-row:last-child{
  border-bottom:none;
}
.table-cell{
  padding:16px 18px;
  text-align:center;
  font-size:16px;
  color:#555555;
  line-height:1.8;
}
.details-wrap{
  display:flex;
  justify-content:center;
  align-items:center;
}
.details-btn{
  min-width:160px;
  height:44px;
  border:1px solid #8b8b8b;
  border-radius:14px;
  background:#FFFFFF;
  color:#4a2b63;
  font-size:16px;
  font-weight:700;
  display:flex;
  align-items:center;
  justify-content:center;
  text-decoration:none;
  font-family:"Noto Kufi Arabic",sans-serif;
}
.ended-btn{
  min-width:160px;
  height:44px;
  border:1px solid #8b8b8b;
  border-radius:14px;
  background:#FFFFFF;
  color:#7b7b7b;
  font-size:16px;
  font-weight:700;
  display:flex;
  align-items:center;
  justify-content:center;
  font-family:"Noto Kufi Arabic",sans-serif;
  cursor:default;
}
.empty-box{
  width:100%;
  max-width:1000px;
  margin:0 auto;
  background:#FFFFFF;
  border:1px solid #e3e3e3;
  border-radius:10px;
  box-shadow:0 2px 10px rgba(0,0,0,0.06);
  padding:40px 20px;
  text-align:center;
  font-size:20px;
  color:#777777;
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
        <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
        <li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
        <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
        <li><a href="Inv10_Payments.php" class="active">المدفوعات</a></li>
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
  <h1 class="page-title">المدفوعات</h1>
  <p class="page-description">صفحة إدارة مدفوعات المنح للمستفيدين</p>
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

    <section class="payments-page">

      <div class="inner-title">قائمة المستفيدين من برامجك التمويلية</div>

      <div class="track-tabs">
        <a href="Inv10_Payments.php?tab=active" class="track-tab <?php echo $tab === 'active' ? 'active' : ''; ?>">المستفيدون النشطون</a>
        <a href="Inv10_Payments.php?tab=others" class="track-tab <?php echo $tab === 'others' ? 'active' : ''; ?>">المستفيدين السابقين </a>
      </div>

      <?php if ($tab === 'others') { ?>

        <div class="payments-box">
          <div class="table-head">
            <div class="head-cell">اسم المستفيد</div>
            <div class="head-cell">اسم المنحة</div>
            <div class="head-cell">الإجراء</div>
          </div>

          <?php if (count($ended_rows) > 0) { ?>
            <?php foreach ($ended_rows as $row) { ?>
              <div class="table-row">

                <div class="table-cell">
                  <?php echo htmlspecialchars($row['f_name']) . " " . htmlspecialchars($row['l_name']); ?>
                </div>

                <div class="table-cell">
                  <?php echo htmlspecialchars($row['sch_name']); ?>
                </div>

                <div class="table-cell">
                  <div class="details-wrap">
                    <div class="ended-btn">منتهي</div>
                  </div>
                </div>

              </div>
            <?php } ?>
          <?php } else { ?>
            <div class="empty-box">لا يوجد مستفيدون هنا حالياً</div>
          <?php } ?>

        </div>

      <?php } else { ?>

        <div class="payments-box">
          <div class="table-head">
            <div class="head-cell">اسم المستفيد</div>
            <div class="head-cell">اسم المنحة</div>
            <div class="head-cell">الإجراء</div>
          </div>

          <?php if (count($active_rows) > 0) { ?>
            <?php foreach ($active_rows as $row) { ?>
              <div class="table-row">

                <div class="table-cell">
                  <?php echo htmlspecialchars($row['f_name']) . " " . htmlspecialchars($row['l_name']); ?>
                </div>

                <div class="table-cell">
                  <?php echo htmlspecialchars($row['sch_name']); ?>
                </div>

                <div class="table-cell">
                  <div class="details-wrap">
                    <a href="Inv11_PaymentDetails.php?request_id=<?php echo $row['request_id']; ?>" class="details-btn">عرض التفاصيل</a>
                  </div>
                </div>

              </div>
            <?php } ?>
          <?php } else { ?>
            <div class="empty-box">لا يوجد مستفيدون هنا حالياً</div>
          <?php } ?>

        </div>

      <?php } ?>

    </section>

  </div>

</div>

</body>
</html>