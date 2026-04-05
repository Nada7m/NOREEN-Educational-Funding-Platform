<?php
session_start();

/* 1. إعداد الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) { 
    die("فشل الاتصال: " . $conn->connect_error); 
}


/* 2. تحديد الحالة */
$is_details = isset($_GET['id']);
$selected_field = isset($_POST['sch_field']) ? $_POST['sch_field'] : '';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_details ? "تفاصيل المنحة" : "التقديم على المنح"; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">
<style>
.page { padding: 20px 40px; position: relative; }

/* زر التراجع  */
.page-top { display: flex; justify-content: flex-end; padding: 0 0 10px 0; margin: 0; }

.back-btn-details { display: inline-block; cursor: pointer; text-decoration: none; background: none; border: none; }

.back-btn-details img { width: 38px; height: 38px; display: block; }

/* الفلترة */
.filter-container { display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }

.filter-select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }

/*الكروت  */
.scholarships-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 25px; }

.s-card { background: #fff; border-radius: 10px; padding: 25px; border: 1px solid #eee; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: right; }

.s-title { color: #3E2454; font-size: 15px; font-weight: 700; margin-bottom: 20px; text-align: center; }

.s-data-row { display: flex; justify-content: flex-start; margin-bottom: 12px; font-size: 13px; gap: 10px; }

.s-lbl { color: #595959; font-weight: 600; min-width: fit-content; }

.s-val { color: #70A0AF; font-weight: 500; text-align: right; }

.s-divider { border-top: 1px solid #eee; margin: 15px 0; }

.btn-action { width: 100%; background: #70A0AF; color: #fff; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.3s; }

/* صندوق التفاصيل محاذاة النص لليمين */
.details-box { background: #FFFFFF; border: 1px solid #E5E5E5; border-radius: 16px; padding: 24px 28px; margin-top: 5px; text-align: right; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }

/* عنوان صندوق التفاصيل */
.det-header-title { color: #3E2454; font-size: 17px; font-weight: 700; text-align: center; margin-bottom: 24px; }

/* الصف العلوي للتفاصيل */
.det-top-grid { display: grid; grid-template-columns: 1fr 1fr 260px; gap: 22px; align-items: start; direction: ltr; }

/* صندوق تاريخ التقديم */
.det-deadline-box { background: #DCCBE3; border: 1px solid #C7B0D0; border-radius: 12px; padding: 10px 16px; display: inline-flex; align-items: center; width: fit-content; min-width: 220px; justify-self: start; direction: rtl; }

.det-deadline-text { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #3E2454; }

.det-deadline-date { color: #70A0AF; font-weight: 500; }

/* أعمدة بيانات المنحة */
.det-info-col { display: flex; flex-direction: column; gap: 8px; direction: rtl; text-align: right; }

.det-item { display: flex; flex-direction: column; gap: 4px; }

.det-label { color: #6B6B6B; font-size: 14px; font-weight: 600; }

.det-value { color: #70A0AF; font-size: 15px; font-weight: 700; }

.det-specialization { margin-bottom: 18px; font-size: 14px; line-height: 1.9; }

.det-specialization .det-label-inline { color: #595959; font-weight: 700; }

.det-specialization .det-value-inline { color: #70A0AF; font-weight: 600; }

/* تقسيم للسطور، شروط التقديم */
.section-divider { border-top: 1px solid #D9D9D9; margin: 16px 0 18px; }

.conditions-sec { margin-top: 10px; text-align: right; }

.conditions-title { margin: 0 0 14px 0; color: #000; font-size: 15px; font-weight: 700; }

.conditions-text { font-size: 13px; color: #555; line-height: 1.6; text-align: right; white-space: pre-wrap; }
</style>
</head>

<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo"><img src="شعار نورين.png"></div>
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php" class="active">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
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
            <div class="page-heading" style="display:flex;align-items:center;gap:10px;">
                <div class="page-title"><?php echo $is_details ? "تفاصيل المنحة" : "التقديم على المنح"; ?></div>
                            <div class="page-description">صفحة استعراض وتقديم الطلبات على المنح المتاحة</div>

            </div>
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben11_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page">
            <?php if (!$is_details): ?>
                <form method="POST" class="filter-container">
                    <span>تصفية المنح حسب المجال الرئيسي:</span>
                    <select name="sch_field" class="filter-select" onchange="this.form.submit()">
                        <option value="">اختر المجال</option>
                        <?php 
                        $fields = ["تقني وحاسوبي", "علوم طبيعية", "صناعي وتشغيلي", "اداري", "قانوني", "اجتماعي وانساني", "تصميمي", "اقتصادي", "إعلامي", "بيئي", "لوجيستي", "صحي"];
                        foreach($fields as $f) {
                            $sel = ($selected_field == $f) ? "selected" : "";
                            echo "<option value='$f' $sel>$f</option>";
                        }
                        ?>
                    </select>
                </form>

                <div class="scholarships-grid">
                    <?php
                    $q = "SELECT * FROM Scholarship_Opps";
                    if(!empty($selected_field)) $q .= " WHERE sch_field = '".$conn->real_escape_string($selected_field)."'";
                    $res = $conn->query($q);
                    while($row = $res->fetch_assoc()): 
                        $parts = explode('-', $row['sch_name']);
                        $provider = isset($parts[0]) ? trim(str_replace('برنامج', '', $parts[0])) : 'غير محدد';
                    ?>
                        <div class="s-card">
                            <div class="s-title"><?php echo $row['sch_name']; ?></div>
                            <div class="s-data-row"><span class="s-lbl">مقدمة من:</span><span class="s-val"><?php echo $provider; ?></span></div>
                            <div class="s-data-row"><span class="s-lbl">الدرجة المستهدفة:</span><span class="s-val"><?php echo $row['study_level']; ?></span></div>
                            <div class="s-data-row"><span class="s-lbl">المجال الرئيسي:</span><span class="s-val"><?php echo $row['sch_field']; ?></span></div>
                            <div class="s-data-row"><span class="s-lbl">التخصصات الدقيقة:</span><span class="s-val"><?php echo $row['Specializations']; ?></span></div>
                            <div class="s-divider"></div>
                            <div class="det-item"> <span class="s-lbl">آخر موعد للتقديم: </span>
                                <span class="s-val" style="margin-right:10px;"><?php echo date("Y-m-d", strtotime($row['app_deadline'])); ?></span>
                            </div>
                            <button class="btn-action" onclick="window.location.href='?id=<?php echo $row['scholarship_id']; ?>'">عرض تفاصيل أكثر</button>
                        </div>
                    <?php endwhile; ?>
                </div>

            <?php else: 
                $sch_id = (int)$_GET['id'];
                $opp = $conn->query("SELECT * FROM Scholarship_Opps WHERE scholarship_id = $sch_id")->fetch_assoc();
            ?>
                <div class="page-top">
                    <a href="Ben04_BrowseScholarships.php" class="back-btn-details">
                        <img src="سهم تراجع.svg">
                    </a>
                </div>
<div class="details-box">
    <div class="det-header-title">اسم المنحة: <?php echo $opp['sch_name']; ?></div>

    <div class="det-top-grid">
        <div class="det-deadline-box">
            <div class="det-deadline-text">
                <span>📅 آخر موعد للتقديم:</span>
                <span class="det-deadline-date"><?php echo date("Y-m-d", strtotime($opp['app_deadline'])); ?></span>
            </div>
        </div>

        <div class="det-info-col">
            <div class="det-item">
                <span class="det-label">التخصص الرئيسي:</span>
                <span class="det-value"><?php echo $opp['sch_field']; ?></span>
            </div>
        </div>

        <div class="det-info-col">
            <div class="det-item">
                <span class="det-label">الدرجة المستهدفة:</span>
                <span class="det-value"><?php echo $opp['study_level']; ?></span>
            </div>
        </div>
    </div>

    <div class="det-specialization">
        <span class="det-label-inline">التخصصات الدقيقة:</span>
        <span class="det-value-inline"><?php echo $opp['Specializations']; ?></span>
    </div>

    <div class="section-divider"></div>

    <div class="conditions-sec">
        <h4 class="conditions-title">الشروط:</h4>
        <div class="conditions-text"><?php echo nl2br($opp['requirements']); ?></div>
    </div>

    <button class="btn-action" style="width: 250px; margin: 40px auto 0; display: block;" onclick="window.location.href='Ben06_ApplyScholarship.php?id=<?php echo $opp['scholarship_id']; ?>'">
        التقديم الآن
    </button>
</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>