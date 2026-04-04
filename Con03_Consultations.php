<?php
session_start();

// 1. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) { die("فشل الاتصال: " . $con->connect_error); }
$con->set_charset("utf8mb4");

// 2. معرف المكتب (يؤخذ من السيشون)
$current_off_id = $_SESSION['office_id'] ?? 2; 

// 3. الاستعلام الذكي لمقارنة التوقيت (بدون حقل is_read)
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
    <link rel="stylesheet" href="CSS01Layout.css?v=3">
    <style>
        :root { --main-purple: #3E2454; }
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        
        /* الهيدر */
        .header { background-color: #FFFFFF; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; padding: 15px 30px; align-items: center; }
        
        /* الجدول */
        .content-body { padding: 20px 30px; }
        .table-container { background: #fff; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fdfdfd; padding: 20px; text-align: right; border-bottom: 2px solid #eee; color: var(--main-purple); font-weight: 700; }
        td { padding: 20px; text-align: right; border-bottom: 1px solid #f0f0f0; color: #444; }
        
        /* الأزرار */
        .btn-action { 
            background-color: #E5E7EB; color: #000; padding: 8px 20px; border-radius: 8px; 
            text-decoration: none; font-size: 13px; border: 1px solid #ccc; 
            display: inline-flex; align-items: center; gap: 10px; transition: 0.3s;
        }

        /* الزر الأخضر الفاتح للرسائل الجديدة */
        .btn-green-alert { 
            background-color: #D1FAE5 !important; 
            color: #065F46 !important; 
            border: 1px solid #10B981 !important; 
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
            <ul class="sidebar-menu">
                <li><a href="Con00_MainPage.php">الرئيسية</a></li>
                <li><a href="Con0_AdmissionRequests.php">إدارة طلبات القبول</a></li>
                <li><a href="Con03_Consultations.php" class="active">الاستشارات</a></li>
                <li><a href="Con0_BeneficiaryRatings.php">تقييمات المستفيدين</a></li>
            </ul>
        </div>

        <div class="sidebar-bottom">
            <form action="logout.php" method="post" style="width: 100%; border: none; background: none; padding: 0;">
                <button type="submit" class="logout-btn" style="cursor: pointer; border: none; background: none; width: 100%; text-align: inherit;">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content" style="flex:1;">
        <header class="header">
            <div class="page-heading">
                <div class="page-title" style="font-size: 24px; font-weight: bold; color: var(--main-purple);">إدارة الاستشارات والتواصل</div>
                 <p class="page-description" style="margin:5px 0 0; font-size: 14px; color: #000000;">صفحة اداره طلبات الأستشارات</p>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" style="width: 30px; cursor: pointer;">
                    <div class="dropdown-menu">
                        <a href="Con02_Profile.php">الملف الشخصي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

         <div class="page">
            <div class="back-wrap" style="display: flex; justify-content: flex-end;">
    <a href="Con00_MainPage.php?id=<?php echo $sch_id; ?>" class="back-btn">
        <img src="سهم تراجع.svg" class="back-icon" alt="رجوع" style="width: 45px; height: 45px;">
    </a>
</div>

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
</body>
</html>