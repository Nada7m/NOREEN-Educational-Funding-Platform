<?php
session_start();

/* التحقق من دخول الأدمن */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");

/* حفظ الرد وتحديث حالة التذكرة */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ticket_id'])) {
    $ticket_id = (int)$_POST['ticket_id'];
    $reply = trim($_POST['admin_reply']);
    if ($ticket_id > 0 && $reply != "") {

        $stmt = mysqli_prepare($con, "
            UPDATE complaints_inquiries
            SET admin_reply = ?, status = 'تم الرد'
            WHERE ticket_id = ?  ");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $reply, $ticket_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);    }  }

    header("Location: Admin5_Complaints.php?tab=replied");  exit();}

/* تحديد التبويب الحالي */
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
if ($tab == 'replied') {
    $status_filter = "تم الرد"; } else {
    $status_filter = "بانتظار الرد";
    $tab = 'pending';}

/* جلب التذاكر */
$sql = "
SELECT
    c.ticket_id,
    c.subject,
    c.message,
    c.submission_date,
    c.status,
    c.admin_reply,
    c.bnf_id,
    c.inv_id,
    c.office_id,
    CASE
        WHEN c.bnf_id IS NOT NULL THEN CONCAT(b.f_name, ' ', b.l_name)
        WHEN c.inv_id IS NOT NULL THEN i.inv_name
        WHEN c.office_id IS NOT NULL THEN o.office_name
        ELSE 'غير معروف'
    END AS sender_name
FROM complaints_inquiries c
LEFT JOIN beneficiary b ON c.bnf_id = b.bnf_id
LEFT JOIN investor i ON c.inv_id = i.inv_id
LEFT JOIN consulting_office o ON c.office_id = o.office_id
WHERE c.status = '$status_filter'
ORDER BY c.ticket_id DESC
";
$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>الشكاوى والاستفسارات</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS_AdminLayout.css?v=3">

<style>

/* الحاوية العامة */
.page-wrapper{ padding:40px; }

/* صندوق التبويبات */
.tabs-box{ display:flex; width:100%; max-width:1050px; margin:0 auto 20px; border:1px solid #D9D9D9; border-radius:4px; overflow:hidden; background:#E9E9E9; }

/* زر التبويب */
.tab-btn{ flex:1; text-align:center; padding:14px 10px; font-size:15px; font-weight:600; color:#3E2454; background:#FFFFFF; border-left:1px solid #D0D0D0; transition:.2s; text-decoration:none; }

/* التبويب النشط */
.tab-btn.active{ background:#F2F2F2; }

/* كرت الجدول */
.permissions-card{ width:100%; max-width:1050px; margin:0 auto; background:#FFFFFF; border:1px solid #D8D8D8; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden; }

/* الجدول */
.permissions-table{ width:100%; border-collapse:collapse; text-align:center; table-layout:fixed; }

/* صف العناوين */
.table-head th{ color:#3E2454; font-size:15px; font-weight:700; padding:16px 10px; background:#FBFBFB; border-bottom:1px solid #CFCFCF; }

/* خلايا الجدول */
.permissions-table td{ color:#595959; font-size:14px; font-weight:500; padding:18px 10px; background:#FFFFFF; vertical-align:middle; border-bottom:1px solid #D9D9D9; }

/* عنوان التذكرة */
.ticket-subject{ line-height:1.9; word-break:break-word; }

/* زر عام */
.btn{ border:none; padding:10px 18px; border-radius:12px; cursor:pointer; font-size:13px;  font-weight:600; display:inline-block; min-width:130px; text-align:center; text-decoration:none; }

/* زر خارجي */
.btn-outline{ background:#FFFFFF; color:#3E2454; border:1px solid #8F8F8F; }

/* صفوف فارغة */
.empty-row td{ height:62px; background:#FFFFFF; border-bottom:1px solid #D9D9D9; }

/* لا توجد بيانات */
.no-data{ color:#8D8D8D; font-size:14px; padding:25px 10px !important; }


/* خلفية المودال */
.reply-modal{ display:none; position:fixed; inset:0; z-index:9999; justify-content:center; align-items:center; }

/* صندوق المودال */
.reply-modal-content{ width:760px; max-width:92%; background:#FFFFFF; border-radius:10px; padding:28px 26px 24px; box-shadow:0 6px 24px rgba(0,0,0,0.18); position:relative; direction:rtl; }

/* زر الإغلاق */
.close-modal{ position:absolute; top:14px; left:16px; background:transparent; border:none; font-size:28px; color:#3E2454; cursor:pointer; font-family:inherit; }

/* عنوان المودال */
.reply-title{ color:#3E2454; font-size:18px; font-weight:700; margin-bottom:18px; text-align:right; }

/* عنوان القسم */
.section-label{ color:#000000; font-size:16px; font-weight:700; margin-bottom:12px; text-align:right; }

/* صندوق الشكوى */
.ticket-message-box{ background:#F0F0F0; border-radius:8px; padding:22px 24px; color:#3E2454; font-size:15px; line-height:2; margin-bottom:24px; min-height:110px; }

/* حقل الرد */
.reply-textarea{ width:100%; min-height:160px; border:1px solid #70A0AF; border-radius:0; outline:none; resize:none; padding:16px 18px; font-size:15px; color:#3E2454; background:#FFFFFF; box-sizing:border-box; margin-bottom:24px; }

/* نص placeholder */
.reply-textarea::placeholder{ color:#C9C9C9; }

/* صندوق الرد المعروض */
.reply-view-box{ width:100%; min-height:50px; border:1px solid #70A0AF; padding:16px 18px;  font-size:15px; color:#3E2454; background:#FFFFFF; box-sizing:border-box; line-height:2; margin-bottom:24px; }

/* زر إرسال الرد */
.send-reply-btn{ display:block; width:320px; max-width:100%; margin:0 auto; background:#3E2454; color:#FFFFFF; border:none; border-radius:4px; padding:14px 20px; font-size:16px; font-weight:700; cursor:pointer; }

</style>
</head>
<body>

<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-logo">
        <img src="شعار نورين بنفسجي.svg" alt="شعار نورين">
      </div>

      <ul class="sidebar-menu">
        <li><a href="Admin2_EntitiesApproval.php">اعتماد الجهات</a></li>
        <li><a href="Admin3_Contracts.php">إدارة العقود</a></li>
        <li><a href="Admin4_UsersManage.php">إدارة المستخدمين</a></li>
        <li><a href="Admin5_Complaints.php" class="active">الشكاوى والاستفسارات</a></li>
      </ul>
    </div>

    <div class="sidebar-bottom">
      <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">
          <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
          تسجيل الخروج
        </button>
      </form>
    </div>
  </aside>

  <div class="main-content">

    <header class="header">
      <div class="page-heading">
        <div class="page-title">الشكاوى والاستفسارات</div>
        <div class="page-description">عرض التذاكر الواردة ومتابعة الردود الإدارية عليها</div>
      </div>

      <div class="header-left">
        <a href="Admin1_profile.php" class="profile-btn">لوحة التحكم</a>
      </div>
    </header>

    <div class="page">
      <div class="page-wrapper">

        <div class="tabs-box">
          <a href="?tab=replied" class="tab-btn <?php echo ($tab == 'replied') ? 'active' : ''; ?>">تم الرد</a>
          <a href="?tab=pending" class="tab-btn <?php echo ($tab == 'pending') ? 'active' : ''; ?>">بانتظار الرد</a>
        </div>

        <div class="permissions-card">
          <table class="permissions-table">

            <tr class="table-head">
              <th>رقم التذكرة</th>
              <th>المرسل</th>
              <th>تاريخ الإرسال</th>
              <th>العنوان</th>
              <th>الإجراءات</th>
            </tr>

            <?php
            /* عرض التذاكر */
            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
            ?>
            <tr>
              <td><?php echo "TKT-" . str_pad($row['ticket_id'], 3, "0", STR_PAD_LEFT); ?></td>
              <td><?php echo htmlspecialchars($row['sender_name']); ?></td>
              <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
              <td class="ticket-subject"><?php echo htmlspecialchars($row['subject']); ?></td>
              <td>
                <?php
                /* في التذاكر بانتظار الرد يفتح المودال مع خانة الرد */
                if ($tab == 'pending') {
                ?>
                <button
                  type="button"
                  class="btn btn-outline"
                  onclick='openReplyModal(<?php echo json_encode([
                    "mode" => "pending",
                    "message" => $row["message"],
                    "raw_id" => $row["ticket_id"]
                  ], JSON_UNESCAPED_UNICODE); ?>)'>
                  الرد على التذكرة
                </button>
                <?php
                /* في التذاكر المردود عليها يفتح نفس المودال مع الرد المحفوظ */
                } else {
                ?>
                <button
                  type="button"
                  class="btn btn-outline"
                  onclick='openReplyModal(<?php echo json_encode([
                    "mode" => "replied",
                    "message" => $row["message"],
                    "admin_reply" => $row["admin_reply"]
                  ], JSON_UNESCAPED_UNICODE); ?>)'>
                  عرض
                </button>
                <?php } ?>
              </td>
            </tr>
            <?php
                }
            } else {
            ?>
            <tr>
              <td colspan="5" class="no-data">لا توجد تذاكر في هذا القسم</td>
            </tr>
            <tr class="empty-row"><td colspan="5"></td></tr>
            <tr class="empty-row"><td colspan="5"></td></tr>
            <tr class="empty-row"><td colspan="5"></td></tr>
            <?php } ?>

          </table>
        </div>

      </div>
    </div>

  </div>
</div>

<div class="reply-modal" id="replyModal">
  <div class="reply-modal-content">

    <button class="close-modal" type="button" onclick="closeReplyModal()">×</button>

    <div class="reply-title" id="modalTitle">الرد على التذكرة</div>

    <div class="section-label">الشكوى / الاستفسار</div>
    <div class="ticket-message-box" id="message"></div>

    <form method="post" id="replyForm">
      <input type="hidden" name="ticket_id" id="rawId">

      <div class="section-label">الرد الإداري</div>
      <textarea name="admin_reply" class="reply-textarea" id="replyTextarea" placeholder="أدخل ردك هنا .." required></textarea>

      <div class="reply-view-box" id="replyViewBox" style="display:none;"></div>

      <button type="submit" class="send-reply-btn" id="sendReplyBtn">إرسال الرد</button>
    </form>

  </div>
</div>

<script>
function openReplyModal(data){
  document.getElementById("message").textContent = data.message;

  if(data.mode === "pending"){
    document.getElementById("modalTitle").textContent = "الرد على التذكرة";
    document.getElementById("rawId").value = data.raw_id;
    document.getElementById("replyTextarea").value = "";
    document.getElementById("replyTextarea").style.display = "block";
    document.getElementById("replyViewBox").style.display = "none";
    document.getElementById("sendReplyBtn").style.display = "block";
    document.getElementById("replyTextarea").required = true;
  }else{
    document.getElementById("modalTitle").textContent = "عرض الرد";
    document.getElementById("rawId").value = "";
    document.getElementById("replyTextarea").style.display = "none";
    document.getElementById("replyViewBox").style.display = "block";
    document.getElementById("replyViewBox").textContent = data.admin_reply ? data.admin_reply : "لا يوجد رد";
    document.getElementById("sendReplyBtn").style.display = "none";
    document.getElementById("replyTextarea").required = false;
  }

  document.getElementById("replyModal").style.display = "flex";
}

function closeReplyModal(){
  document.getElementById("replyModal").style.display = "none";
}

window.onclick = function(e){
  let modal = document.getElementById("replyModal");
  if(e.target === modal){
    modal.style.display = "none";
  }
}
</script>

</body>
</html>