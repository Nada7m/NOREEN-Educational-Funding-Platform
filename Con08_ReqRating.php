<?php
session_start();
/* التحقق من تسجيل دخول المكتب */
if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}
/* الاتصال بقاعدة البيانات */
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$con->set_charset("utf8mb4");
/* رقم المكتب الحالي */
$office_id = $_SESSION['office_id'];
/*  جلب تقييمات المستفيدين */
$sql = "SELECT r.request_id, r.rating_date, r.comment_text, b.f_name,b.l_name
        FROM rating r
        INNER JOIN admission_request ar ON r.request_id = ar.request_id
        INNER JOIN beneficiary b ON ar.bnf_id = b.bnf_id
        WHERE ar.office_id = '$office_id'
        ORDER BY r.rating_date DESC";
        // تنفيذ الاستعلام
$result = $con->query($sql);?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تقييمات المستفيدين - نورين</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=6س">
<style>
/*  محتوى الصفحة */
.content-body{padding:30px;}
/*  رأس الصفحة */
.header{display:flex;align-items:center;justify-content:space-between;direction:rt;;}
/* عنوان الصفحة */
.page-heading{direction:rtl;text-align:right;}
/* أيقونات الهيدر */
.header-icons{direction:rtl;}
/*  صندوق الجدول الخارجي */
.table-wrap{max-width:1180px;margin:0 auto;border:0.5px solid #c5c3c3;border-radius:18px;}
/* بطاقة الجدول */
.table-card{background:#FFFFFF;border:1px solid #E8E8E8;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);overflow:hidden;}
/* خلايا الجدول */
.ratings-table{width:100%;border-collapse:collapse;background:#FFFFFF;}
/* رأس الجدول */
.ratings-table th{background:#FFFFFF;color:#3E2454;font-size:16px;font-weight:700;padding:18px 20px;text-align:center;border-bottom:1px solid #DCDCDC;}
/* خلايا الجدول */
.ratings-table td{padding:18px 20px;text-align:center;font-size:14px;color:#555555;border-bottom:1px solid #EAEAEA;vertical-align:middle;}
/* إزالة الخط من آخر صف */
.ratings-table tbody tr:last-child td{border-bottom:none;}
/*  اسم المستفيد */
.bnf-name{font-weight:600;color:#6B6B6B;}
/* رقم الطلب */
.req-code{color:#7A7A7A;font-weight:500;}
/* تاريخ التقييم */
.rating-date{color:#7A7A7A;}
/* نص التقييم */
.rating-comment{color:#6A6A6A;line-height:2;text-align:center;max-width:420px;margin:0 auto;}
/* صف عدم وجود بيانات */
.empty-row td{padding:40px 20px;color:#888888;}
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="شعار نورين">
            </div>
            <ul class="sidebar-menu">
                <li><a href="Con00_MainPage.php">الرئيسية</a></li>
                <li><a href="Con04_AdmissionReq.php">إدارة طلبات القبول</a></li>
                <li><a href="Con03_Consultations.php">الاستشارات</a></li>
                <li><a href="Con08_ReqRating.php" class="active">تقييمات المستفيدين</a></li>
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
                <div class="page-title">تقييمات المستفيدين</div>
                <div class="page-description">تقييمات المستفيدين لخدمة إصدار القبول الجامعي</div>
            </div>
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="القائمة">
                    <div class="dropdown-menu">
                        <a href="Con02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى أو استفسار</a>
                    </div>
                </div>
            </div>
           
        </header>
        <div class="content-body">
            <div class="table-wrap">
                <div class="table-card">
                       <!-- جدول التقييمات -->
                    <table class="ratings-table">
                        <thead>
                            <tr>
                                <th>اسم المستفيد</th>
                                <th>رقم الطلب</th>
                                <th>تاريخ التقييم</th>
                                <th>التقييم</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--  إذا توجد تقييمات -->
                            <?php if ($result && $result->num_rows > 0): ?>
                                    <!-- المرور على جميع التقييمات -->
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                       <!-- اسم المستفيد -->
                                        <td class="bnf-name">
                                            <?php echo htmlspecialchars($row['f_name'] . " " . $row['l_name']); ?>
                                        </td>
                                         <!-- رقم الطلب -->
                                        <td class="req-code">
                                            <?php echo $row['request_id']; ?>
                                        </td>
                                        <!-- تاريخ التقييم -->
                                        <td class="rating-date">
                                            <?php echo date("d-m-Y", strtotime($row['rating_date'])); ?>
                                        </td>
                                        <td>
                                        <!-- نص التقييم -->
                                            <div class="rating-comment">
                                                <?php echo nl2br(htmlspecialchars($row['comment_text'])); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <!--  إذا لا توجد تقييمات -->
                                <tr class="empty-row">
                                    <td colspan="4">لا توجد تقييمات متاحة حاليًا.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>