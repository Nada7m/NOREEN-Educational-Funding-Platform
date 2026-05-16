<?php
/* بدء الجلسة */
session_start();

/** التحقق من تسجيل دخول المستفيد **/
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

/* رقم المستفيد */
$bnf_id = (int) $_SESSION['bnf_id'];

/* تنفيذ الدفع */
if (isset($_POST['confirm_payment'])) {

    $request_id = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;

    if ($request_id > 0) {

        /* تحديث حالة الدفع */
        $sqlPay = "UPDATE admission_request 
                   SET payment_status = 'مدفوع' 
                   WHERE request_id = $request_id 
                   AND bnf_id = $bnf_id
                   AND request_status = 'مقبول'";

        $conn->query($sqlPay);
    }
}

/* جلب الطلبات */
$sql = "SELECT 
            ar.request_id,
            ar.Submit_date,
            ar.request_status,
            ar.result_notes,
            ar.Result_status,
            ar.payment_status,
            ar.program_type,
            co.office_name,
            co.Bachelor_fee,
            co.Masters_fee,
            co.Phd_fee
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
/* الصفحة */
.page{ padding:30px; }

/* الصندوق */
.content-box{ max-width:1250px; margin:0 auto; background:#fff; border-radius:12px; border:0.5px solid #c5c3c3; overflow-x:auto; padding:0; }

/* تغليف الجدول */
.requests-table-wrap{ width:100%; overflow-x:auto; }

/* الجدول */
.requests-table{ width:100%; min-width:1150px; border-collapse:collapse; text-align:center; font-family:'Noto Kufi Arabic',sans-serif; }

/* رأس الجدول */
.requests-table tr:first-child th{ background:#f8f8f8; color:#3E2454; font-size:15px; font-weight:700; padding:14px 10px; border-bottom:1px solid #ddd; font-family:'Noto Kufi Arabic',sans-serif; }

/* خلايا الجدول */
.requests-table td{ padding:14px 10px; border-bottom:1px solid #eee; font-size:14px; color:#333; text-align:center; font-family:'Noto Kufi Arabic',sans-serif; vertical-align:middle; }

/* آخر صف */
.requests-table tr:last-child td{ border-bottom:none; }

/* رقم الطلب */
.req-code{ color:#333; font-weight:600; }

/* الحالة */
.status-box{ display:inline-block; min-width:120px; padding:7px 14px; border-radius:20px; color:#fff; font-size:13px; font-weight:700; font-family:'Noto Kufi Arabic',sans-serif; }

/* قيد المعالجة */
.status-processing{ background:#E9BE66; }

/* مكتمل */
.status-done{ background:#63B68B; }

/* مرفوض */
.status-rejected{ background:#D96C6C; }

/* حالة عادية */
.status-plain{ background:#FFFFFF; color:#444444; border:1px solid #DDDDDD; }

/* زر التفاصيل */
.details-btn{ display:inline-block; padding:8px 18px; border:1px solid #999; border-radius:10px; background:#fff; color:#3E2454; text-decoration:none; font-size:13px; font-weight:600; font-family:'Noto Kufi Arabic',sans-serif; transition:0.3s; cursor:pointer; }

/* مرور على الزر */
.details-btn:hover{ background:#f4f0f7; }

/* زر معطل */
.details-btn.disabled{ background:#E5E5E5; color:#9A9A9A; border:1px solid #D0D0D0; cursor:not-allowed; pointer-events:none; opacity:0.7; }

/* لا توجد بيانات */
.empty-box{ text-align:center; padding:50px 20px; color:#777; font-size:16px; font-family:'Noto Kufi Arabic',sans-serif; }

/* المودال */
.modal{ display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); justify-content:center; align-items:center; z-index:3000; }

/* محتوى المودال */
.modal-content{ width:700px; max-width:92%; background:#FFFFFF; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.18); padding:28px 34px; }

/* عنوان المودال */
.modal-title{ text-align:center; font-size:20px; font-weight:700; color:#4a2b63; margin-bottom:26px; }

/* نموذج الدفع */
.pay-form{ display:flex; flex-direction:column; gap:20px; }

/* صف الدفع */
.pay-row{ display:flex; gap:20px; }

/* الحقل */
.pay-field{ flex:1; }

/* عنوان الحقل */
.pay-field label{ display:block; margin-bottom:8px; font-size:15px; font-weight:600; color:#333333; }

/* الإدخال */
.pay-field input{ width:100%; height:52px; border:1px solid #cccccc; border-radius:10px; padding:10px 14px; font-size:15px; outline:none; font-family:"Noto Kufi Arabic",sans-serif; box-sizing:border-box; }

/* زر التأكيد */
.confirm-pay-btn{ display:inline-block; width:100%; padding:12px 0; border:1px solid #999; border-radius:10px; background:#fff; color:#3E2454; text-decoration:none; font-size:15px; font-weight:700; font-family:'Noto Kufi Arabic',sans-serif; transition:0.3s; cursor:pointer; text-align:center; }

/* مرور على زر التأكيد */
.confirm-pay-btn:hover{ background:#f4f0f7; }

/* زر الإلغاء */
.close-modal-btn{ display:inline-block; width:100%; padding:10px 0; border:1px solid #999; border-radius:10px; background:#fff; color:#3E2454; text-decoration:none; font-size:14px; font-weight:600; font-family:'Noto Kufi Arabic',sans-serif; transition:0.3s; cursor:pointer; text-align:center; }

/* مرور على زر الإلغاء */
.close-modal-btn:hover{ background:#f4f0f7; }

/* صندوق الرسوم */
.fee-box{ background:linear-gradient(to left, #3E2454, #A48BB5); color:#FFFFFF; border-radius:14px; height:60px; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700; }
</style>
</head>

<body>

<!-- التخطيط -->
<div class="layout">

    <!-- القائمة الجانبية -->
    <aside class="sidebar">

        <div class="sidebar-top">

            <!-- الشعار -->
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="نورين">
            </div>

            <!-- روابط القائمة -->
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionRequests.php" class="active">طلبات إصدار القبول</a></li>
                <li><a href="Ben19_Consultations.php">الاستشارات</a></li>
            </ul>
        </div>

        <!-- تسجيل الخروج -->
        <div class="sidebar-bottom">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <!-- المحتوى -->
    <div class="main-content">

        <!-- الهيدر -->
        <header class="header">

            <div class="page-heading">
                <div class="page-title">طلبات إصدار القبول</div>
                <div class="page-description">صفحة عرض ومتابعة طلبات إصدار القبول</div>
            </div>

            <!-- الإعدادات -->
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

        <!-- الصفحة -->
        <div class="page">

            <!-- الصندوق -->
            <div class="content-box">

                <?php if ($result && $result->num_rows > 0) { ?>

                    <!-- الجدول -->
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
                                /* حالة الطلب */
                                $request_status = trim($row['request_status']);

                                if ($request_status == "" || $request_status == "في الانتظار") {
                                    $requestStatusText = "في الانتظار";
                                    $requestStatusClass = "status-processing";
                                } elseif ($request_status == "مرفوض") {
                                    $requestStatusText = "مرفوض";
                                    $requestStatusClass = "status-rejected";
                                } else {
                                    $requestStatusText = "مقبول";
                                    $requestStatusClass = "status-done"; }

                                /* حالة النتيجة */
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
                                    $resultStatusClass = "status-done"; }

                                /* حالة الدفع */
                                $payment_status = trim($row['payment_status']);
                                $program_type = trim($row['program_type']);

                                /* رسوم البرنامج */
                                if ($program_type == "bachelor") {
                                    $feeAmount = $row['Bachelor_fee'];
                                } elseif ($program_type == "master") {
                                    $feeAmount = $row['Masters_fee'];
                                } else {
                                    $feeAmount = $row['Phd_fee']; }
                                ?>

                                <tr>

                                    <!-- رقم الطلب -->
                                    <td class="req-code">UA<?php echo $row['request_id']; ?></td>

                                    <!-- المكتب -->
                                    <td><?php echo htmlspecialchars($row['office_name']); ?></td>

                                    <!-- التاريخ -->
                                    <td><?php echo htmlspecialchars($row['Submit_date']); ?></td>

                                    <!-- حالة الطلب -->
                                    <td>
                                        <div class="status-box <?php echo $requestStatusClass; ?>">
                                            <?php echo $requestStatusText; ?>
                                        </div>
                                    </td>

                                    <!-- حالة النتيجة -->
                                    <td>
                                        <div class="status-box <?php echo $resultStatusClass; ?>">
                                            <?php echo $resultStatusText; ?>
                                        </div>
                                    </td>

                                    <!-- حالة الدفع -->
                                    <td>

                                        <?php if ($payment_status == "مدفوع") { ?>

                                            <div class="status-box status-done">
                                                تم الدفع
                                            </div>

                                        <?php } else { ?>

                                            <?php if ($request_status == "مقبول") { ?>

                                                <button type="button" class="details-btn" onclick="openPaymentModal(<?php echo $row['request_id']; ?>, '<?php echo $feeAmount; ?>')">
                                                    ادفع الآن
                                                </button>

                                            <?php } else { ?>

                                                <button type="button" class="details-btn disabled">
                                                    ادفع الآن
                                                </button>

                                            <?php } ?>

                                        <?php } ?>

                                    </td>

                                    <!-- الإجراءات -->
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

                    <!-- لا توجد طلبات -->
                    <div class="empty-box">
                        لا توجد لديك طلبات إصدار قبول حتى الآن.
                    </div>

                <?php } ?>

            </div>
        </div>

    </div>
</div>

<!-- المودال -->
<div class="modal" id="paymentModal">

    <div class="modal-content">

        <div class="modal-title">رجاء قم بإدخال بيانات البطاقة لسداد رسوم البرنامج</div>

        <!-- نموذج الدفع -->
        <form method="POST" class="pay-form" id="paymentForm">

            <input type="hidden" name="request_id" id="payment_request_id">

            <!-- الرسوم -->
            <div class="fee-box" id="payment_fee"></div>

            <!-- اسم البطاقة -->
            <div class="pay-field">
                <label>الاسم على البطاقة</label>
                <input type="text" id="cardName" maxlength="100">
            </div>

            <!-- رقم البطاقة -->
            <div class="pay-field">
                <label>رقم البطاقة</label>
                <input type="text" id="cardNumber" maxlength="16" inputmode="numeric">
            </div>

            <div class="pay-row">

                <!-- تاريخ الانتهاء -->
                <div class="pay-field">
                    <label>تاريخ الانتهاء</label>
                    <input type="text" id="expDate" placeholder="MM/YY" maxlength="5" inputmode="numeric">
                </div>

                <!-- رمز الأمان -->
                <div class="pay-field">
                    <label>رمز الأمان</label>
                    <input type="text" id="cvv" maxlength="3" inputmode="numeric">
                </div>

            </div>

            <!-- زر التأكيد -->
            <button type="button" class="confirm-pay-btn" onclick="confirmPayment()">تأكيد السداد</button>

            <!-- زر الإلغاء -->
            <button type="button" class="close-modal-btn" onclick="closePaymentModal()">إلغاء</button>

            <!-- زر الإرسال -->
            <button type="submit" name="confirm_payment" id="realPaySubmit" style="display:none;"></button>

        </form>

    </div>
</div>

<script>
/* فتح المودال */
function openPaymentModal(requestId, feeAmount){
    document.getElementById("payment_request_id").value = requestId;
    document.getElementById("payment_fee").innerText = "رسوم الطلب: " + feeAmount + " ريال";
    document.getElementById("paymentModal").style.display = "flex";}

/* إغلاق المودال */
function closePaymentModal(){
    document.getElementById("paymentModal").style.display = "none";}

/* رقم البطاقة */
document.getElementById("cardNumber").addEventListener("input", function(){
    this.value = this.value.replace(/\D/g, "").slice(0, 16);});

/* رمز الأمان */
document.getElementById("cvv").addEventListener("input", function(){
    this.value = this.value.replace(/\D/g, "").slice(0, 3);});

/* تاريخ الانتهاء */
document.getElementById("expDate").addEventListener("input", function(){
    let value = this.value.replace(/\D/g, "").slice(0, 4);
    this.value = value.length >= 3 ? value.slice(0, 2) + "/" + value.slice(2) : value;});

/* التحقق من الدفع */
function confirmPayment(){
    const cardName = document.getElementById("cardName").value.trim();
    const cardNumber = document.getElementById("cardNumber").value.trim();
    const expDate = document.getElementById("expDate").value.trim();
    const cvv = document.getElementById("cvv").value.trim();

    /** التحقق من الحقول **/
    if(cardName === "" || cardNumber === "" || expDate === "" || cvv === ""){
        alert("يرجى تعبئة جميع بيانات الدفع.");
        return; }

    /** التحقق من الاسم **/
    if(!/^[\u0600-\u06FFa-zA-Z\s]{3,100}$/.test(cardName)){
        alert("اسم حامل البطاقة غير صحيح.");
        return;}

    /** التحقق من رقم البطاقة **/
    if(!/^\d{16}$/.test(cardNumber)){
        alert("رقم البطاقة يجب أن يكون 16 رقم.");
        return;}

    /** التحقق من التاريخ **/
    if(!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expDate)){
        alert("تاريخ الانتهاء يجب أن يكون بالشكل MM/YY.");
        return; }

    /** التحقق من CVV **/
    if(!/^\d{3}$/.test(cvv)){
        alert("رمز الأمان يجب أن يكون 3 أرقام.");
        return; }

    document.getElementById("realPaySubmit").click();}

/* إغلاق عند الضغط بالخارج */
window.addEventListener("click", function(e){
    const modal = document.getElementById("paymentModal");
      if(e.target === modal){ modal.style.display = "none";}});
</script>

</body>
</html>