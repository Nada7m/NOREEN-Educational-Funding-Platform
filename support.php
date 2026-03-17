<?php
session_start();

// 1. الاتصال المباشر بقاعدة بيانات نورين
$con = mysqli_connect("localhost", "root", "", "noreen");

// 2. التحقق من الاتصال
if (!$con) {
    die("خطأ في الاتصال بالقاعدة: " . mysqli_connect_error());
}

// 3. جلب بيانات المستخدم
$email = isset($_SESSION['email']) ? $_SESSION['email'] : ""; 
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : "beneficiary"; 

// تحديد صفحة العودة
$back_link = "Ben00_MainPage.php";
if($user_type == "investor") $back_link = "Inv00_MainPage.php";
if($user_type == "consultant") $back_link = "Con00_MainPage.php";

$page = isset($_GET['page']) ? $_GET['page'] : "";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نورين - التواصل والدعم</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Kufi Arabic', sans-serif; background-color: #F8F9FA; margin: 0; padding: 0; }
        .wrapper { max-width: 1100px; margin: 20px auto; padding: 20px; position: relative; }
        
        /*زر التراجع */
        .back-nav-container { position: absolute; left: 40px; top: 40px; }
        .back-circle { 
            width: 45px; 
            height: 45px; 
            background-color: #D6B7E2; /* هوية نورين */
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-decoration: none; 
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(62, 36, 84, 0.2);
        }
        .back-circle:hover { background-color: #3E2454; transform: scale(1.1); }
        .back-circle svg { width: 24px; height: 24px; fill: white; transform: rotate(180deg); } /* سهم يشير لليمين لكنه باليسار */

        /* قسم العنوان مستند لليمين */
        .page-header { margin-bottom: 30px; padding-right: 10px; }
        .page-title { color: #000; font-size: 26px; font-weight: bold; margin: 0; }
        .page-subtitle { color: #666; font-size: 14px; margin-top: 8px; max-width: 80%; }

        /* الصندوق الأبيض */
        .content-box { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 40px; border: 1px solid #EAEAEA; min-height: 450px; }

        /* زر تقديم شكوى  */
        .btn-purple { background-color: #3E2454; color: white; padding: 12px 28px; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; gap: 10px; float: left; margin-bottom: 25px; transition: 0.3s; }
        .btn-purple:hover { background-color: #5a357a; }

        /* الجدول */
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .custom-table th { padding: 15px; background-color: #FBFCFD; border: 1px solid #EEEEEE; color: #333; font-size: 14px; text-align: center; }
        .custom-table td { padding: 18px; border: 1px solid #EEEEEE; text-align: center; font-size: 13px; color: #555; }

        /* الحالات */
        .badge { padding: 6px 20px; border-radius: 25px; color: white; font-size: 12px; font-weight: bold; display: inline-block; min-width: 90px; }
        .badge-green { background-color: #76b893; } 
        .badge-orange { background-color: #f2cc8f; }

        .btn-outline { border: 1px solid #CCCCCC; padding: 6px 18px; border-radius: 20px; color: #444; text-decoration: none; font-size: 12px; }

        /* فورم التقديم */
        .form-container { max-width: 650px; margin: 0 auto; }
        .input-text, .input-textarea { width: 100%; padding: 15px; border: 1px solid #DDD; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; font-family: inherit; font-size: 14px; background: #FAFAFA; }
        .input-textarea { height: 160px; resize: none; }

        /* صندوق الرد */
        .reply-section { background: #FDFDFD; border: 1px solid #F0F0F0; padding: 25px; border-radius: 10px; margin-top: 20px; text-align: right; }
        .reply-title { font-weight: bold; color: #3E2454; display: block; margin-bottom: 12px; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="back-nav-container">
        <a href="<?php echo $back_link; ?>" class="back-circle">
            <svg viewBox="0 0 24 24">
                <path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/>
            </svg>
        </a>
    </div>

    <div class="page-header">
        <h1 class="page-title">الشكاوى والاستفسارات</h1>
        <p class="page-subtitle">يمكنك هنا متابعة جميع تذاكرك والاطلاع على حالتها والردود المقدمة من إدارة النظام.</p>
    </div>

    <div class="content-box">

        <?php if ($page == ""): // --- الواجهة 1: الجدول --- ?>
            <div style="overflow: hidden;">
                <a href="support.php?page=new" class="btn-purple">+ تقديم شكوى او استفسار</a>
            </div>
            
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>رقم التذكرة</th>
                        <th>تاريخ الإرسال</th>
                        <th>حالة الرد</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = "SELECT * FROM Complaints_Inquiries WHERE sender_id='$email' ORDER BY submission_date DESC";
                    $result = mysqli_query($con, $query);
                    if($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)): 
                            $status_class = ($row['status'] == 'تم الرد') ? 'badge-green' : 'badge-orange';
                    ?>
                    <tr>
                        <td>TKT-<?php echo $row['ticket_id']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['submission_date'])); ?></td>
                        <td><span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span></td>
                        <td><a href="support.php?page=details&id=<?php echo $row['ticket_id']; ?>" class="btn-outline">عرض الرد</a></td>
                    </tr>
                    <?php endwhile; } else { echo "<tr><td colspan='4' style='padding:50px; color:#999;'>لا توجد بيانات حالياً</td></tr>"; } ?>
                </tbody>
            </table>

        <?php elseif ($page == "new"): // --- الواجهة 2: تقديم طلب --- ?>
            <div class="form-container">
                <h3 style="text-align: center; margin-bottom: 30px; color: #3E2454;">تقديم شكوى او استفسار جديد</h3>
                <form method="POST">
                    <input type="text" name="subject" class="input-text" placeholder="اكتب عنوان الموضوع هنا..." required>
                    <textarea name="message" class="input-textarea" placeholder="أدخل تفاصيل شكواك أو استفسارك أو اقتراحك هنا..." required></textarea>
                    <button type="submit" name="submit_ticket" class="btn-purple" style="float: none; width: 100%; justify-content: center;">إرسال التذكرة</button>
                </form>
            </div>
            <?php
            if(isset($_POST['submit_ticket'])){
                $subj = mysqli_real_escape_string($con, $_POST['subject']);
                $msg = mysqli_real_escape_string($con, $_POST['message']);
                mysqli_query($con, "INSERT INTO Complaints_Inquiries (sender_id, submission_date, subject, message, status) VALUES ('$email', NOW(), '$subj', '$msg', 'بانتظار الرد')");
                echo "<script>window.location.href='support.php';</script>";
            }
            ?>

        <?php elseif ($page == "details"): // --- الواجهة 3: عرض الرد من المدير --- ?>
            <?php 
            $id = mysqli_real_escape_string($con, $_GET['id']);
            $data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM Complaints_Inquiries WHERE ticket_id='$id'"));
            ?>
            <div class="form-container">
                <h3 style="text-align: center; color: #3E2454; margin-bottom: 25px;">تفاصيل التذكرة</h3>
                <div class="reply-section">
                    <span class="reply-title">موضوع التذكرة</span>
                    <p style="color:#666; font-size:14px;"><?php echo $data['subject']; ?></p>
                </div>
                <div class="reply-section">
                    <span class="reply-title">الرد على تذكرتك</span>
                    <p style="color:#444; font-size:14px; line-height:1.6;">
                        <?php 
                        // جلب الرد من عمود admin_reply بقاعدة بيانات نورين
                        echo !empty($data['admin_reply']) ? nl2br($data['admin_reply']) : "طلبك قيد المراجعة حالياً، سنرد عليك قريباً."; 
                        ?>
                    </p>
                </div>
                <div style="margin-top: 30px; text-align: center;">
                    <a href="support.php" class="btn-purple" style="float: none; width: 140px; justify-content: center;">إغلاق</a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>