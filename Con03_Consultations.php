<?php
session_start();

// 1. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) { die("فشل الاتصال: " . $con->connect_error); }
$con->set_charset("utf8mb4");

// 2. معرف المكتب
$current_off_id = $_SESSION['office_id'] ?? 2; 

// 3. الاستعلام
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
        /* تنسيقات الجدول */
        .content-body { padding: 24px 30px; }
        .page-description{ width:100%; direction:rtl; text-align:right;}
        .table-wrap { max-width: 1000px; margin: 0 auto; }
        .table-container {; width: 100%; background: #FFFFFF; border: 1px solid #EAEAEA; border-radius: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; }
        table { border:0.5px solid #c5c3c3; width: 100%;  background: #FFFFFF; }
        th { background: #FFFFFF; padding: 22px 26px; text-align: center; border-bottom: 1px solid #EAEAEA; color: #3E2454; font-size: 16px; font-weight: 700; }
        td { padding: 24px 26px; text-align: center; border-bottom: 1px solid #F0F0F0; color: #444444; font-size: 15px; }
        tbody tr:last-child td { border-bottom: none; }
        .name-cell { font-weight: 600; }
        .btn-action { background-color: #E5E7EB; color: #000000; padding: 10px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; border: 1px solid #CCCCCC; display: inline-flex; align-items: center; justify-content: center; gap: 10px; font-weight: 600; }
        .btn-green-alert { background-color: #D1FAE5 !important; color: #065F46 !important; border: 1px solid #10B981 !important; font-weight: 700; }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
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
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content">
        <header class="header" >
            <div class="page-heading">
                <div class="page-title" style="font-size: 24px; font-weight: bold; color: #000000;">إدارة الاستشارات والتواصل</div>
                 <p class="page-description" style="color: #000000;">صفحة إدارة طلبات الاستشارات</p>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" style="width: 30px; cursor: pointer;">
                    <div class="dropdown-menu">
                        <a href="Con02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى أو استفسار</a>
                    </div>
                </div>
            </div>
        </header>
            <div class="content-body">
                <div class="table-container">
                    <table>
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
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($row['bnf_full_name']); ?></td>
                                        <td><?php echo date("d-m-Y", strtotime($row['last_date'])); ?></td>
                                        <td>
                                            <a href="Con04_BnfContact.php?bnf_id=<?php echo $row['bnf_id']; ?>" class="<?php echo $btn_class; ?>">
                                                عرض محتوى الاستشارة
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align: center; padding: 20px;">لا توجد استشارات واردة حالياً.</td></tr>
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