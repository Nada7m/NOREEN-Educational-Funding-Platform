<?php
session_start();

// 1. التحقق من تسجيل دخول المكتب
if (!isset($_SESSION['office_id'])) {
    header("Location: login.php");
    exit();
}

// 2. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) { die("فشل الاتصال بقاعدة البيانات"); }
$con->set_charset("utf8mb4");

// 3. معرف المكتب
$current_off_id = $_SESSION['office_id'];

// 4. جلب الاستشارات
$sql = "SELECT 
            b.bnf_id, 
            CONCAT(b.f_name, ' ', b.l_name) as bnf_full_name, 
            MAX(m.msg_time) as last_date,
            MAX(CASE WHEN m.sender_type = 'beneficiary' THEN m.msg_time END) as last_bnf_time,
            MAX(CASE WHEN m.sender_type = 'office' THEN m.msg_time END) as last_off_time
        FROM bnf_off_msg m
        INNER JOIN beneficiary b ON m.bnf_id = b.bnf_id
        WHERE m.office_id = '$current_off_id'
        GROUP BY b.bnf_id, bnf_full_name
        ORDER BY last_date DESC";

$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الاستشارات الواردة - نورين</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=5">

<style>
/* مساحة الصفحة */
.content-body{
    padding:30px;
}

/* حاوية الجدول */
.table-wrap{
    max-width:1180px;
    margin:0 auto;
    border:0.5px solid #c5c3c3;
    border-radius:18px;
}

/* كرت الجدول */
.table-card{
    background:#FFFFFF;
    border:1px solid #E8E8E8;
    border-radius:18px;
    box-shadow:0 2px 8px rgba(0,0,0,0.04);
    overflow:hidden;
}

/* الجدول */
.consult-table{
    width:100%;
    border-collapse:collapse;
    background:#FFFFFF;
}

/* رأس الجدول */
.consult-table th{
    background:#FFFFFF;
    color:#3E2454;
    font-size:16px;
    font-weight:700;
    padding:18px 20px;
    text-align:center;
    border-bottom:1px solid #DCDCDC;
}

/* خلايا الجدول */
.consult-table td{
    padding:18px 20px;
    text-align:center;
    font-size:14px;
    color:#555555;
    border-bottom:1px solid #EAEAEA;
    vertical-align:middle;
}

/* إزالة خط آخر صف */
.consult-table tbody tr:last-child td{
    border-bottom:none;
}

/* اسم المستفيد */
.bnf-name{
    font-weight:600;
    color:#6B6B6B;
}

/* التاريخ */
.send-date{
    color:#7A7A7A;
}

/* زر الإجراء */
.btn-action{
    background:#E5E7EB;
    color:#000000;
    padding:10px 22px;
    border-radius:10px;
    text-decoration:none;
    font-size:14px;
    border:1px solid #CCCCCC;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:170px;
    font-weight:600;
}

/* زر وجود رسالة جديدة */
.btn-green-alert{
    background:#D1FAE5 !important;
    color:#065F46 !important;
    border:1px solid #10B981 !important;
    font-weight:700;
}

/* صف عدم وجود بيانات */
.empty-row td{
    padding:40px 20px;
    color:#888888;
}
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
                <li><a href="Con03_Consultations.php" class="active">الاستشارات</a></li>
                <li><a href="Con08_ReqRating.php">تقييمات المستفيدين</a></li>
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
                <div class="page-title"> إدارة الاستشارات و التواصل</div>
                <div class="page-description"> صفحة إدارة طلبات الاستشارات الواردة من المستفيدين</div>
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

                    <table class="consult-table">
                        <thead>
                            <tr>
                                <th>اسم المستفيد</th>
                                <th>تاريخ الإرسال</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    $last_bnf = $row['last_bnf_time'];
                                    $last_off = $row['last_off_time'];
                                    $is_new = (!$last_off || ($last_bnf > $last_off));
                                    $btn_class = $is_new ? "btn-action btn-green-alert" : "btn-action";
                                ?>
                                    <tr>
                                        <td class="bnf-name">
                                            <?php echo htmlspecialchars($row['bnf_full_name']); ?>
                                        </td>

                                        <td class="send-date">
                                            <?php echo date("d-m-Y", strtotime($row['last_date'])); ?>
                                        </td>

                                        <td>
                                            <a href="Con04_BnfContact.php?bnf_id=<?php echo $row['bnf_id']; ?>" class="<?php echo $btn_class; ?>">
                                                عرض محتوى الاستشارة
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr class="empty-row">
                                    <td colspan="3">لا توجد استشارات واردة حاليًا.</td>
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