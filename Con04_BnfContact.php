<?php
session_start();

// 1. الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen", 3306);
if ($con->connect_error) { die("فشل الاتصال: " . $con->connect_error); }
$con->set_charset("utf8mb4");

// 2. المعرفات
$current_off_id = $_SESSION['office_id'] ?? 2; 
$target_bnf_id = (isset($_GET['bnf_id']) && intval($_GET['bnf_id']) > 0) ? intval($_GET['bnf_id']) : 1; 

// 3. معالجة الإرسال
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_msg'])) {
    $text = trim($con->real_escape_string($_POST['message']));
    if(!empty($text)) {
        $sql_insert = "INSERT INTO bnf_off_msg (bnf_id, office_id, msg_text, sender_type) 
                       VALUES ('$target_bnf_id', '$current_off_id', '$text', 'office')";
        
        if ($con->query($sql_insert)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?bnf_id=" . $target_bnf_id);
            exit();
        }
    }
}

// 4. جلب اسم الطالب
$bnf_name = "الطالب المستفيد";
$bnf_res = $con->query("SELECT f_name, l_name FROM beneficiary WHERE bnf_id = '$target_bnf_id'");
if ($bnf_res && $row_bnf = $bnf_res->fetch_assoc()) {
    $bnf_name = $row_bnf['f_name'] . " " . $row_bnf['l_name'];
}

// 5. جلب المحادثة
$res_msgs = $con->query("SELECT * FROM bnf_off_msg WHERE bnf_id = '$target_bnf_id' AND office_id = '$current_off_id' ORDER BY msg_time ASC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نورين - التواصل مع المستفيد</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">
    <style>
        :root { --main-purple: #3E2454; --chat-bg: #F5F5F5; }
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        
        .chat-container { flex: 1; margin: 20px; padding: 25px; background-color: var(--chat-bg) !important; border-radius: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; height: 400px; }
        .msg { max-width: 75%; padding: 15px 20px; background: #FFFFFF; box-shadow: 0 2px 5px rgba(0,0,0,0.05); line-height: 1.8; position: relative; font-size: 14px; border-radius: 15px; }
        .from-me { align-self: flex-start; border-right: 6px solid var(--main-purple); }
        .from-them { align-self: flex-end; border-left: 6px solid #999; background-color: #f9f9f9; }
        .msg-meta { font-size: 10px; color: #888; display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 5px; }
        
        .send-area-form { padding: 20px; background: #fff; display: flex; gap: 15px; border-top: 1px solid #eee; align-items: center; }
        .msg-input { flex: 1; padding: 15px; border: 1px solid #ddd; border-radius: 30px; outline: none; }
        .send-icon { width: 50px; cursor: pointer; }

        .settings-dropdown { position: relative; display: inline-block; }
        .dropdown-menu { display: none; position: absolute; left: 0; top: 100%; background: white; border: 1px solid #ddd; border-radius: 8px; width: 190px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .dropdown-menu a { display: block; padding: 12px 15px; text-decoration: none; color: #333; font-size: 13px; }
      
        .settings-dropdown:hover .dropdown-menu { display: block; }
    </style>
</head>

<body>
  <div class="layout">
    <aside class="sidebar">
      <div class="sidebar-top">
        <div class="sidebar-logo"><img src="شعار نورين.png"></div>
        <ul class="sidebar-menu">
          <li><a href="Con00_MainPage.php">الرئيسية</a></li>
          <li><a href="Con0_AdmissionRequests.php">إدارة طلبات القبول</a></li>
          <li><a href="Con0_Consultations.php" class="active">الاستشارات</a></li>
          <li><a href="Con0_BeneficiaryRatings.php">تقييمات المستفيدين</a></li>
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
      <header class="header" style="background:#fff; display:flex; justify-content:space-between; padding:15px 30px; border-bottom:1px solid #eee;">
        <div class="page-heading">
          <h1 class="page-title" style="margin:0; font-size: 24px;">إدارة الاستشارات والتواصل</h1>
          <p class="page-description" style="margin:5px 0 0; font-size: 14px; color: #000000;">صفحة اداره طلبات الأستشارات</p>
        </div>
        <div class="settings-dropdown">
            <img src="ايقونة قائمة الاعدادات.png" width="30" style="cursor:pointer;">
            <div class="dropdown-menu">
              <a href="Con02_Profile.php">الملف الشخصي</a>
              <a href="support.php">تقديم شكوى او استفسار</a>
            </div>
        </div>
      </header>

      <div style="background: #E9DFF1; padding: 12px 25px; margin: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
          <div>تواصل مع المستفيد : <span style="color: var(--main-purple); font-weight: bold;"><?php echo htmlspecialchars($bnf_name); ?></span></div>
          <a href="Con03_Consultations.php"><img src="سهم تراجع.svg" width="40"></a>
      </div>

      <div class="chat-container">
          <?php if($res_msgs && $res_msgs->num_rows > 0): 
              while($row = $res_msgs->fetch_assoc()): 
                  $is_me = ($row['sender_type'] == 'office'); ?>
                  <div class="msg <?php echo $is_me ? 'from-me' : 'from-them'; ?>">
                      <div class="text"><?php echo nl2br(htmlspecialchars($row['msg_text'])); ?></div>
                      <div class="msg-meta"><span><?php echo date("h:i A", strtotime($row['msg_time'])); ?></span></div>
                  </div>
              <?php endwhile; 
          endif; ?>
      </div>

      <form class="send-area-form" method="POST">
          <input type="text" name="message" class="msg-input" placeholder="اكتب الرد هنا..." required autocomplete="off">
          <button type="submit" name="send_msg" style="background:none; border:none; padding:0;">
              <img src="select box.png" class="send-icon">
          </button>
      </form>
    </div>
  </div>
</body>
</html>

