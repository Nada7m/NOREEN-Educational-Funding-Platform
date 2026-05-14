<?php
session_start();
//  الاتصال بالقاعدة
$con = new mysqli("localhost", "root", "", "noreen");
if ($con->connect_error) { die("فشل الاتصال: " . $con->connect_error); }
$con->set_charset("utf8mb4");
// جلب معرف المكتب الحالي
$current_off_id = $_SESSION['office_id'] ?? 0; 
// جلب معرف المستفيد من الرابط
$target_bnf_id = (isset($_GET['bnf_id']) && intval($_GET['bnf_id']) > 0) ? intval($_GET['bnf_id']) : 0; 
// معالجة إرسال الرسالة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_msg'])) {
 // تنظيف النص المدخل
    $text = trim($con->real_escape_string($_POST['message']));
// التحقق أن الرسالة ليست فارغة
    if(!empty($text)) {
        // إدخال الرسالة في قاعدة البيانات
        $sql_insert = "INSERT INTO bnf_off_msg (bnf_id, office_id, msg_text, sender_type) 
                       VALUES ('$target_bnf_id', '$current_off_id', '$text', 'office')";
        if ($con->query($sql_insert)) {
             // تحديث الصفحة بعد الإرسال
            header("Location: " . $_SERVER['PHP_SELF'] . "?bnf_id=" . $target_bnf_id);
            exit();
        } }}
// جلب اسم المستفيد
// اسم افتراضي
$bnf_name = "المستفيد المستفيد";
// استعلام جلب الاسم
$bnf_res = $con->query("SELECT f_name, l_name FROM beneficiary WHERE bnf_id = '$target_bnf_id'");
// إذا تم العثور على بيانات
if ($bnf_res && $row_bnf = $bnf_res->fetch_assoc()) {
        // دمج الاسم الأول والأخير
    $bnf_name = $row_bnf['f_name'] . " " . $row_bnf['l_name'];}
// جلب جميع رسائل المحادثة
$res_msgs = $con->query("SELECT * FROM bnf_off_msg WHERE bnf_id = '$target_bnf_id' AND office_id = '$current_off_id' ORDER BY msg_time ASC");?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نورين - التواصل مع المستفيد</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css?v=6">
    <style>
        /*   الألوان الرئيسية */
        :root { --main-purple: #3E2454; --chat-bg: #F5F5F5; }
      /* تنسيق الصفحة */
        body { background-color: #FFFFFF; margin: 0; font-family: 'Noto Kufi Arabic', sans-serif; }
        /* الهيكل الرئيسي */
        .layout { display: flex; min-height: 100vh; }
          /* صندوق المحادثة*/
        .chat-container { flex: 1; margin: 20px; padding: 25px; background-color: var(--chat-bg) !important; border-radius: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; height: 400px; }
          /* شكل الرسالة*/
        .msg { max-width: 75%; padding: 15px 20px; background: #FFFFFF; box-shadow: 0 2px 5px rgba(0,0,0,0.05); line-height: 1.8; position: relative; font-size: 14px; border-radius: 15px; }
        /*   رسائل المكتب*/
        .from-me { align-self: flex-start; border-right: 6px solid var(--main-purple); }
          /* رسائل المستفيد*/
        .from-them { align-self: flex-end; border-left: 6px solid #999; background-color: #f9f9f9; }
         /*  معلومات الرسالة*/
        .msg-meta { font-size: 10px; color: #888; display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 5px; }
          /* منطقة إرسال الرسالة*/
        .send-area-form { padding: 20px; background: #fff; display: flex; gap: 15px; border-top: 1px solid #eee; align-items: center; }
        /* حقل الكتابة */
        .msg-input { flex: 1; padding: 15px; border: 1px solid #ddd; border-radius: 30px; outline: none; }
        /* أيقونة الإرسال */
        .send-icon { width: 50px; cursor: pointer; }
        /* قائمة الإعدادات*/
        .settings-dropdown { position: relative; display: inline-block; }
        /* القائمة المنسدلة */
        .dropdown-menu { display: none; position: absolute; left: 0; top: 100%; background: white; border: 1px solid #ddd; border-radius: 8px; width: 190px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        /* روابط القائمة */
        .dropdown-menu a { display: block; padding: 12px 15px; text-decoration: none; color: #333; font-size: 13px; }
      /* إظهار القائمة عند المرور */
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
                        // التحقق هل الرسالة من المكتب 
                  $is_me = ($row['sender_type'] == 'office'); ?>
                  <!-- الرسالة -->
                  <div class="msg <?php echo $is_me ? 'from-me' : 'from-them'; ?>">
                        <!-- نص الرسالة -->
                      <div class="text"><?php echo nl2br(htmlspecialchars($row['msg_text'])); ?></div>
                          <!-- وقت الرسالة -->
                      <div class="msg-meta"><span><?php echo date("h:i A", strtotime($row['msg_time'])); ?></span></div>
                  </div>
              <?php endwhile; 
          endif; ?>
      </div>
                          <!--  نموذج إرسال رسالة -->
      <form class="send-area-form" method="POST">
                    <!-- حقل كتابة الرسالة -->
          <input type="text" name="message" class="msg-input" placeholder="اكتب الرد هنا..." required autocomplete="off">
                      <!-- زر الإرسال -->
          <button type="submit" name="send_msg" style="background:none; border:none; padding:0;">
              <img src="select box.png" class="send-icon">
          </button>
      </form>
    </div>
  </div>
</body>
</html>