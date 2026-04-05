<?php
session_start();

// 1. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) { die("فشل الاتصال: " . $con->connect_error); }
$con->set_charset("utf8mb4");

// 2. المعرفات
$current_bnf_id = $_SESSION['bnf_id'] ?? 1; 
// القيمة الافتراضية أصبحت 2 للتجربة
$target_off_id = (isset($_GET['off_id']) && intval($_GET['off_id']) > 0) ? intval($_GET['off_id']) : 2; 

// 3. معالجة الإرسال
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_msg'])) {
    $text = trim($con->real_escape_string($_POST['message']));
    if(!empty($text)) {
        $sql_insert = "INSERT INTO bnf_off_msg (bnf_id, office_id, msg_text, sender_type) 
                       VALUES ('$current_bnf_id', '$target_off_id', '$text', 'beneficiary')";
        if ($con->query($sql_insert)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?off_id=" . $target_off_id);
            exit();
        }
    }
}

// 4. جلب بيانات المكتب (اسم الجدول consulting_office)
$off_name = "المكتب الاستشاري";
$off_res = $con->query("SELECT office_name FROM consulting_office WHERE office_id = '$target_off_id'");
if ($off_res && $off_row = $off_res->fetch_assoc()) {
    $off_name = $off_row['office_name'];
}

// 5. جلب المحادثة
$res_msgs = $con->query("SELECT * FROM bnf_off_msg WHERE bnf_id = '$current_bnf_id' AND office_id = '$target_off_id' ORDER BY msg_time ASC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تواصل مع المكتب الاستشاري</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        :root { --main-purple: #3E2454; --chat-bg: #F5F5F5; }
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        
        .header { 
            background-color: #FFFFFF !important; 
            border-bottom: 1px solid #eee; 
            display: flex; 
            justify-content: space-between; 
            padding: 15px 30px; 
            align-items: center;
        }

        .chat-container { flex: 1; margin: 20px; padding: 25px; background-color: var(--chat-bg) !important; border-radius: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; height: 400px; }
        .msg { max-width: 75%; padding: 15px 20px; background: #FFFFFF; box-shadow: 0 2px 5px rgba(0,0,0,0.05); line-height: 1.8; position: relative; font-size: 14px; border-radius: 15px; }
        .from-me { align-self: flex-start; border-right: 6px solid var(--main-purple); }
        .from-them { align-self: flex-end; border-left: 6px solid #999; background-color: #f9f9f9; }
        .msg-meta { font-size: 10px; color: #888; display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 5px; }
        
        .send-area-form { padding: 20px; background: #fff; display: flex; gap: 15px; border-top: 1px solid #eee; align-items: center; }
        .msg-input { flex: 1; padding: 15px; border: 1px solid #ddd; border-radius: 30px; outline: none; }
        .send-icon { width: 50px; cursor: pointer; }

        .settings-dropdown { position: relative; display: inline-block; }
        .dropdown-menu { 
            display: none; position: absolute; left: 0; top: 100%; background: white; 
            border: 1px solid #ddd; border-radius: 8px; width: 190px; z-index: 1000; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .dropdown-menu a { display: block; padding: 12px 15px; text-decoration: none; color: #333; font-size: 13px; }
        .dropdown-menu a:hover { background-color: #f3f3f3; color: var(--main-purple); }
        .settings-dropdown:hover .dropdown-menu { display: block; }
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
                <li><a href="Ben013_ConsultingOffices.php" class="active">المكاتب الاستشارية</a></li>
                <li><a href="Ben15_AdmissionReq.php">طلبات إصدار القبول</a></li>
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
                <h1 class="page-title" style="margin:0; font-size: 24px;">المكاتب الاستشارية</h1>
                <p class="page-description">صفحة التواصل مع المكتب الاستشاري</p>
            </div>
            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" width="30" class="menu-icon" style="cursor:pointer;">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                                      <a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى أو استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div style="background: #E9DFF1; padding: 12px 25px; margin: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
            <div>التواصل مع مكتب : <span style="color: var(--main-purple); font-weight: bold;"><?php echo htmlspecialchars($off_name); ?></span></div>
<a href="javascript:history.back()">
    <img src="سهم تراجع.svg" width="40">
</a>        </div>

        <div class="chat-container">
            <?php if($res_msgs && $res_msgs->num_rows > 0): 
                while($row = $res_msgs->fetch_assoc()): 
                    $is_me = ($row['sender_type'] == 'beneficiary'); ?>
                    <div class="msg <?php echo $is_me ? 'from-me' : 'from-them'; ?>">
                        <div class="text"><?php echo nl2br(htmlspecialchars($row['msg_text'])); ?></div>
                        <div class="msg-meta"><span><?php echo date("h:i A", strtotime($row['msg_time'])); ?></span></div>
                    </div>
                <?php endwhile; 
            endif; ?>
        </div>

        <form class="send-area-form" method="POST">
            <input type="text" name="message" class="msg-input" placeholder="اكتب الرسالة هنا..." required autocomplete="off">
            <button type="submit" name="send_msg" style="background:none; border:none; padding:0;">
                <img src="select box.png" class="send-icon">
            </button>
        </form>
    </div>
</div>
</body>
</html>