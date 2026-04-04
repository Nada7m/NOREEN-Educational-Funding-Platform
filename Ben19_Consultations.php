<?php
session_start();

// 1. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) { die("فشل الاتصال بالقاعدة"); }
$con->set_charset("utf8mb4");

// 2. معرف المستخدم (تلقائي من السيشون)
$current_bnf_id = $_SESSION['bnf_id'] ?? 2; 

// 3. الاستعلام الذكي لمقارنة توقيت آخر رسالة بين المستفيد والمكتب
$sql = "SELECT 
            co.office_id, 
            co.office_name, 
            MAX(m.msg_time) as last_date,
            /* جلب وقت آخر رسالة أرسلها المكتب */
            MAX(CASE WHEN m.sender_type = 'office' THEN m.msg_time END) as last_off_time,
            /* جلب وقت آخر رسالة أرسلها المستفيد (أنتِ) */
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
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        :root { --main-purple: #3E2454; }
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        
        .header { background-color: #FFFFFF; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; padding: 15px 30px; align-items: center; }

        /* الجدول */
        .content-body { padding: 20px 30px; }
        .table-container { background: #fff; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fdfdfd; padding: 20px; text-align: right; border-bottom: 2px solid #eee; color: var(--main-purple); font-weight: 700; }
        td { padding: 20px; text-align: right; border-bottom: 1px solid #f0f0f0; color: #444; }

        /* الأزرار والحالات */
        .btn-action {
            background-color: #E5E7EB; color: #000; padding: 8px 20px; border-radius: 8px; 
            text-decoration: none; font-size: 13px; border: 1px solid #ccc; 
            display: inline-flex; align-items: center; gap: 10px; transition: 0.3s;
        }

        /* اللون الأخضر الفاتح عند وجود رد جديد من المكتب */
        .btn-green-alert { 
            background-color: #D1FAE5 !important; 
            color: #065F46 !important; 
            border: 1px solid #10B981 !important; 
            font-weight: bold;
        }

        /* قائمة الإعدادات */
        .settings-dropdown { position: relative; }
        .dropdown-menu { display: none; position: absolute; left: 0; top: 100%; background: white; border: 1px solid #ddd; border-radius: 8px; width: 190px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .settings-dropdown:hover .dropdown-menu { display: block; }
        .dropdown-menu a { display: block; padding: 12px 15px; text-decoration: none; color: #333; font-size: 13px; text-align: right; }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben15_AdmissionRequests.php">طلبات إصدار القبول</a></li>
                <li><a href="Ben19_Consultations.php" class="active">الاستشارات</a></li>
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

    <div class="main-content" style="flex:1;">
        <header class="header">
            <div class="page-heading">
                <h1 style="margin:0; font-size: 24px;">الاستشارات</h1>
                 <p class="page-description" style="margin:5px 0 0; font-size: 14px; color: #000000;">صفحة تتضمن جميع الاستشارات التي تم تقديمها للمكاتب الاستشاراية</p>
            </div>
            <div class="settings-dropdown">
                <img src="ايقونة قائمة الاعدادات.png" width="30" style="cursor:pointer;">
                <div class="dropdown-menu">
                    <a href="Ben02_Profile.php">الملف الشخصي</a>
                    <a href="support.php">تقديم  شكوى او استفسار</a>
                </div>
            </div>
        </header>

        <div class="content-body">
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
                            <?php while($row = $result->fetch_assoc()): 
                                // المنطق: إذا كان وقت رسالة المكتب أحدث من رسالتك (أو لم ترسلي شيئاً بعد)، يظهر الأخضر
                                $last_off = $row['last_off_time'];
                                $last_bnf = $row['last_bnf_time'];
                                
                                $is_new_reply = (!$last_bnf || ($last_off > $last_bnf));

                                $btn_class = $is_new_reply ? "btn-action btn-green-alert" : "btn-action";
                                $btn_text = $is_new_reply ? "لديك رد جديد" : "عرض محتوى الاستشارة";
                            ?>
                                <tr>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($row['office_name']); ?></td>
                                    <td><?php echo date("d-m-Y", strtotime($row['last_date'])); ?></td>
                                    <td>
                                        <a href="Ben18_ContactConsultingOffice.php?off_id=<?php echo $row['office_id']; ?>" class="<?php echo $btn_class; ?>">
                                            <?php echo $btn_text; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; padding: 20px;">لا توجد استشارات سابقة.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>