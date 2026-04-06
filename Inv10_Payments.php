<?php
session_start();

/* التحقق من تسجيل دخول المستثمر */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* دعم العربية */
mysqli_set_charset($con, "utf8mb4");

/* رقم المستثمر الحالي */
$inv_id = $_SESSION['inv_id'];

/* قيمة البحث */
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

/* جلب المستفيدين المقبولين المرتبطين بالمستثمر */
$sql = "SELECT
            scholarship_requests.request_id,
            beneficiary.f_name,
            beneficiary.l_name,
            scholarship_opps.sch_name
        FROM scholarship_requests
        INNER JOIN scholarship_opps
            ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
        INNER JOIN beneficiary
            ON scholarship_requests.bnf_id = beneficiary.bnf_id
        WHERE scholarship_opps.inv_id = ?
        AND scholarship_requests.request_status = 'مقبول'";

/* إذا فيه بحث */
if ($search != "") {
    $sql .= " AND (
                beneficiary.f_name LIKE ?
                OR beneficiary.l_name LIKE ?
                OR scholarship_opps.sch_name LIKE ?
             )";
}

$sql .= " ORDER BY scholarship_requests.request_id DESC";

$stmt = mysqli_prepare($con, $sql);

if ($search != "") {
    $search_like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "isss", $inv_id, $search_like, $search_like, $search_like);
} else {
    mysqli_stmt_bind_param($stmt, "i", $inv_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
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

/* مساحة الصفحة */
.payments-page{
  padding:35px 40px 50px;
}

/* صف السهم */
.page-top{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:20px;
}

/* عنوان الصفحة الداخلي */
.inner-title{
  text-align:center;
  font-size:22px;
  font-weight:700;
  color:#111111;
  margin-bottom:25px;
}

/* البحث */
.search-box{
  width:100%;
  background:#FFFFFF;
  border:1px solid #d9cbe5;
  border-radius:4px;
  display:flex;
  align-items:center;
  height:48px;
  overflow:hidden;
  margin-bottom:30px;
}

.search-btn{
  width:55px;
  height:100%;
  border:none;
  border-left:1px solid #e9dff0;
  background:#FFFFFF;
  color:#6b517e;
  font-size:18px;
  cursor:pointer;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.search-input{
  flex:1;
  height:100%;
  border:none;
  outline:none;
  padding:0 14px;
  font-size:15px;
  font-family:"Noto Kufi Arabic",sans-serif;
  background:#FFFFFF;
}

.search-left-icon{
  width:55px;
  height:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#999999;
  border-right:1px solid #f0f0f0;
  font-size:16px;
}

/* الصندوق الرئيسي */
.payments-box{
  width:100%;
  background:#FFFFFF;
  border:1px solid #e3e3e3;
  border-radius:10px;
  box-shadow:0 2px 10px rgba(0,0,0,0.06);
  overflow:hidden;
}

/* رأس الجدول */
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

/* صفوف البيانات */
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

/* زر التفاصيل */
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

/* حالة عدم وجود نتائج */
.empty-box{
  padding:40px 20px;
  text-align:center;
  font-size:20px;
  color:#777777;
}

</style>
</head>

<body>

<div class="layout">

  <!-- الشريط الجانبي -->
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

  <!-- المحتوى -->
  <div class="main-content">

    <!-- الهيدر -->
    <header class="header">

      <div class="page-heading">
        <h1 class="page-title">المدفوعات</h1>
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

    <!-- محتوى الصفحة -->
    <section class="payments-page">

      <div class="inner-title">قائمة المستفيدين من برامجك التمويلية</div>

      <!-- البحث -->
      <form method="get" class="search-box">
        <button type="submit" class="search-btn">⌕</button>
        <input type="text" name="search" class="search-input" placeholder="ابحث باسم المستفيد أو اسم المنحة" value="<?php echo htmlspecialchars($search); ?>">
        <div class="search-left-icon">⊗</div>
      </form>

      <!-- الجدول -->
      <div class="payments-box">

        <div class="table-head">
          <div class="head-cell">اسم المستفيد</div>
          <div class="head-cell">اسم المنحة</div>
          <div class="head-cell">الإجراء</div>
        </div>

        <?php if(mysqli_num_rows($result) > 0): ?>

          <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="table-row">

              <div class="table-cell">
                <?php echo htmlspecialchars($row['f_name']) . " " . htmlspecialchars($row['l_name']); ?>
              </div>

              <div class="table-cell">
                <?php echo htmlspecialchars($row['sch_name']); ?>
              </div>

              <div class="table-cell">
                <div class="details-wrap">
                  <a href="Inv11_PaymentDetails.php?request_id=<?php echo $row['request_id']; ?>" class="details-btn">
                    عرض التفاصيل
                  </a>
                </div>
              </div>

            </div>
          <?php endwhile; ?>

        <?php else: ?>
          <div class="empty-box">لا يوجد مستفيدون مطابقون للبحث حالياً</div>
        <?php endif; ?>

      </div>

    </section>

  </div>

</div>

</body>
</html>