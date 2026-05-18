<?php
session_start();

/* الاتصال بقاعدة البيانات */
$con = new mysqli("localhost", "root", "", "noreen");

/* ضبط الترميز لدعم اللغة العربية */
$con->set_charset("utf8mb4");

/* الحصول على رقم المستثمر الحالي */
$current_inv_id = $_SESSION['inv_id'] ?? 0;
/* الحصول على رقم المستفيد */
$target_bnf_id = isset($_GET['bnf_id']) ? intval($_GET['bnf_id']) : 0; 

/** التحقق من إرسال النموذج **/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_msg'])) {

    /* تنظيف الرسالة */
    $text = trim($con->real_escape_string($_POST['message']));

    /** التحقق من أن الرسالة غير فارغة **/
    if(!empty($text)) {

        /* إرسال الرسالة كمستثمر */
        $sql_insert = "INSERT INTO bnf_inv_msg (bnf_id, inv_id, msg_text, sender_type) 
                       VALUES ('$target_bnf_id', '$current_inv_id', '$text', 'investor')";

        /* تنفيذ الاستعلام */
        $con->query($sql_insert);

        /* إعادة التوجيه بعد الإرسال */
        header("Location: Inv08_ContactBeneficiary.php?bnf_id=" . $target_bnf_id);
        exit();
    }
}

/* جلب بيانات المستفيد */
$user_data = $con->query("SELECT f_name, l_name FROM beneficiary WHERE bnf_id = '$target_bnf_id'")->fetch_assoc();

/* تجهيز الاسم الكامل */
$full_name = $user_data ? $user_data['f_name'] . " " . $user_data['l_name'] : "المستفيد";

/* جلب جميع الرسائل */
$res_msgs = $con->query("SELECT * FROM bnf_inv_msg WHERE bnf_id = '$target_bnf_id' AND inv_id = '$current_inv_id' ORDER BY msg_time ASC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المنحة - المحادثة</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        :root { --main-purple: #3E2454; --chat-bg: #E5E7EB; }
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        .page-title { color: #000; font-size: 24px; font-weight: bold; margin: 0; }
        .page-description { color: #000; font-size: 14px; margin: 5px 0 0 0; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background-color: #fff; border-bottom: 1px solid #eee; }
        .settings-dropdown { position: relative; }
        .menu-icon-img { width: 30px; height: 30px; cursor: pointer; }
        .dropdown-menu { display: none; position: absolute; left: 0; top: 45px; background: white; border: 1px solid #ddd; border-radius: 8px; width: 180px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .dropdown-menu a { display: block; padding: 10px 15px; text-decoration: none; color: #333; font-size: 14px; border-bottom: 1px solid #eee; }
        .show { display: block !important; }
        .status-header { background: #E9DFF1; padding: 12px 25px; margin: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; }
        .student-name { color: var(--main-purple); font-weight: bold; }
        .chat-container { flex: 1; margin: 0 20px; padding: 25px; background-color: #F5F5F5; border-radius: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; height: 450px; }
        .msg { max-width: 75%; padding: 15px 20px; background: #FFFFFF; box-shadow: 0 2px 5px rgba(0,0,0,0.05); line-height: 1.8; position: relative; font-size: 14px; border-radius: 15px; }
        
        /* منطق الاتجاهات للمستثمر */
        .from-me { align-self: flex-start; border-right: 6px solid var(--main-purple); } /* يمين */
        .from-them { align-self: flex-end; border-left: 6px solid #999; background-color: #f9f9f9; } /* يسار */
        .msg-meta { font-size: 10px; color: #888; display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 5px; }
        .sidebar-bottom { margin-top: auto; padding: 25px; text-align: center; }
        .logout-btn-custom { background-color: #F3E6DD; border: none; border-radius: 50px; padding: 8px 18px; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; flex-direction: row-reverse; gap: 8px; font-weight: bold; color: #000; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
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
                <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
                <li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
                <li><a href="Inv06_ManageScholarships.php" class="active">إدارة المنح</a></li>
<li><a href="Inv10_Payments.php">المدفوعات</a></li>
            </ul>
        </div>
        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn-custom"><span>تسجيل الخروج</span><img src="ايقونة تسجيل الخروج.png" width="18"></button>
            </form>
        </div>
    </aside>

    <div class="main-content">
        <header class="header">
            <div class="page-heading"><h1 class="page-title">إدارة المنح</h1><p class="page-description">صفحة التواصل مع المرشح المعني بالمنحة</p></div>
            <div class="settings-dropdown">
                <img src="ايقونة قائمة الاعدادات.png" id="menuBtn" class="menu-icon-img">
                <div class="dropdown-menu" id="myDropdown"><a href="Inv02_Profile.php">الملف الشخصي</a><a href="support.php">تقديم شكوى او استفسار</a></div>
            </div>
        </header>
        <div class="status-header">
            <div>صفحة التواصل مع : <span class="student-name"><?php echo $full_name; ?></span></div>
            <a href="Inv06_ManageScholarships.php"><img src="سهم تراجع.svg" width="40"></a>
        </div>
        <div class="chat-container">
            <?php while($row = $res_msgs->fetch_assoc()): 
                // المستثمر هو صاحب الصفحة
                $is_me = ($row['sender_type'] == 'investor'); ?>
                <div class="msg <?php echo $is_me ? 'from-me' : 'from-them'; ?>">
                    <div class="text"><?php echo nl2br(htmlspecialchars($row['msg_text'])); ?></div>
                    <div class="msg-meta"><span><?php echo date("h:i A", strtotime($row['msg_time'])); ?></span></div>
                </div>
            <?php endwhile; ?>
        </div>
        <form class="send-area-form" method="POST">
            <input type="text" name="message" class="msg-input" placeholder="اكتب الرسالة هنا..." required>
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