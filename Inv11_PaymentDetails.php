<?php
session_start();

/* التحقق من تسجيل دخول المستثمر */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* دعم العربية */
mysqli_set_charset($con, "utf8mb4");

/* رقم المستثمر الحالي */
$inv_id = $_SESSION['inv_id'];

/* التحقق من وجود رقم الطلب */
if (!isset($_GET['request_id']) || $_GET['request_id'] == "") {
    die("رقم الطلب غير موجود.");
}

$request_id = (int)$_GET['request_id'];

/* رسائل الصفحة */
$msg = "";
$type = "";

/* =====================================================
   1) اعتماد تقرير دفعة معينة
   الاعتماد هنا مرتبط بالدفعة نفسها من جدول academic_report
===================================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["approve_report"])) {

    $report_id = (int)$_POST["report_id"];

    /* التأكد أن التقرير يخص هذا المستثمر */
    $check_sql = "SELECT academic_report.report_id
                  FROM academic_report
                  INNER JOIN e_contract
                    ON academic_report.contract_id = e_contract.contract_id
                  INNER JOIN scholarship_requests
                    ON e_contract.request_id = scholarship_requests.request_id
                  INNER JOIN scholarship_opps
                    ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
                  WHERE academic_report.report_id = ?
                  AND scholarship_requests.request_id = ?
                  AND scholarship_opps.inv_id = ?";

    $check_stmt = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "iii", $report_id, $request_id, $inv_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {

        /* تحديث حالة اعتماد التقرير
           استخدمت القيمة "معتمد"
           إذا كانت القيمة الفعلية في جدولك مختلفة بدليها هنا */
        $update_sql = "UPDATE academic_report
                       SET report_appoval = 'معتمد'
                       WHERE report_id = ?";

        $update_stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $report_id);

        if (mysqli_stmt_execute($update_stmt)) {
            $msg = "تم اعتماد التقرير بنجاح.";
            $type = "success";
        } else {
            $msg = "حدث خطأ أثناء اعتماد التقرير.";
            $type = "error";
        }

    } else {
        $msg = "لا يمكن اعتماد هذا التقرير.";
        $type = "error";
    }
}

/* =====================================================
   2) دفع دفعة معينة
   بيانات البطاقة لا تُخزن
===================================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_payment"])) {

    $payment_id = (int)$_POST["payment_id"];
    $card_name  = trim($_POST["card_name"]);
    $card_number = trim($_POST["card_number"]);
    $exp_date = trim($_POST["exp_date"]);
    $cvv = trim($_POST["cvv"]);

    /* تحقق بسيط من تعبئة الحقول */
    if ($card_name == "" || $card_number == "" || $exp_date == "" || $cvv == "") {
        $msg = "يرجى تعبئة جميع بيانات الدفع.";
        $type = "error";
    } else {

        /* لا يمكن الدفع إلا إذا كان تقرير هذه الدفعة معتمد */
        $pay_check_sql = "SELECT payments.payment_id
                          FROM payments
                          INNER JOIN e_contract
                            ON payments.contract_id = e_contract.contract_id
                          INNER JOIN scholarship_requests
                            ON e_contract.request_id = scholarship_requests.request_id
                          INNER JOIN scholarship_opps
                            ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
                          INNER JOIN academic_report
                            ON payments.payment_id = academic_report.payment_id
                          WHERE payments.payment_id = ?
                          AND scholarship_requests.request_id = ?
                          AND scholarship_opps.inv_id = ?
                          AND academic_report.report_appoval = 'معتمد'
                          AND payments.payment_status != 'مكتملة'";

        $pay_check_stmt = mysqli_prepare($con, $pay_check_sql);
        mysqli_stmt_bind_param($pay_check_stmt, "iii", $payment_id, $request_id, $inv_id);
        mysqli_stmt_execute($pay_check_stmt);
        $pay_check_result = mysqli_stmt_get_result($pay_check_stmt);

        if (mysqli_num_rows($pay_check_result) > 0) {

            /* تحديث حالة الدفعة */
            $pay_sql = "UPDATE payments
                        SET payment_status = 'مكتملة',
                            payment_date = NOW()
                        WHERE payment_id = ?";

            $pay_stmt = mysqli_prepare($con, $pay_sql);
            mysqli_stmt_bind_param($pay_stmt, "i", $payment_id);

            if (mysqli_stmt_execute($pay_stmt)) {
                $msg = "تم سداد الدفعة بنجاح.";
                $type = "success";
            } else {
                $msg = "حدث خطأ أثناء تسجيل الدفع.";
                $type = "error";
            }

        } else {
            $msg = "لا يمكن دفع هذه الدفعة قبل اعتماد تقريرها أو ربما تم سدادها مسبقًا.";
            $type = "error";
        }
    }
}

/* =====================================================
   3) جلب البيانات الأساسية
===================================================== */
$main_sql = "SELECT
                scholarship_requests.request_id,
                scholarship_requests.major_name,
                scholarship_requests.univ_name,
                beneficiary.f_name,
                beneficiary.l_name,
                scholarship_opps.sch_name,
                e_contract.contract_id,
                e_contract.payments_count,
                e_contract.funding_duration,
                e_contract.amount
             FROM e_contract
             INNER JOIN scholarship_requests
               ON e_contract.request_id = scholarship_requests.request_id
             INNER JOIN scholarship_opps
               ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
             INNER JOIN beneficiary
               ON scholarship_requests.bnf_id = beneficiary.bnf_id
             WHERE e_contract.request_id = ?
             AND scholarship_opps.inv_id = ?";

$main_stmt = mysqli_prepare($con, $main_sql);
mysqli_stmt_bind_param($main_stmt, "ii", $request_id, $inv_id);
mysqli_stmt_execute($main_stmt);
$main_result = mysqli_stmt_get_result($main_stmt);
$main_data = mysqli_fetch_assoc($main_result);

if (!$main_data) {
    die("لا يمكن الوصول إلى هذه البيانات.");
}

/* =====================================================
   4) جلب الدفعات مع التقرير الخاص بكل دفعة
===================================================== */
$rows = [];

$details_sql = "SELECT
                  payments.payment_id,
                  payments.installment_number,
                  payments.payment_amount,
                  payments.payment_status,
                  payments.payment_date,
                  academic_report.report_id,
                  academic_report.report_file,
                  academic_report.report_appoval
                FROM payments
                LEFT JOIN academic_report
                  ON payments.payment_id = academic_report.payment_id
                WHERE payments.contract_id = ?
                ORDER BY payments.installment_number ASC";

$details_stmt = mysqli_prepare($con, $details_sql);
mysqli_stmt_bind_param($details_stmt, "i", $main_data['contract_id']);
mysqli_stmt_execute($details_stmt);
$details_result = mysqli_stmt_get_result($details_stmt);

while ($row = mysqli_fetch_assoc($details_result)) {
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تفاصيل المدفوعات</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">

<style>
/* الهيدر */
.header{
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.page-heading{
  display:flex;
  flex-direction:column;
  align-items:flex-start;
  text-align:right;
}

.header-icons{
  display:flex;
  align-items:center;
}

/* مساحة الصفحة */
.pay-details-page{
  padding:35px 30px 50px;
}

/* صف السهم */
.page-top{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:25px;
}

/* الكروت */
.cards-row{
  display:flex;
  gap:18px;
  margin-bottom:35px;
}

.info-card{
  flex:1;
  background:#FFFFFF;
  border:1px solid #e4e4e4;
  border-radius:10px;
  box-shadow:0 2px 10px rgba(0,0,0,0.08);
  overflow:hidden;
}

.info-card-title{
  font-size:20px;
  font-weight:700;
  color:#111111;
  padding:20px 24px 10px;
  border-bottom:1px solid #d9d9d9;
}

.info-card-body{
  padding:18px 24px 24px;
}

.info-line{
  margin-bottom:12px;
  font-size:18px;
  color:#555555;
  line-height:2;
}

.info-line .label{
  color:#76a6b7;
  font-weight:700;
  margin-left:8px;
}

/* عنوان القسم */
.section-title{
  font-size:22px;
  font-weight:700;
  color:#111111;
  text-align:right;
  margin-bottom:18px;
}

/* الجدول */
.table-wrap{
  width:100%;
  background:#FFFFFF;
  border:1px solid #e3e3e3;
  border-radius:8px;
  box-shadow:0 2px 10px rgba(0,0,0,0.06);
  overflow:hidden;
}

.pay-table{
  width:100%;
  border-collapse:collapse;
}

.pay-table th,
.pay-table td{
  border:1px solid #d9d9d9;
  padding:16px 10px;
  text-align:center;
  font-size:16px;
  vertical-align:middle;
}

.pay-table th{
  background:#FFFFFF;
  color:#4a2b63;
  font-weight:700;
  font-size:18px;
}

.installment-number{
  font-size:20px;
  font-weight:700;
  color:#555555;
}

/* الأزرار */
.small-btn{
  min-width:120px;
  height:42px;
  border:1px solid #7a7a7a;
  border-radius:14px;
  background:#FFFFFF;
  color:#4a2b63;
  font-size:14px;
  font-weight:700;
  cursor:pointer;
  font-family:"Noto Kufi Arabic",sans-serif;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  text-decoration:none;
}

.approve-btn{
  min-width:120px;
  height:44px;
  border:none;
  border-radius:14px;
  background:#65b185;
  color:#FFFFFF;
  font-size:15px;
  font-weight:700;
  cursor:pointer;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.pay-btn{
  min-width:120px;
  height:44px;
  border:none;
  border-radius:14px;
  background:#a8a8a8;
  color:#FFFFFF;
  font-size:15px;
  font-weight:700;
  cursor:pointer;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.pay-btn.active{
  background:#e4b869;
}

/* البادجات */
.badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:110px;
  height:42px;
  border-radius:14px;
  font-size:14px;
  font-weight:700;
  color:#FFFFFF;
}

.badge-green{
  background:#65b185;
}

.badge-yellow{
  background:#e4b869;
}

.badge-gray{
  background:#a8a8a8;
}

/* الرسائل */
.message{
  text-align:center;
  padding:10px;
  margin-bottom:20px;
  border-radius:6px;
  font-size:13px;
}

.error{
  background:#fff3f3;
  color:#b42318;
}

.success{
  background:#f1fff3;
  color:#1f7a2e;
}

/* النافذة المنبثقة */
.modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.35);
  justify-content:center;
  align-items:center;
  z-index:3000;
}

.modal-content{
  width:700px;
  max-width:92%;
  background:#FFFFFF;
  border-radius:12px;
  box-shadow:0 10px 25px rgba(0,0,0,0.18);
  padding:28px 34px;
}

.modal-title{
  text-align:center;
  font-size:20px;
  font-weight:700;
  color:#4a2b63;
  margin-bottom:26px;
}

.pay-form{
  display:flex;
  flex-direction:column;
  gap:20px;
}

.pay-row{
  display:flex;
  gap:20px;
}

.pay-field{
  flex:1;
}

.pay-field label{
  display:block;
  margin-bottom:8px;
  font-size:15px;
  font-weight:600;
  color:#333333;
}

.pay-field input{
  width:100%;
  height:52px;
  border:1px solid #cccccc;
  border-radius:10px;
  padding:10px 14px;
  font-size:15px;
  outline:none;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.confirm-pay-btn{
  width:100%;
  height:56px;
  border:none;
  border-radius:10px;
  background:#77a7b8;
  color:#FFFFFF;
  font-size:18px;
  font-weight:700;
  cursor:pointer;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.close-modal-btn{
  background:none;
  border:none;
  color:#b54747;
  font-size:15px;
  cursor:pointer;
  margin-top:8px;
  font-family:"Noto Kufi Arabic",sans-serif;
}

/* تجاوب */
@media (max-width: 950px){
  .cards-row{
    flex-direction:column;
  }

  .pay-table{
    display:block;
    overflow-x:auto;
  }

  .pay-row{
    flex-direction:column;
  }
}
</style>
</head>
<body>

<div class="layout">

  <!-- الشريط الجانبي -->
  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-logo">
        <img src="شعار نورين.png" alt="نورين">
      </div>

      <ul class="sidebar-menu">
        <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
        <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
        <li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
        <li><a href="Inv10_Payments.php" class="active">المدفوعات</a></li>
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

  <!-- المحتوى -->
  <div class="main-content">

    <!-- الهيدر -->
    <header class="header">
      <div class="header-icons">
        <div class="settings-dropdown">
          <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
          <div class="dropdown-menu">
            <a href="Inv02_Profile.php">الملف الشخصي</a>
            <a href="support.php">تقديم شكوى او استفسار</a>
          </div>
        </div>
      </div>

      <div class="page-heading">
        <h1 class="page-title">المدفوعات</h1>
      </div>
    </header>

    <section class="pay-details-page">

      <!-- زر الرجوع -->
      <div class="page-top">
        <div></div>
        <a href="Inv10_Payments.php">
          <img src="سهم تراجع.svg" width="40" alt="رجوع">
        </a>
      </div>

      <!-- رسالة النظام -->
      <?php if($msg != ""): ?>
        <div class="message <?php echo $type; ?>">
          <?php echo $msg; ?>
        </div>
      <?php endif; ?>

      <!-- الكروت العليا -->
      <div class="cards-row">

        <div class="info-card">
          <div class="info-card-title">تفاصيل المنحة الحالية</div>
          <div class="info-card-body">
            <div class="info-line"><span class="label">رقم الطلب:</span>#<?php echo htmlspecialchars($main_data['request_id']); ?></div>
            <div class="info-line"><span class="label">المنحة:</span><?php echo htmlspecialchars($main_data['sch_name']); ?></div>
            <div class="info-line"><span class="label">التخصص:</span><?php echo htmlspecialchars($main_data['major_name']); ?></div>
            <div class="info-line"><span class="label">المستفيد:</span><?php echo htmlspecialchars($main_data['f_name']) . " " . htmlspecialchars($main_data['l_name']); ?></div>
          </div>
        </div>

        <div class="info-card">
          <div class="info-card-title">ملخص العقد</div>
          <div class="info-card-body">
            <div class="info-line"><span class="label">رقم العقد:</span><?php echo htmlspecialchars($main_data['contract_id']); ?></div>
            <div class="info-line"><span class="label">قيمة المنحة الإجمالية:</span><?php echo number_format($main_data['amount'], 2); ?></div>
            <div class="info-line"><span class="label">عدد الدفعات:</span><?php echo htmlspecialchars($main_data['payments_count']); ?></div>
          </div>
        </div>

      </div>

      <!-- جدول الدفعات -->
      <div class="section-title">التقارير والدفعات</div>

      <div class="table-wrap">
        <table class="pay-table">
          <thead>
            <tr>
              <th>رقم الدفعة</th>
              <th>المبلغ</th>
              <th>عرض التقرير</th>
              <th>اعتماد التقرير</th>
              <th>حالة الدفعة</th>
              <th>الإجراء</th>
            </tr>
          </thead>
          <tbody>

            <?php foreach($rows as $item): ?>
              <tr>

                <td class="installment-number">
                  <?php echo htmlspecialchars($item['installment_number']); ?>
                </td>

                <td>
                  <?php echo number_format($item['payment_amount'], 2); ?>
                </td>

                <!-- عرض الملف -->
                <td>
                  <?php if($item['report_file'] != ""): ?>
                    <a href="<?php echo htmlspecialchars($item['report_file']); ?>" target="_blank" class="small-btn">
                      تنزيل الملف
                    </a>
                  <?php else: ?>
                    <span style="color:#999;">لا يوجد تقرير</span>
                  <?php endif; ?>
                </td>

                <!-- اعتماد التقرير -->
                <td>
                  <?php if($item['report_file'] == ""): ?>
                    <span class="badge badge-gray">لا يوجد تقرير</span>

                  <?php elseif($item['report_appoval'] == "معتمد"): ?>
                    <span class="badge badge-green">معتمد</span>

                  <?php else: ?>
                    <form method="post" style="margin:0;">
                      <input type="hidden" name="report_id" value="<?php echo $item['report_id']; ?>">
                      <button type="submit" name="approve_report" class="approve-btn">اعتماد</button>
                    </form>
                  <?php endif; ?>
                </td>

                <!-- حالة الدفع -->
                <td>
                  <?php if($item['payment_status'] == "مكتملة"): ?>
                    <span class="badge badge-green">مدفوعة</span>
                  <?php else: ?>
                    <span class="badge badge-yellow">غير مدفوعة</span>
                  <?php endif; ?>
                </td>

                <!-- زر الدفع -->
                <td>
                  <?php if($item['payment_status'] == "مكتملة"): ?>
                    <button class="pay-btn" disabled>تم السداد</button>

                  <?php elseif($item['report_appoval'] == "معتمد"): ?>
                    <button type="button"
                            class="pay-btn active openPayModal"
                            data-payment="<?php echo $item['payment_id']; ?>">
                      دفع الدفعة
                    </button>

                  <?php else: ?>
                    <button class="pay-btn" disabled>دفع الدفعة</button>
                  <?php endif; ?>
                </td>

              </tr>
            <?php endforeach; ?>

          </tbody>
        </table>
      </div>

    </section>

  </div>
</div>

<!-- نافذة الدفع -->
<div class="modal" id="paymentModal">
  <div class="modal-content">
    <div class="modal-title">رجاء قم بإدخال بيانات البطاقة لسداد دفعة المنحة للطالب</div>

    <form method="post" class="pay-form">
      <input type="hidden" name="payment_id" id="modal_payment_id">

      <div class="pay-field">
        <label>الاسم على البطاقة</label>
        <input type="text" name="card_name">
      </div>

      <div class="pay-field">
        <label>رقم البطاقة</label>
        <input type="text" name="card_number">
      </div>

      <div class="pay-row">
        <div class="pay-field">
          <label>تاريخ الانتهاء</label>
          <input type="text" name="exp_date" placeholder="MM/YY">
        </div>

        <div class="pay-field">
          <label>CVV</label>
          <input type="text" name="cvv">
        </div>
      </div>

      <button type="submit" name="confirm_payment" class="confirm-pay-btn">اضغط لتأكيد الدفع</button>
      <button type="button" class="close-modal-btn" id="closeModal">إغلاق</button>
    </form>
  </div>
</div>

<script>
/* فتح وإغلاق نافذة الدفع */
const modal = document.getElementById("paymentModal");
const modalPaymentId = document.getElementById("modal_payment_id");
const openButtons = document.querySelectorAll(".openPayModal");
const closeModal = document.getElementById("closeModal");

openButtons.forEach(function(btn){
  btn.addEventListener("click", function(){
    modal.style.display = "flex";
    modalPaymentId.value = btn.getAttribute("data-payment");
  });
});

closeModal.addEventListener("click", function(){
  modal.style.display = "none";
});

window.addEventListener("click", function(e){
  if(e.target === modal){
    modal.style.display = "none";
  }
});
</script>

</body>
</html>