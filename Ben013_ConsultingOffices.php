<?php
session_start();

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) { die("فشل الاتصال بقاعدة البيانات");} $conn->set_charset("utf8mb4");

/* التحقق من وجود دول مختارة في الفلتر وتخزيينها في المتغير ، وإذا لم توجد تترك فارغة */
if (isset($_GET['country'])) {
    $selectedCountry = $_GET['country'];}
    else { $selectedCountry = "";}

/*  جلب المكاتب وربطها مع كل دولة يكون رقم المكتب موجود عندها*/
if (!empty($selectedCountry)) {
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
            WHERE con_name = ?
        )
        GROUP BY 
            co.office_id,
            co.office_name,
            co.office_description,
            co.Bachelor_fee,
            co.Masters_fee,
            co.Phd_fee
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedCountry);}
    else { $sql = "
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
    ";
$stmt = $conn->prepare($sql);}
$stmt->execute();

/* متغير يحوي جميع الصفوف الناتجة من الاستعلام*/
$result = $stmt->get_result();

/*  مصفوفة فارغة لتخزين المكاتب */
$offices = [];

/* لوب يمر على كل صف في جدول المكاتب في القاعدة ويجلب المكاتب واحدًا تلو الآخر */
while ($row = $result->fetch_assoc()) { $offices[] = $row;}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>المكاتب الاستشارية</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">
<style>

.page{  padding:20px 30px; font-family:"Noto Kufi Arabic", sans-serif;}
.frow{ display:flex; align-items:center; gap:14px; margin-bottom:14px;}
.flbl{ font-size:16px; font-weight:600; color:#444;}
.fsel { padding: 4px 15px; border: 1px ; width: 120px;  }
.cgrid{ display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:25px;}
.ccard{ background:#fff; border:1px solid #e4e4e4; width:100%; border-radius:14px; padding:25px;box-shadow:0 1px 1px rgba(127, 127, 127, 0.05); display:flex; flex-direction:column; gap:8px; box-sizing:border-box;}
.cttl{  font-size:15px; font-weight:700;  color:#3E2454;  text-align:center;  padding-bottom:2px; border-bottom:1px solid #eee; margin-bottom:-6px;}
.cdesc{font-size:13px; color:#666; text-align:center; border-bottom:1px solid #eee; margin-top:4px; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:3; overflow:hidden; text-overflow:ellipsis; min-height:25px;}
.sttl{ font-size:14px; font-weight:700; color:#444; text-align:center;}
.cbox{ display:flex; flex-wrap:wrap; justify-content:center; gap:6px; padding-bottom:10px; border-bottom:1px solid #eee;}
.ctag{ background:#eadcf6; color:#5a2d82; padding:6px 10px; border-radius:16px; font-size:10px; font-weight:600; line-height:1.4;}
.btnmore{ margin-top:auto; width:100%; height:44px; border:none; border-radius:6px; background:#4b2a63; color:#fff; font-family:"Noto Kufi Arabic", sans-serif; font-size:15px;cursor:pointer;}
.btnmore:hover{ background:#3d2251;}
.nores{ background:#fff; border:1px solid #e3e3e3; border-radius:10px; padding:20px; text-align:center; color:#666;}

</style>
<!-- الاساسيات المشتركة بين كل الصفحات-->
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
<li><a href="Ben19_Consultations.php">الاستشارات</a></li>
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
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon">
<div class="dropdown-menu">
<a href="Ben02_Profile.php">الملف الشخصي</a>
                                      <a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
<a href="support.php">تقديم شكوى او استفسار</a>
</div>
</div>
</div>
</header>
<!-- اعلى الصفحة والفلتر-->

<div class="page">  <div class="frow"> 
<div class="flbl">تصفية المكاتب الاستشارية حسب الدولة:</div>

<form class="fform" method="get">
<select id="country" name="country" class="fsel" onchange="this.form.submit()">
<option value="">اختر الدولة</option>
<option value="امريكا">امريكا</option>
<option value="فرنسا">فرنسا</option>
<option value="ايرلندا">ايرلندا</option>
<option value="مالطا">مالطا</option>
<option value="الهند">الهند</option>
<option value="الصين">الصين</option>
<option value="اليابان">اليابان</option>
<option value="بريطانيا">بريطانيا</option>
<option value="نيوزلندا">نيوزلندا</option>
<option value="ماليزيا">ماليزيا</option>
<option value="تركيا">تركيا</option>
<option value="المانيا">المانيا</option>
<option value="كندا">كندا</option>
<option value="استراليا">استراليا</option>
</select>
</form>
</div>


<?php if (!empty($offices)) { ?>
<div class="cgrid">
<!-- لوب يمر على كل مكتب في القاعدة ليعرض في بطاقة مستقلة -->
<?php foreach ($offices as $office) { ?>
<?php
$countries = [];

/* تقسيم الدول القادمة من قاعدة البيانات إلى مصفوفة */
if (!empty($office['countries']))
{$countries = explode("||", $office['countries']);}
?>

<div class="ccard">  <div class="cttl"><?php echo $office['office_name']; ?>  </div>
<div class="cdesc">  <?php echo $office['office_description']; ?>   </div>
<div class="sttl">الدول:</div>
<div class="cbox">

<?php if (!empty($countries)) { ?>

<?php foreach ($countries as $country) { ?>

<div class="ctag">
<?php echo  ($country); ?>
</div>

<?php } ?>

<?php } else { ?>

<div class="ctag">لا توجد دول</div>

<?php } ?>
<!-- زر استعراض التفاصيل الخاصة بكل مكتب عن طريق استدعاء بياناته من القاعدة-->
</div>
<button type="button" class="btnmore" onclick="window.location.href=
'Ben14_OfficeDetails.php?id=<?php echo $office['office_id']; ?>'"> عرض تفاصيل أكثر </button>
</div>

<?php } ?>

</div>

<?php } else { ?> <div class="nores"> لا توجد مكاتب استشارية مطابقة للدول المختارة. </div> <?php } ?>

</div>
</div>
</div>

</body> </html>
