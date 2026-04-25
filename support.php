<?php
session_start();

$con = new mysqli("localhost", "root", "", "noreen");

if (!isset($_SESSION['email'])) {
    die("يرجى تسجيل الدخول أولاً.");
}

$email = $_SESSION['email'];
$user_type = $_SESSION['user_type']; 

$current_id = "";
$column_name = "";
$back_link = "";

if ($user_type == "beneficiary") {
    $res = $con->query("SELECT bnf_id FROM beneficiary WHERE email = '$email'");
    $row = $res->fetch_assoc();
    $current_id = $row['bnf_id'];
    $column_name = "bnf_id";
    $back_link = "Ben00_MainPage.php";

} elseif ($user_type == "investor") {
    $res = $con->query("SELECT inv_id FROM investor WHERE email = '$email'");
    $row = $res->fetch_assoc();
    $current_id = $row['inv_id'];
    $column_name = "inv_id";
    $back_link = "Inv00_MainPage.php";

} elseif ($user_type == "consulting_office") {
    $res = $con->query("SELECT office_id FROM consulting_office WHERE email = '$email'");
    $row = $res->fetch_assoc();
    $current_id = $row['office_id'];
    $column_name = "office_id";
    $back_link = "Con00_MainPage.php";
}

if (isset($_POST['submit_ticket'])) {
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $message = mysqli_real_escape_string($con, $_POST['message']);

    $sql = "INSERT INTO complaints_inquiries ($column_name, submission_date, subject, message, status) 
            VALUES ('$current_id', NOW(), '$subject', '$message', 'بانتظار الرد')";
    
    if ($con->query($sql)) {
        echo "<script>alert('تم إرسال طلبك بنجاح'); window.location.href='support.php';</script>";
        exit();
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : "list";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الدعم والتواصل</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Noto Kufi Arabic', sans-serif;
    background-color:#F4F4F4;
    margin:0;
}

.wrapper{
    max-width:1100px;
    margin:30px auto;
    padding:20px 25px;
}

.page-top{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    margin-bottom:8px;
}

.back-btn-details{
    width:50px;
    height:50px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.back-btn-details img{
    width:40px;
    height:40px;
    display:block;
}

h2{
    font-size:22px;
    font-weight:700;
    color:#472764;
    margin:0 0 18px;
}

.content-box{
    background:#FFFFFF;
    border:1px solid #CFCFCF;
    border-radius:8px;
    padding:28px;
    min-height:400px;
}

.btn-purple{
    background:#472764;
    color:white;
    padding:12px 22px;
    border-radius:6px;
    text-decoration:none;
    border:none;
    cursor:pointer;
    float:left;
    margin-bottom:25px;
    font-size:14px;
    font-weight:700;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.btn-purple:hover{
    background:#3f2556;
}

table{
    width:100%;
    border-collapse:collapse;
    background:#FFFFFF;
    clear:both;
}

th{
    background:#F7F7F7;
    color:#333;
    font-size:14px;
    font-weight:700;
    padding:15px;
    border:1px solid #EEEEEE;
    text-align:center;
}

td{
    color:#555;
    font-size:13.5px;
    padding:15px;
    border:1px solid #EEEEEE;
    text-align:center;
}

.badge{
    padding:7px 16px;
    border-radius:8px;
    font-size:13px;
    font-weight:700;
    display:inline-block;
}

.bg-orange{
    background:#FFF4E5;
    color:#E6BC6A;
}

.bg-green{
    background:#D4F4E2;
    color:#55A082;
}

h3{
    font-size:18px;
    font-weight:700;
    color:#333;
    margin:0 0 20px;
    text-align:center;
    border-bottom:1px solid #f0f0f0;
    padding-bottom:10px;
}

input,
textarea{
    width:100%;
    padding:13px 14px;
    margin-bottom:15px;
    border:1px solid #b8d2dd;
    border-radius:4px;
    font-size:14px;
    font-family:'Noto Kufi Arabic', sans-serif;
    outline:none;
    box-sizing:border-box;
    direction:rtl;
    text-align:right;
}

input:focus,
textarea:focus{
    border-color:#472764;
}

textarea{
    resize:none;
    line-height:1.8;
}

.reset-link{
    color:#EEE;
    font-size:9px;
    text-decoration:none;
    position:absolute;
    bottom:5px;
    right:5px;
}
</style>
</head>

<body>

<div class="wrapper">
<?php
// تحديد رابط الرجوع حسب الصفحة
if($page == "new"){
    $back = "support.php"; // من صفحة الإرسال → يرجع للقائمة
}else{
    $back = $back_link; // من القائمة → يرجع للرئيسية
}
?>

<div class="page-top">
    <a href="<?php echo $back; ?>" class="back-btn-details">
        <img src="سهم تراجع.svg" alt="رجوع">
    </a>
</div>

    <h2>الشكاوى والاستفسارات</h2>

    <div class="content-box">

        <?php if ($page == "list"): ?>

            <a href="support.php?page=new" class="btn-purple">+ تقديم شكوى او استفسار</a>

            <table>
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
                $res = $con->query("SELECT * FROM complaints_inquiries WHERE $column_name = '$current_id' ORDER BY ticket_id DESC");
                while($row = $res->fetch_assoc()):
                    $st = ($row['status'] == 'تم الرد') ? 'bg-green' : 'bg-orange';
                ?>
                    <tr>
                        <td>#<?php echo $row['ticket_id']; ?></td>
                        <td><?php echo $row['submission_date']; ?></td>
                        <td><span class="badge <?php echo $st; ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <a href="support.php?page=view&id=<?php echo $row['ticket_id']; ?>" style="color:#3E2454; font-weight:bold;">
                                عرض الرد
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        <?php elseif ($page == "new"): ?>

            <h3>إرسال تذكرة جديدة</h3>

            <form method="POST">
                <input type="text" name="subject" placeholder="موضوع الرسالة" required>
                <textarea name="message" rows="6" placeholder="اكتب تفاصيل استفسارك هنا..." required></textarea>
                <button type="submit" name="submit_ticket" class="btn-purple" style="float:none; width:100%;">
                    إرسال الآن
                </button>
            </form>

        <?php elseif ($page == "view"): 
            $id = $_GET['id'];
            $data = $con->query("SELECT * FROM complaints_inquiries WHERE ticket_id = '$id'")->fetch_assoc();
        ?>

            <h3>رد الإدارة</h3>

            <div style="background: #f9f9f9; padding: 25px; border-radius: 10px;">
                <p><strong>الموضوع:</strong> <?php echo $data['subject']; ?></p>
                <hr>
                <p><strong>الرد:</strong></p>
                <p style="color: #3E2454; font-weight: bold;">
                    <?php echo !empty($data['admin_reply']) ? nl2br($data['admin_reply']) : "سيتم الرد عليك قريباً."; ?>
                </p>
            </div>

            <br>
            <a href="support.php" class="btn-purple" style="float:none;">رجوع</a>

        <?php endif; ?>

    </div>
</div>

</body>
</html>