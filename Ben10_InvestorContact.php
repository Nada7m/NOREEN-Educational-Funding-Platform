<?php
session_start();

// 1. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) {
    die("فشل الاتصال بالقاعدة: " . $con->connect_error);
}
$con->set_charset("utf8mb4");

// 2. المعرفات (تأكدي أن inv_id يصل في الرابط)
$current_bnf_id = $_SESSION['bnf_id'] ?? 0;
$target_inv_id = isset($_GET['inv_id']) ? intval($_GET['inv_id']) : 0; 

// 3. معالجة الإرسال
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_msg']) && $target_inv_id > 0) {
    $text = trim($con->real_escape_string($_POST['message']));
    if(!empty($text)) {
        $sql_insert = "INSERT INTO bnf_inv_msg (bnf_id, inv_id, msg_text, sender_type) 
                       VALUES ('$current_bnf_id', '$target_inv_id', '$text', 'beneficiary')";
        if ($con->query($sql_insert)) {
            header("Location: Ben10_InvestorContact.php?inv_id=" . $target_inv_id);
            exit();
        }
    }
}

// 4. جلب بيانات المستثمر (مع فحص الأمان لمنع الخطأ الذي ظهر لك)
$inv_name = "الجهة المانحة";
if ($target_inv_id > 0) {
    $inv_res = $con->query("SELECT inv_name FROM investor WHERE inv_id = '$target_inv_id'");
    if ($inv_res && $inv_res->num_rows > 0) {
        $inv_row = $inv_res->fetch_assoc();
        $inv_name = $inv_row['inv_name'];
    }
}
// 5. جلب المحادثة
$res_msgs = $con->query("SELECT * FROM bnf_inv_msg WHERE bnf_id = '$current_bnf_id' AND inv_id = '$target_inv_id' ORDER BY msg_time ASC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>متابعة المنح - التواصل</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        :root { --main-purple: #3E2454; --chat-bg: #E5E7EB; }
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background-color: #fff; border-bottom: 1px solid #eee; }
        .page-title { color: #000; font-size: 24px; font-weight: bold; margin: 0; }
        .settings-dropdown { position: relative; }
        .menu-icon-img { width: 30px; height: 30px; cursor: pointer; }
        .dropdown-menu { display: none; position: absolute; left: 0; top: 45px; background: white; border: 1px solid #ddd; border-radius: 8px; width: 200px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .show { display: block !important; }
        
        .chat-container { flex: 1; margin: 20px; padding: 25px; background-color: #F5F5F5; border-radius: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; height: 450px; }
        .msg { max-width: 75%; padding: 15px 20px; background: #FFFFFF; box-shadow: 0 2px 5px rgba(0,0,0,0.05); line-height: 1.8; position: relative; font-size: 14px; border-radius: 15px; }
        
        .from-me { align-self: flex-start; border-right: 6px solid var(--main-purple); } /* يمين */
        .from-them { align-self: flex-end; border-left: 6px solid #999; background-color: #f9f9f9; } /* يسار */
        
        .msg-meta { font-size: 10px; color: #888; display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 5px; }
        .send-area-form { padding: 20px; background: #fff; display: flex; gap: 15px; border-top: 1px solid #eee; align-items: center; }
        .msg-input { flex: 1; padding: 15px; border: 1px solid #ddd; border-radius: 30px; outline: none; }
        .send-icon { width: 50px; cursor: pointer; }
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
                <li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
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
            <div class="page-heading"><h1 class="page-title">متابعة المنح</h1><p class="page-description">صفحة التواصل مع الجهة المانحة</p></div>
            <div class="settings-dropdown">
                <img src="ايقونة قائمة الاعدادات.png" id="menuBtn" class="menu-icon-img">
                <div class="dropdown-menu" id="myDropdown">
                <a href="Ben02_Profile.php">الملف الشخصي</a>
                <a href=".php">محفظة منحتي</a>
                <a href="support.php">تقديم شكوى او استفسار</a></div>
            </div>
        </header>

        <div style="background: #E9DFF1; padding: 12px 25px; margin: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
            <div>تواصل مع : <span style="color: var(--main-purple); font-weight: bold;"><?php echo htmlspecialchars($inv_name); ?></span></div>
            <a href="Ben09_TrackScholarship.php"><img src="سهم تراجع.svg" width="40"></a>
        </div>

        <div class="chat-container">
            <?php 
            // فحص إذا كان استعلام الرسائل نجح قبل البدء في الـ loop
            if($res_msgs && $res_msgs->num_rows > 0): 
                while($row = $res_msgs->fetch_assoc()): 
                    $is_me = ($row['sender_type'] == 'beneficiary'); ?>
                    <div class="msg <?php echo $is_me ? 'from-me' : 'from-them'; ?>">
                        <div class="text"><?php echo nl2br(htmlspecialchars($row['msg_text'])); ?></div>
                        <div class="msg-meta"><span><?php echo date("h:i A", strtotime($row['msg_time'])); ?></span></div>
                    </div>
                <?php endwhile; 
            else: ?>
                <p style="text-align: center; color: #888; margin-top: 50px;">لا توجد رسائل سابقة.</p>
            <?php endif; ?>
        </div>

        <form class="send-area-form" method="POST">
            <input type="text" name="message" class="msg-input" placeholder="اكتب الرسالة هنا..." required autocomplete="off">
            <button type="submit" name="send_msg" style="background:none; border:none; padding:0;"><img src="select box.png" class="send-icon"></button>
        </form>
    </div>
</div>
<script>
    document.getElementById("menuBtn").onclick = function(e) { document.getElementById("myDropdown").classList.toggle("show"); e.stopPropagation(); }
    window.onclick = function() { document.getElementById("myDropdown").classList.remove("show"); }
</script>
</body>
</html>