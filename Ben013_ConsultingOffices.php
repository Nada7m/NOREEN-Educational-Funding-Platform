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

/* الدول المختارة من الفلتر */
$selectedCountries = isset($_GET['country']) && is_array($_GET['country']) ? $_GET['country'] : [];

/* حذف القيم الفارغة */
$selectedCountries = array_filter($selectedCountries, function($value){
    return trim($value) !== "";
});

/* قائمة الدول الثابتة للفلتر */
$countryList = [
    "امريكا",
    "فرنسا",
    "ايرلندا",
    "مالطا",
    "الهند",
    "الصين",
    "اليابان",
    "بريطانيا",
    "نيوزلندا",
    "ماليزيا",
    "تركيا",
    "المانيا",
    "كندا",
    "استراليا",
    "جنوب افريقيا",
    "اسبانيا",
    "ايطاليا",
    "هولندا",
    "بلجيكا",
    "سويسرا",
    "السويد",
    "النرويج",
    "فنلندا",
    "الدنمارك",
    "بولندا",
    "النمسا",
    "التشيك",
    "المجر",
    "البرتغال",
    "اليونان",
    "روسيا",
    "كوريا الجنوبية",
    "سنغافورة",
    "تايلاند",
    "اندونيسيا",
    "الفلبين",
    "فيتنام",
    "مصر",
    "الامارات",
    "الكويت",
    "قطر",
    "الاردن"
];

/* جلب المكاتب */
if (!empty($selectedCountries)) {

    $placeholders = implode(',', array_fill(0, count($selectedCountries), '?'));
    $types = str_repeat('s', count($selectedCountries));

    $sql = "
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
        WHERE co.office_id IN (
            SELECT office_id
            FROM office_country
            WHERE con_name IN ($placeholders)
        )
        GROUP BY 
            co.office_id,
            co.office_name,
            co.office_description,
            co.Bachelor_fee,
            co.Masters_fee,
            co.Phd_fee
        ORDER BY co.office_id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$selectedCountries);

} else {

    $sql = "
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
        GROUP BY 
            co.office_id,
            co.office_name,
            co.office_description,
            co.Bachelor_fee,
            co.Masters_fee,
            co.Phd_fee
        ORDER BY co.office_id DESC
    ";

    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$offices = [];
while ($row = $result->fetch_assoc()) {
    $offices[] = $row;
}

$stmt->close();
$conn->close();

/* دالة لاختصار الوصف */
function shortText($text, $length = 95){
    $text = trim($text);

    if (mb_strlen($text, "UTF-8") <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length, "UTF-8") . " ...";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>المكاتب الاستشارية</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css">

<style>
.page{
  padding:30px 40px;
  font-family:"Noto Kufi Arabic", sans-serif;
}

.filter-row{
  display:flex;
  align-items:flex-start;
  gap:14px;
  margin-bottom:24px;
  flex-wrap:wrap;
}

.filter-label{
  font-size:16px;
  font-weight:600;
  color:#444;
  margin-top:8px;
}

.filter-form{
  margin:0;
}

.filter-select{
  width:260px;
  height:130px;
  border:1px solid #c9b7d7;
  border-radius:8px;
  padding:8px;
  background:#fff;
  font-family:"Noto Kufi Arabic", sans-serif;
  font-size:14px;
  color:#444;
  outline:none;
}

.filter-note{
  margin-top:8px;
  font-size:12px;
  color:#777;
}

.consultants-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(320px, 360px));
  gap:22px;
  justify-content:start;
}

.consultant-card{
  background:#fff;
  border:1px solid #e4e4e4;
  border-radius:10px;
  padding:18px;
  box-shadow:0 2px 8px rgba(0,0,0,0.05);
}

.consultant-title{
  font-size:20px;
  font-weight:700;
  color:#5a2d82;
  margin-bottom:8px;
}

.consultant-desc{
  font-size:14px;
  color:#666;
  line-height:1.9;
  margin-bottom:14px;
  min-height:54px;
}

.info-title{
  font-size:15px;
  font-weight:700;
  color:#444;
  margin-bottom:8px;
}

.countries-box{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin-bottom:15px;
}

.country-tag{
  background:#eadcf6;
  color:#5a2d82;
  padding:6px 12px;
  border-radius:16px;
  font-size:13px;
  font-weight:600;
}

.more-tag{
  background:#f3f3f3;
  color:#666;
  padding:6px 12px;
  border-radius:16px;
  font-size:13px;
  font-weight:600;
}

.fees-box{
  background:#f2f5f6;
  border-radius:8px;
  padding:14px;
  margin-bottom:16px;
}

.fees-title{
  font-size:14px;
  font-weight:700;
  color:#555;
  margin-bottom:10px;
}

.fees-row{
  display:flex;
  justify-content:space-between;
  gap:10px;
}

.fee-item{
  flex:1;
  text-align:center;
}

.fee-name{
  font-size:14px;
  font-weight:700;
  color:#555;
  margin-bottom:4px;
}

.fee-value{
  font-size:14px;
  color:#5a2d82;
}

.details-btn{
  width:100%;
  height:42px;
  border:none;
  border-radius:6px;
  background:#4b2a63;
  color:#fff;
  font-family:"Noto Kufi Arabic", sans-serif;
  font-size:15px;
  cursor:pointer;
}

.details-btn:hover{
  background:#3d2251;
}

.no-data{
  background:#fff;
  border:1px solid #e3e3e3;
  border-radius:10px;
  padding:20px;
  text-align:center;
  color:#666;
  font-size:15px;
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
        <li><a href="#">طلبات إصدار القبول</a></li>
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
        <div class="page-description">صفحة استعراض المكاتب الاستشارية</div>
      </div>

      <div class="header-icons">
        <div class="settings-dropdown">
          <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">

          <div class="dropdown-menu">
            <a href="Ben02_Profile.php">الملف الشخصي</a>
            <a href="Ben03_EditProfile.php">تعديل الملف الشخصي</a>
            <a href="#">محفظة منحتي</a>
            <a href="#">التواصل والدعم</a>
          </div>
        </div>
      </div>

    </header>

    <div class="page">

      <div class="filter-row">

        <div class="filter-label">
          تصفية المكاتب الاستشارية حسب الدولة:
        </div>

        <form class="filter-form" method="get">
          <select id="country" name="country[]" multiple class="filter-select" onchange="this.form.submit()">
            <?php foreach ($countryList as $country) { ?>
              <option value="<?php echo htmlspecialchars($country); ?>"
                <?php echo in_array($country, $selectedCountries) ? "selected" : ""; ?>>
                <?php echo htmlspecialchars($country); ?>
              </option>
            <?php } ?>
          </select>

          <div class="filter-note">
            يمكن اختيار أكثر من دولة بالضغط المستمر على Ctrl أو Cmd.
          </div>
        </form>

      </div>

      <?php if (!empty($offices)) { ?>
        <div class="consultants-grid">

          <?php foreach ($offices as $office) { ?>
            <?php
              $countries = [];

              if (!empty($office['countries'])) {
                  $countries = explode("||", $office['countries']);
              }

              $shownCountries = array_slice($countries, 0, 4);
              $remainingCount = count($countries) - count($shownCountries);
            ?>

            <div class="consultant-card">

              <div class="consultant-title">
                <?php echo htmlspecialchars($office['office_name']); ?>
              </div>

              <div class="consultant-desc">
                <?php echo htmlspecialchars(shortText($office['office_description'], 85)); ?>
              </div>

              <div class="info-title">الدول:</div>

              <div class="countries-box">
                <?php if (!empty($shownCountries)) { ?>
                  <?php foreach ($shownCountries as $country) { ?>
                    <div class="country-tag">
                      <?php echo htmlspecialchars($country); ?>
                    </div>
                  <?php } ?>

                  <?php if ($remainingCount > 0) { ?>
                    <div class="more-tag">
                      +<?php echo $remainingCount; ?>
                    </div>
                  <?php } ?>
                <?php } else { ?>
                  <div class="country-tag">لا توجد دول</div>
                <?php } ?>
              </div>

              <div class="fees-box">
                <div class="fees-title">رسوم خدمة إصدار القبول:</div>

                <div class="fees-row">
                  <div class="fee-item">
                    <div class="fee-name">بكالوريوس</div>
                    <div class="fee-value"><?php echo htmlspecialchars($office['Bachelor_fee']); ?> ر.س</div>
                  </div>

                  <div class="fee-item">
                    <div class="fee-name">ماجستير</div>
                    <div class="fee-value"><?php echo htmlspecialchars($office['Masters_fee']); ?> ر.س</div>
                  </div>

                  <div class="fee-item">
                    <div class="fee-name">دكتوراه</div>
                    <div class="fee-value"><?php echo htmlspecialchars($office['Phd_fee']); ?> ر.س</div>
                  </div>
                </div>
              </div>

              <button
                type="button"
                class="details-btn"
                onclick="window.location.href='Ben11_ConsultantDetails.php?id=<?php echo $office['office_id']; ?>'">
                عرض تفاصيل أكثر
              </button>

            </div>
          <?php } ?>

        </div>
      <?php } else { ?>
        <div class="no-data">
          لا توجد مكاتب استشارية مطابقة للدول المختارة.
        </div>
      <?php } ?>

    </div>

  </div>

</div>

</body>
</html>