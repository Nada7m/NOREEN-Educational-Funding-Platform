<?php
/* بدء الجلسة */
session_start();

/* الاتصال بقاعدة البيانات */
$con = new mysqli("localhost", "root", "", "noreen");

if ($con->connect_error) {
    die("فشل الاتصال بالقاعدة");
}

$con->set_charset("utf8mb4");

/* رقم المستفيد */
$current_bnf_id = $_SESSION['bnf_id'] ?? 0;

/* جلب الاستشارات */
$sql = "SELECT 
            co.office_id, 
            co.office_name, 
            MAX(m.msg_time) as last_date,

            /* آخر رسالة من المكتب */
            MAX(CASE WHEN m.sender_type = 'office' THEN m.msg_time END) as last_off_time,

            /* آخر رسالة من المستفيد */
            MAX(CASE WHEN m.sender_type = 'beneficiary' THEN m.msg_time END) as last_bnf_time

        FROM bnf_off_msg m
        JOIN consulting_office co ON m.office_id = co.office_id

        WHERE m.bnf_id = '$current_bnf_id'

        GROUP BY co.office_id, co.office_name
        ORDER BY last_date DESC";

$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<title>الاستشارات - نورين</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">

<style>
/* محتوى الصفحة */
.content-body{ padding:30px; }

/* صندوق الجدول */
.table-container{ max-width:1250px; margin:20px auto; background:#fff; border-radius:12px; border:0.5px solid #c5c3c3; overflow-x:auto; padding:0; box-shadow:none; }

/* الجدول */
table{ width:100%; min-width:900px; border-collapse:collapse; text-align:center; font-family:'Noto Kufi Arabic',sans-serif; }

/* رأس الجدول */
thead tr th{ background:#f8f8f8; color:#3E2454; font-size:15px; font-weight:700; padding:14px 10px; border-bottom:1px solid #ddd; }

/* خلايا الجدول */
tbody td{ padding:14px 10px; border-bottom:1px solid #eee; font-size:14px; color:#333; }

/* آخر صف */
tbody tr:last-child td{ border-bottom:none; }

/* زر الإجراء */
.btn-action{ display:inline-block; padding:8px 18px; border:1px solid #999; border-radius:10px; background:#fff; color:#3E2454; text-decoration:none; font-size:13px; font-weight:600; transition:0.3s; }

/* زر الرد الجديد */
.btn-green-alert{ background:#D1FAE5; color:#065F46; border:1px solid #10B981; font-weight:700; }

/* مرور على الزر */
.btn-action:hover{ background:#f4f0f7; }
</style>
</head>

<body>

<!-- التخطيط -->
<div class="layout">

    <!-- القائمة الجانبية -->
    <aside class="sidebar">

        <div class="sidebar-top">

            <!-- الشعار -->
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="نورين">
            </div>

            <!-- روابط القائمة -->
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
                <li><a href="Ben19_Consultations.php" class="active">الاستشارات</a></li>
            </ul>

        </div>

        <!-- تسجيل الخروج -->
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
                <div class="page-title">الاستشارات</div>
                <div class="page-description">صفحة تتضمن جميع الاستشارات التي تم تقديمها للمكاتب الاستشارية</div>
            </div>

            <!-- الإعدادات -->
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

        <!-- محتوى الصفحة -->
        <div class="content-body">

            <!-- صندوق الجدول -->
            <div class="table-container">

                <table>

                    <thead>
                        <tr>
                            <th>اسم المكتب</th>
                            <th>تاريخ التواصل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if($result && $result->num_rows > 0): ?>

                            <?php while($row = $result->fetch_assoc()): ?>

                                <?php
                                /* آخر رسالة */
                                $last_off = $row['last_off_time'];
                                $last_bnf = $row['last_bnf_time'];

                                /* التحقق من وجود رد جديد */
                                $is_new_reply = (!$last_bnf || ($last_off > $last_bnf));

                                /* تنسيق الزر */
                                $btn_class = $is_new_reply ? "btn-action btn-green-alert" : "btn-action";
                                $btn_text = "عرض محتوى الاستشارة";
                                ?>

                                <tr>

                                    <!-- اسم المكتب -->
                                    <td style="font-weight:600;">
                                        <?php echo htmlspecialchars($row['office_name']); ?>
                                    </td>

                                    <!-- التاريخ -->
                                    <td>
                                        <?php echo date("d-m-Y", strtotime($row['last_date'])); ?>
                                    </td>

                                    <!-- الإجراء -->
                                    <td>
                                        <a href="Ben18_ContactConsultingOffice.php?off_id=<?php echo $row['office_id']; ?>" class="<?php echo $btn_class; ?>">
                                            <?php echo $btn_text; ?>
                                        </a>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <!-- لا توجد استشارات -->
                            <tr>
                                <td colspan="3" style="text-align:center; padding:20px;">
                                    لا توجد استشارات سابقة.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

</body>
</html>