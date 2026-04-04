<?php
session_start();

// 1. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) { die("فشل الاتصال: " . $con->connect_error); }
$con->set_charset("utf8mb4");

// 2. المعرفات (معرفك كطالبة هو 2)
$current_bnf_id = $_SESSION['bnf_id'] ?? 2; 

// 3. استقبال رقم المكتب من الرابط (سواء من Ben14 أو Ben19)
if (isset($_GET['off_id'])) {
    $target_off_id = intval($_GET['off_id']);
} else {
    header("Location: Ben19_Consultations.php"); // إذا ما فيه رقم مكتب يرجع للجدول
    exit();
}

// 4. تحديث الرسائل لتصبح مقروءة فوراً
$con->query("UPDATE bnf_off_msg SET is_read = 1 
             WHERE bnf_id = '$current_bnf_id' AND office_id = '$target_off_id' AND sender_type = 'office'");

// 5. جلب بيانات المكتب
$off_res = $con->query("SELECT office_name FROM consulting_office WHERE office_id = '$target_off_id'");
$office = $off_res->fetch_assoc();

// 6. معالجة الإرسال
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_msg'])) {
    $text = trim($con->real_escape_string($_POST['message']));
    if(!empty($text)) {
        $con->query("INSERT INTO bnf_off_msg (bnf_id, office_id, msg_text, sender_type, is_read) 
                     VALUES ('$current_bnf_id', '$target_off_id', '$text', 'beneficiary', 0)");
        header("Location: Ben18_ContactConsultingOffice.php?off_id=" . $target_off_id);
        exit();
    }
}

// 7. جلب المحادثة
$res_msgs = $con->query("SELECT * FROM bnf_off_msg WHERE bnf_id = '$current_bnf_id' AND office_id = '$target_off_id' ORDER BY msg_time ASC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تواصل مع <?php echo $office['office_name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        :root { --main-purple: #3E2454; }
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        .header { background-color: #FFFFFF; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; padding: 15px 30px; align-items: center; }
        .chat-container { flex: 1; margin: 20px; padding: 25px; background-color: #F5F5F5; border-radius: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; height: 450px; }
        .msg { max-width: 75%; padding: 12px 18px; border-radius: 15px; font-size: 14px; line-height: 1.6; }
        .from-me { align-self: flex-start; background-color: #FFFFFF; border-right: 6px solid var(--main-purple); }
        .from-them { align-self: flex-end; background-color: #f0f0f0; border-left: 6px solid #999; }
        .send-area { padding: 20px; display: flex; gap: 15px; border-top: 1px solid #eee; }
        .msg-input { flex: 1; padding: 15px; border: 1px solid #ddd; border-radius: 30px; outline: none; }
        .settings-dropdown { position: relative; }
        .dropdown-menu { display: none; position: absolute; left: 0; top: 100%; background: white; border: 1px solid #ddd; border-radius: 8px; width: 180px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .settings-dropdown:hover .dropdown-menu { display: block; }
        .dropdown-menu a { display: block; padding: 10px 15px; text-decoration: none; color: #333; font-size: 13px; text-align: right; }
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
            <form action="logout.php" method="post" style="width: 100%;">
                <button type="submit" class="logout-btn" style="cursor:pointer; width: 100%; border: none; background: none; display: flex; align-items: center; gap: 10px; padding: 20px;">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <span style="color: white; font-weight: bold;">تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content" style="flex:1;">
        <header class="header">
            <div class="page-heading">
                <h1 style="margin:0; font-size: 24px;">المكاتب الاستشارية</h1>
                <p style="margin:5px 0 0; font-size: 14px; color: #666;">صفحة التواصل مع المكتب الاستشاري</p>
            </div>
            <div class="settings-dropdown">
                <img src="ايقونة قائمة الاعدادات.png" width="30" style="cursor:pointer;">
                <div class="dropdown-menu">
                    <a href="Ben02_Profile.php">الملف الشخصي</a>
                    <a href="support.php">تقديم شكوى</a>
                </div>
            </div>
        </header>

        <div style="background: #E9DFF1; padding: 12px 25px; margin: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
            <div>تواصل مع : <span style="color: var(--main-purple); font-weight: bold;"><?php echo htmlspecialchars($office['office_name']); ?></span></div>
            <a href="Ben19_Consultations.php"><img src="سهم تراجع.svg" width="40"></a>
        </div>

        <div class="chat-container" id="chatContainer">
            <?php while($row = $res_msgs->fetch_assoc()): 
                $is_me = ($row['sender_type'] == 'beneficiary'); ?>
                <div class="msg <?php echo $is_me ? 'from-me' : 'from-them'; ?>">
                    <?php echo htmlspecialchars($row['msg_text']); ?>
                    <div style="font-size: 10px; color: #888; margin-top: 5px;"><?php echo date("h:i A", strtotime($row['msg_time'])); ?></div>
                </div>
            <?php endwhile; ?>
        </div>

        <form class="send-area" method="POST">
            <input type="text" name="message" class="msg-input" placeholder="اكتب الرسالة هنا..." required autocomplete="off">
            <button type="submit" name="send_msg" style="background:none; border:none; cursor:pointer;">
                <img src="select box.png" width="50">
            </button>
        </form>
    </div>
</div>
<script>
    var chat = document.getElementById("chatContainer");
    chat.scrollTop = chat.scrollHeight;
</script>
</body>
</html>