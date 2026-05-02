<?php
session_start();

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

/* تحديد المجال المختار من الفلتر */
$selected_field = isset($_POST['sch_field']) ? $_POST['sch_field'] : '';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>التقديم على المنح</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">

<style>

/* مساحة الصفحة */
.page{ padding:20px 40px; position:relative; }

/* حاوية الفلتر */
.filter-container{ display:flex; align-items:center; gap:10px; margin-bottom:30px; }

/* نص الفلتر */
.filter-label{ color:#3E2454; font-size:15px; font-weight:600; }

/* قائمة الفلتر */
.filter-select{ padding:8px 12px; border:1px solid #DDDDDD; border-radius:5px; ; font-size:14px; color:#444444; background:#FFFFFF; min-width:200px; }

/* شبكة البطاقات */
.scholarships-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(360px,1fr)); gap:25px; align-items:stretch; }

/* بطاقة المنحة */
.s-card{ background:#FFFFFF; border-radius:10px; padding:25px; border:1px solid #EEEEEE;  text-align:right; display:flex; flex-direction:column; height:100%; }

/* عنوان البطاقة */
.s-title{ color:#3E2454; font-size:18px; font-weight:700; line-height:1.8; min-height:65px; display:flex; align-items:center; justify-content:center; text-align:center; margin-bottom:18px; }

/* محتوى البطاقة */
.s-content{ display:flex; flex-direction:column; gap:14px; flex:1; }

/* صف البيانات */
.s-item{ display:flex; align-items:flex-start; justify-content:flex-start; gap:8px; direction:rtl; text-align:right; }

/* اسم الحقل */
.s-lbl{ color:#777777; font-size:14px; font-weight:600; flex-shrink:0; }

/* قيمة الحقل */
.s-val{ color:#70A0AF; font-size:16px; font-weight:500; line-height:1.8;  text-align:right; direction:rtl; }

/* الفاصل */
.s-divider{ border-top:1px solid #EEEEEE; margin:8px 0 16px; }

/* موعد التقديم */
.s-deadline{ margin-top:auto; margin-bottom:18px; }

/* زر التفاصيل */
.btn-action{ width:100%; background:#70A0AF; color:#FFFFFF; border:none; padding:12px; border-radius:6px; cursor:pointer; font-weight:600; text-decoration:none; text-align:center; display:block;  font-size:14px; }
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
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content">

        <header class="header">
            <div class="page-heading">
                <div class="page-title">التقديم على المنح</div>
                <div class="page-description">صفحة استعراض وتقديم الطلبات على المنح المتاحة</div>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben03_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>
        <div class="page">
            <form method="POST" class="filter-container">
                <div class="filter-label">تصفية المنح حسب المجال الرئيسي:</div>
                <select name="sch_field" class="filter-select" onchange="this.form.submit()">
                    <option value="">اختر المجال</option>
                    <?php
                    /* عرض المجالات داخل القائمة */
                    $fields = ["تقني وحاسوبي", "علوم طبيعية", "صناعي وتشغيلي", "اداري", "قانوني", "اجتماعي وانساني", "تصميمي", "اقتصادي", "إعلامي", "بيئي", "لوجيستي", "صحي"];

                    foreach($fields as $f) {
                        $sel = ($selected_field == $f) ? "selected" : "";
                        echo "<option value='$f' $sel>$f</option>"; } ?>
                </select>
            </form>
            <div class="scholarships-grid">
                <?php
                /* جلب المنح التي ما زال التقديم عليها متاحًا */
                $q = "SELECT s.*, i.inv_name
                      FROM scholarship_opps s
                      LEFT JOIN investor i ON s.inv_id = i.inv_id
                      WHERE DATE(s.app_deadline) >= CURDATE()";
                /* إضافة الفلترة حسب المجال إذا تم الاختيار */
                if (!empty($selected_field)) {
                    $q .= " AND s.sch_field = '".$conn->real_escape_string($selected_field)."'";
                }
                $res = $conn->query($q);
                /* عرض بطاقات المنح */
                while($row = $res->fetch_assoc()):
                    $provider = !empty($row['inv_name']) ? $row['inv_name'] : 'غير محدد'; ?>
                <div class="s-card">
                    <div class="s-title"><?php echo htmlspecialchars($row['sch_name']); ?></div>
                    <div class="s-content">
                        <div class="s-item">
                            <div class="s-lbl">مقدمة من:</div>
                            <div class="s-val"><?php echo htmlspecialchars($provider); ?></div>
                        </div>
                        <div class="s-item">
                            <div class="s-lbl">المجال الرئيسي:</div>
                            <div class="s-val"><?php echo htmlspecialchars($row['sch_field']); ?></div>
                        </div>
                        <div class="s-item">
                            <div class="s-lbl">الدرجة المستهدفة:</div>
                            <div class="s-val"><?php echo htmlspecialchars($row['study_level']); ?></div>
                        </div>
                    </div>
                    <div class="s-divider"></div>
                    <div class="s-item s-deadline">
                        <div class="s-lbl">آخر موعد للتقديم:</div>
                        <div class="s-val"><?php echo date("Y-m-d", strtotime($row['app_deadline'])); ?></div>
                    </div>
                    <a href="Ben05_ScholarshipDetails.php?id=<?php echo $row['scholarship_id']; ?>" class="btn-action">عرض تفاصيل أكثر</a>
                </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>
</div>
</body>
</html>