<?php
session_start();

/* التحقق من تسجيل دخول المستفيد */
if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

$bnf_id = (int) $_SESSION['bnf_id'];

/* تنفيذ الدفع داخل الصفحة */
if (isset($_POST['confirm_payment'])) {

    $request_id = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;

    if ($request_id > 0) {

        $sqlPay = "UPDATE admission_request 
                   SET payment_status = 'مدفوع' 
                   WHERE request_id = $request_id 
                   AND bnf_id = $bnf_id
                   AND request_status = 'مقبول'";

        $conn->query($sqlPay);
    }
}

/* جلب طلبات إصدار القبول الخاصة بالمستفيد */
$sql = "SELECT 
            ar.request_id,
            ar.Submit_date,
            ar.request_status,
            ar.result_notes,
            ar.Result_status,
            ar.payment_status,
            co.office_name
        FROM admission_request ar
        LEFT JOIN consulting_office co ON ar.office_id = co.office_id
        WHERE ar.bnf_id = $bnf_id
        ORDER BY ar.request_id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>طلبات إصدار القبول</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
.page{
    padding:30px;
}

.content-box{
    max-width:1250px;
    margin:0 auto;
    background:#fff;
    border-radius:12px;
    border:0.5px solid #c5c3c3;
    overflow-x:auto;
    padding:0;
}

.requests-table-wrap{
    width:100%;
    overflow-x:auto;
}

.requests-table{
    width:100%;
    min-width:1150px;
    border-collapse:collapse;
    text-align:center;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.requests-table tr:first-child th{
    background:#f8f8f8;
    color:#3E2454;
    font-size:15px;
    font-weight:700;
    padding:14px 10px;
    border-bottom:1px solid #ddd;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.requests-table td{
    padding:14px 10px;
    border-bottom:1px solid #eee;
    font-size:14px;
    color:#333;
    text-align:center;
    font-family:'Noto Kufi Arabic', sans-serif;
    vertical-align:middle;
}

.requests-table tr:last-child td{
    border-bottom:none;
}

.req-code{
    color:#333;
    font-weight:600;
}

.status-box{
    display:inline-block;
    min-width:120px;
    padding:7px 14px;
    border-radius:20px;
    color:#fff;
    font-size:13px;
    font-weight:700;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.status-processing{
    background:#E9BE66;
}

.status-done{
    background:#63B68B;
}

.status-rejected{
    background:#D96C6C;
}

.status-plain{
    background:#FFFFFF;
    color:#444444;
    border:1px solid #DDDDDD;
}

.details-btn{
    display:inline-block;
    padding:8px 18px;
    border:1px solid #999;
    border-radius:10px;
    background:#fff;
    color:#3E2454;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    font-family:'Noto Kufi Arabic', sans-serif;
    transition:0.3s;
    cursor:pointer;
}

.details-btn:hover{
    background:#f4f0f7;
}

.details-btn.disabled{
    background:#E5E5E5;
    color:#9A9A9A;
    border:1px solid #D0D0D0;
    cursor:not-allowed;
    pointer-events:none;
    opacity:0.7;
}

.empty-box{
    text-align:center;
    padding:50px 20px;
    color:#777;
    font-size:16px;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.pay-modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.45);
    z-index:3000;
    justify-content:center;
    align-items:center;
}

.pay-card{
    width:700px;
    max-width:92%;
    background:#fff;
    border-radius:10px;
    padding:30px 24px;
    box-shadow:0 8px 24px rgba(0,0,0,0.18);
}

.pay-title{
    text-align:center;
    color:#d46a6a;
    font-size:22px;
    font-weight:700;
    margin-bottom:18px;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.pay-box{
    width:78%;
    margin:0 auto;
    border:1px solid #e0e0e0;
    border-radius:10px;
    padding:18px;
}

.pay-box-title{
    text-align:center;
    font-size:20px;
    font-weight:700;
    color:#333;
    margin-bottom:16px;
    font-family:'Noto Kufi Arabic', sans-serif;
}

.pay-field{
    margin-bottom:14px;
}

.pay-field input{
    width:100%;
    height:46px;
    border:1px solid #d1d1d1;
    border-radius:6px;
    padding:8px 12px;
    font-size:14px;
    font-family:'Noto Kufi Arabic', sans-serif;
    outline:none;
}

.pay-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
}

.pay-confirm{
    width:100%;
    height:46px;
    border:none;
    border-radius:6px;
    background:#70A0AF;
    color:#fff;
    font-size:18px;
    font-weight:700;
    font-family:'Noto Kufi Arabic', sans-serif;
    cursor:pointer;
    margin-top:10px;
}

.close-btn{
    display:block;
    margin:12px auto 0;
    background:none;
    border:none;
    color:#777;
    font-size:14px;
    font-family:'Noto Kufi Arabic', sans-serif;
    cursor:pointer;
}</style>
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="نورين">
            </div>

            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionRequests.php" class="active">طلبات إصدار القبول</a></li>
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
                <div class="page-title">طلبات إصدار القبول</div>
                <div class="page-description">صفحة عرض ومتابعة طلبات إصدار القبول</div>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page">
            <div class="content-box">

                <?php if ($result && $result->num_rows > 0) { ?>
                    <div class="requests-table-wrap">
                        <table class="requests-table">

                            <tr>
                                <th>رقم الطلب</th>
                                <th>المكتب</th>
                                <th>تاريخ التقديم</th>
                                <th>حالة الطلب</th>
                                <th>حالة النتيجة</th>
                                <th>حالة الدفع</th>
                                <th>الإجراءات</th>
                            </tr>

                            <?php while ($row = $result->fetch_assoc()) { ?>

                                <?php
                                $request_status = trim($row['request_status']);

                                if ($request_status == "" || $request_status == "في الانتظار") {
                                    $requestStatusText = "في الانتظار";
                                    $requestStatusClass = "status-processing";
                                } elseif ($request_status == "مرفوض") {
                                    $requestStatusText = "مرفوض";
                                    $requestStatusClass = "status-rejected";
                                } else {
                                    $requestStatusText = "مقبول";
                                    $requestStatusClass = "status-done";
                                }

                                $result_status = trim($row['Result_status']);

                                if ($request_status == "مرفوض") {
                                    $resultStatusText = "لم تُصدر";
                                    $resultStatusClass = "status-plain";
                                } elseif ($result_status == "" || $result_status == "قيد المعالجة") {
                                    $resultStatusText = "قيد المعالجة";
                                    $resultStatusClass = "status-processing";
                                } elseif ($result_status == "أُصدرت" || $result_status == "أصدرت") {
                                    $resultStatusText = "أُصدرت";
                                    $resultStatusClass = "status-done";
                                } else {
                                    $resultStatusText = $result_status;
                                    $resultStatusClass = "status-done";
                                }

                                $payment_status = trim($row['payment_status']);
                                ?>

                                <tr>
                                    <td class="req-code">UA<?php echo $row['request_id']; ?></td>

                                    <td><?php echo htmlspecialchars($row['office_name']); ?></td>

                                    <td><?php echo htmlspecialchars($row['Submit_date']); ?></td>

                                    <td>
                                        <div class="status-box <?php echo $requestStatusClass; ?>">
                                            <?php echo $requestStatusText; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="status-box <?php echo $resultStatusClass; ?>">
                                            <?php echo $resultStatusText; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if ($payment_status == "مدفوع") { ?>

                                            <div class="status-box status-done">
                                                تم الدفع
                                            </div>

                                        <?php } else { ?>

                                            <?php if ($request_status == "مقبول") { ?>

                                                <button type="button" class="details-btn" onclick="openPaymentModal(<?php echo $row['request_id']; ?>)">
                                                    ادفع الآن
                                                </button>

                                            <?php } else { ?>

                                                <button type="button" class="details-btn disabled">
                                                    ادفع الآن
                                                </button>

                                            <?php } ?>

                                        <?php } ?>
                                    </td>

                                    <td>
                                        <a href="Ben17_AdmissionDetails.php?id=<?php echo $row['request_id']; ?>" class="details-btn">
                                            عرض تفاصيل الطلب
                                        </a>
                                    </td>
                                </tr>

                            <?php } ?>

                        </table>
                    </div>

                <?php } else { ?>
                    <div class="empty-box">
                        لا توجد لديك طلبات إصدار قبول حتى الآن.
                    </div>
                <?php } ?>

            </div>
        </div>

    </div>
</div>

<div class="pay-modal" id="paymentModal">
    <div class="pay-card">
        <div class="pay-title">إتمام عملية الدفع</div>

        <div class="pay-box">
            <div class="pay-box-title">بيانات تأكيد الدفع</div>

            <form method="POST" id="paymentForm">
                <input type="hidden" name="request_id" id="payment_request_id">

                <div class="pay-field">
                    <input type="text" id="cardName" placeholder="NAME ON CARD">
                </div>

                <div class="pay-field">
                    <input type="text" id="cardNumber" placeholder="CARD NUMBER" maxlength="19">
                </div>

                <div class="pay-row">
                    <div class="pay-field">
                        <input type="text" id="expDate" placeholder="MM/YY" maxlength="5">
                    </div>

                    <div class="pay-field">
                        <input type="text" id="cvv" placeholder="CVV" maxlength="4">
                    </div>
                </div>

                <button type="button" class="pay-confirm" onclick="confirmPayment()">اضغط لتأكيد الدفع</button>
                <button type="button" class="close-btn" onclick="closePaymentModal()">إغلاق</button>
                <button type="submit" name="confirm_payment" id="realPaySubmit" style="display:none;"></button>
            </form>
        </div>
    </div>
</div>

<script>
function openPaymentModal(requestId){
    document.getElementById("payment_request_id").value = requestId;
    document.getElementById("paymentModal").style.display = "flex";
}

function closePaymentModal(){
    document.getElementById("paymentModal").style.display = "none";
}

document.getElementById("expDate").addEventListener("input", function(){
    let value = this.value.replace(/[^\d]/g, "");

    if(value.length > 4){
        value = value.substring(0, 4);
    }

    if(value.length >= 3){
        value = value.substring(0, 2) + "/" + value.substring(2);
    }

    this.value = value;
});

function confirmPayment(){
    const cardName = document.getElementById("cardName").value.trim();
    const cardNumber = document.getElementById("cardNumber").value.trim().replace(/\s+/g, "");
    const expDate = document.getElementById("expDate").value.trim();
    const cvv = document.getElementById("cvv").value.trim();

    if(cardName === "" || cardNumber === "" || expDate === "" || cvv === ""){
        alert("يرجى تعبئة جميع بيانات الدفع.");
        return;
    }

    if(!/^\d{12,19}$/.test(cardNumber)){
        alert("رقم البطاقة غير صحيح.");
        return;
    }

    if(!/^\d{2}\/\d{2}$/.test(expDate)){
        alert("صيغة تاريخ الانتهاء يجب أن تكون MM/YY");
        return;
    }

    const parts = expDate.split("/");
    const month = parseInt(parts[0], 10);
    const year = parseInt(parts[1], 10);

    if(month < 1 || month > 12){
        alert("شهر الانتهاء غير صحيح.");
        return;
    }

    const now = new Date();
    const currentMonth = now.getMonth() + 1;
    const currentYear = now.getFullYear() % 100;

    if(year < currentYear || (year === currentYear && month < currentMonth)){
        alert("البطاقة منتهية الصلاحية.");
        return;
    }

    if(!/^\d{3,4}$/.test(cvv)){
        alert("رمز CVV غير صحيح.");
        return;
    }

    document.getElementById("realPaySubmit").click();
}

window.addEventListener("click", function(e){
    const modal = document.getElementById("paymentModal");
    if(e.target === modal){
        modal.style.display = "none";
    }
});
</script>

</body>
</html>