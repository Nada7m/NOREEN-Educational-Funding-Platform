<?php
session_start();

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

/* التحقق من رقم المكتب */
if (!isset($_GET['id'])) {
    die("رقم المكتب غير موجود");
}

$officeId = (int) $_GET['id'];

/* جلب تفاصيل المكتب */
$sqlOffice = "
    SELECT 
        co.office_id,
        co.office_name,
        co.office_description,
        co.Bachelor_fee,
        co.Masters_fee,
        co.Phd_fee,
        GROUP_CONCAT(DISTINCT oc.con_name ORDER BY oc.con_name SEPARATOR '||') AS countries
    FROM consulting_office co
    LEFT JOIN office_country oc ON co.office_id = oc.office_id
    WHERE co.office_id = ?
    GROUP BY 
        co.office_id,
        co.office_name,
        co.office_description,
        co.Bachelor_fee,
        co.Masters_fee,
        co.Phd_fee
";

$stmtOffice = $conn->prepare($sqlOffice);
$stmtOffice->bind_param("i", $officeId);
$stmtOffice->execute();
$resOffice = $stmtOffice->get_result();

if ($resOffice->num_rows == 0) {
    die("المكتب غير موجود");
}

$office = $resOffice->fetch_assoc();
$stmtOffice->close();

/* تقسيم الدول */
$countries = [];
if (!empty($office['countries'])) {
    $countries = explode("||", $office['countries']);
}

/* جلب التقييمات */
$sqlRate = "
    SELECT
        r.rating_date,
        r.comment_text,
        CONCAT(b.f_name, ' ', b.l_name) AS bnf_name
    FROM rating r
    LEFT JOIN beneficiary b ON r.bnf_id = b.bnf_id
    WHERE r.office_id = ?
    ORDER BY r.rating_date DESC
";

$stmtRate = $conn->prepare($sqlRate);
$stmtRate->bind_param("i", $officeId);
$stmtRate->execute();
$resRate = $stmtRate->get_result();

$rates = [];
while ($row = $resRate->fetch_assoc()) {
    $rates[] = $row;
}

$stmtRate->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تفاصيل المكتب الاستشاري</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">
<style>
.page{
  padding:10px 30px;
  font-family:"Noto Kufi Arabic", sans-serif;
}

/* صف يحتوي زر الرجوع أعلى الصفحة */
.backrow{
  width:90%;
  margin:10px auto 18px;
  display:flex;
  justify-content:flex-end;
}

/* حاوية لتنظيم زر الرجوع والأيقونة */
.backwrap{
  display:flex;
  align-items:center;
}


/* كارد  تفاصيل المكتب */
.dbox{
  width:90%;
  margin:0 auto 20px;
  background:#fff;
  border:1px solid #e4e4e4;
  border-radius:10px;
  padding:18px;
  box-shadow:0 2px 8px rgba(0,0,0,0.04);
}

/* اسم المكتب في  الكارد */
.ttl{
  font-size:20px;
  font-weight:700;
  color:#5a2d82;
  text-align:right;
  margin-bottom:10px;
}

/* وصف المكتب */
.desc{
  font-size:14px;
  color:#555;
  line-height:2;
  margin-bottom:14px;
}

/*  عنوان قسم الدول */
.sttl{
  font-size:15px;
  font-weight:700;
  color:#444;
  margin-bottom:10px;
}

/* بوكس يحتوي تاقز الدول */
.cbox{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin-bottom:18px;
}

/* التاق الفردي لكل دولة */
.ctag{
  background:#eadcf6;
  color:#5a2d82;
  padding:5px 10px;
  border-radius:14px;
  font-size:12px;
  font-weight:600;
}

/* صف يحتوي الأزرار وصندوق الرسوم */
.midrow{
  display:flex;
  gap:14px;
  margin-bottom:14px;
}

.actcol{
  flex:1;
  display:flex;
  flex-direction:column;
  gap:8px;
}

/* بوكس الرسوم الخاصة بالخدمة */
.feebox{
  flex:1;
  background:#f4f6f7;
  border:1px solid #dcdcdc;
  border-radius:6px;
  padding:14px;
}

/* عنوان بوكس الرسوم */
.feettl{
  font-size:14px;
  font-weight:700;
  color:#555;
  text-align:center;
  margin-bottom:16px;
}

/* قريد لعرض رسوم البرامج  */
.feegrid{
  display:grid;
  grid-template-columns:33% 33% 33%;
  text-align:center;
}

.feeitem{
  font-size:14px;
  color:#4b2a63;
  line-height:2;
}

/* اسم البرنامج (بكالوريوس / ماجستير / دكتوراه) */
.fname{
  display:block;
  font-size:16px;
  font-weight:700;
  color:#555;
  margin-bottom:6px;
}

/* رسوم  البرنامج */
.fprice{
  display:block;
  font-size:16px;
  font-weight:700;
  color:#4b2a63;
}

/* تنسيق مشترك للأزرار */
.btnlight,
.btndark{
  width:100%;
  height:40px;
  border:none;
  border-radius:6px;
  font-family:"Noto Kufi Arabic", sans-serif;
  font-size:15px;
  font-weight:600;
  cursor:pointer;
}

/* زر التواصل للاستشارة */
.btnlight{
  background:#d7bce4;
  color:#fff;
  height:67px;
}

/* زر تقديم طلب إصدار قبول */
.btndark{
  background:#4b2a63;
  color:#fff;
  height:67px;
}

.rsec{
  width:90%;
  margin:0 auto;
}

.rttl{
  font-size:15px;
  font-weight:700;
  color:#4b2a63;
  margin-bottom:10px;
}

/* كارد التقييم الواحد */
.rcard{
  background:#fff;
  border:1px solid #dcdcdc;
  border-radius:4px;
  padding:10px;
  margin-bottom:10px;
  display:flex;
  justify-content:space-between;
  gap:12px;
}

.rtxt{
  flex:1;
}

.rdate{
  font-size:12px;
  color:#777;
  margin-bottom:6px;
}

/* اسم المستفيد */
.rname{
  font-size:14px;
  font-weight:700;
  color:#555;
  margin-bottom:6px;
}

/* نص  التقييم */
.rcom{
  font-size:13px;
  color:#666;
  line-height:1.8;
}

.rimg{
  width:34px;
  height:34px;
  border-radius:4px;
  object-fit:cover;
  border:1px solid #cfcfcf;
}

/* الرسال عند عدم وجود تقييمات */
.norate{
  background:#fff;
  border:1px solid #ddd;
  border-radius:6px;
  padding:14px;
  text-align:center;
  color:#666;
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
<li><a href="Ben10_Consultants.php" class="active">المكاتب الاستشارية</a></li>
<li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
<li><a href="#">الاستشارات</a></li>
</ul>
</div>

<div class="sidebar-bottom">
<form action="logout.php" method="post">
<button type="submit" class="logout-btn">
<img src="ايقونة تسجيل الخروج.png" class="logout-icon">
<b>تسجيل الخروج</b>
</button>
</form>
</div>
</aside>
<div class="main-content">
<header class="header">
<div class="page-heading">
<div class="page-title">المكاتب الاستشارية</div>
<div class="page-description">صفحة استعراض تفاصيل المكتب الاستشاري</div>
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

<!-- زر الرجوع -->
<div class="backrow">
  <div class="backwrap">
    <a href="Ben013_ConsultingOffices.php" class="backbtn">
      <img src="سهم تراجع.svg" class="backicon">
    </a>
  </div>
</div>

<!-- بطاقة التفاصيل -->
<div class="dbox">
  <div class="ttl">
    <?php echo $office['office_name']; ?>
  </div>
  <div class="desc">
    <?php echo $office['office_description']; ?>
  </div>
  <div class="sttl">الدول:</div>
  <div class="cbox">
    <?php if (!empty($countries)) { ?>
      <?php foreach ($countries as $country) { ?>
        <div class="ctag"><?php echo  ($country); ?></div>
      <?php } ?>
    <?php } else { ?>
      <div class="ctag">لا توجد دول</div>
    <?php } ?>
  </div>

  <div class="midrow">
    <!-- أزرار التنفيذ -->
    <div class="actcol">
      <button type="button" class="btnlight" onclick="window.location.href='#'">
        التواصل للاستشارة
      </button>
      <button type="button" class="btndark" onclick="window.location.href='Ben15_AdmissionReq.php?id=<?php echo $office['office_id']; ?>'">
  تقديم طلب إصدار قبول
</button>
    </div>

    <!-- الرسوم -->
    <div class="feebox">
      <div class="feettl">رسوم خدمة إصدار القبول لمختلف البرامج:</div>

      <div class="feegrid">
        <div class="feeitem">
          <span class="fname">بكالوريوس</span>
          <span class="fprice"><?php echo $office['Bachelor_fee']; ?> ر.س</span>
        </div>
        <div class="feeitem">
          <span class="fname">ماجستير</span>
          <span class="fprice"><?php echo $office['Masters_fee']; ?> ر.س</span>
        </div>
        <div class="feeitem">
          <span class="fname">دكتوراه</span>
          <span class="fprice"><?php echo $office['Phd_fee']; ?> ر.س</span>
        </div>
      </div>
    </div>

  </div>

</div>

<!-- التقييمات -->
<div class="rsec">
  <div class="rttl">آراء المستفيدين عن المكتب</div>
  <?php if (!empty($rates)) { ?>
    <?php foreach ($rates as $rate) { ?>
      <div class="rcard">
        <img src=" أيقونة التقيم.svg" class="rimg" alt="مستخدم">
        <div class="rtxt">
          <div class="rdate"><?php echo date("d-m-Y", strtotime($rate['rating_date'])); ?></div>
          <div class="rname">
            <?php
              if (!empty(trim($rate['bnf_name']))) {
                echo $rate['bnf_name'];
              } else {
                echo "مستفيد";
              }
            ?>
          </div>
          <div class="rcom">
            <?php echo $rate['comment_text']; ?>
          </div>
        </div>
      </div>
    <?php } ?>
  <?php } else { ?>
    <div class="norate">
      لا توجد تقييمات لهذا المكتب حتى الآن.
    </div>

  <?php } ?>

</div>
</div>
</div>
</div>
</body>
</html>