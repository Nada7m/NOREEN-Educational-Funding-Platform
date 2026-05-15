<?php
session_start();

/** التحقق من دخول المستثمر **/
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

/** التحقق من نجاح الاتصال **/
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* ضبط الترميز لدعم اللغة العربية */
mysqli_set_charset($con, "utf8mb4");

/* بيانات أساسية */
$inv_id = $_SESSION['inv_id'];

/** التحقق من وجود رقم الطلب **/
if (!isset($_GET['request_id']) || $_GET['request_id'] == "") {
    die("رقم الطلب غير موجود.");
}

/* تحويل رقم الطلب إلى عدد صحيح */
$request_id = (int)$_GET['request_id'];

/* تجهيز رسائل النظام */
$msg = "";
$type = "";

/* اعتماد التقرير */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["approve_report"])) {

    /* الحصول على رقم التقرير */
    $report_id = (int)$_POST["report_id"];

    /* التحقق من صلاحية التقرير */
    $check_sql = "SELECT academic_report.report_id
                  FROM academic_report
                  INNER JOIN e_contract ON academic_report.contract_id = e_contract.contract_id
                  INNER JOIN scholarship_requests ON e_contract.request_id = scholarship_requests.request_id
                  INNER JOIN scholarship_opps ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
                  WHERE academic_report.report_id = ?
                  AND scholarship_requests.request_id = ?
                  AND scholarship_opps.inv_id = ?";

    /* تجهيز الاستعلام */
    $check_stmt = mysqli_prepare($con, $check_sql);

    /* ربط البيانات */
    mysqli_stmt_bind_param($check_stmt, "iii", $report_id, $request_id, $inv_id);

    /* تنفيذ الاستعلام */
    mysqli_stmt_execute($check_stmt);

    /* جلب النتائج */
    $check_result = mysqli_stmt_get_result($check_stmt);

    /** التحقق من وجود التقرير **/
    if (mysqli_num_rows($check_result) > 0) {

        /* تحديث حالة التقرير */
        $update_sql = "UPDATE academic_report SET report_appoval = 'معتمد' WHERE report_id = ?";

        /* تجهيز الاستعلام */
        $update_stmt = mysqli_prepare($con, $update_sql);

        /* ربط رقم التقرير */
        mysqli_stmt_bind_param($update_stmt, "i", $report_id);

        /** التحقق من نجاح اعتماد التقرير **/
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

/* دفع الدفعة */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_payment"])) {

    /* الحصول على بيانات الدفع */
    $payment_id  = (int)$_POST["payment_id"];
    $card_name   = trim($_POST["card_name"]);
    $card_number = preg_replace('/\D/', '', $_POST["card_number"]);
    $exp_date    = trim($_POST["exp_date"]);
    $cvv         = preg_replace('/\D/', '', $_POST["cvv"]);

    /** التحقق من تعبئة جميع بيانات الدفع **/
    if ($card_name == "" || $card_number == "" || $exp_date == "" || $cvv == "") {

        $msg = "يرجى تعبئة جميع بيانات الدفع.";
        $type = "error";

    /** التحقق من صحة اسم حامل البطاقة **/
    } elseif (!preg_match('/^[\p{Arabic}a-zA-Z\s]{3,100}$/u', $card_name)) {

        $msg = "اسم حامل البطاقة غير صحيح.";
        $type = "error";

    /** التحقق من صحة رقم البطاقة **/
    } elseif (!preg_match('/^\d{16}$/', $card_number)) {

        $msg = "رقم البطاقة يجب أن يكون 16 رقم.";
        $type = "error";

    /** التحقق من صحة تاريخ الانتهاء **/
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $exp_date)) {

        $msg = "تاريخ الانتهاء يجب أن يكون بالشكل MM/YY.";
        $type = "error";

    /** التحقق من صحة رمز الأمان **/
    } elseif (!preg_match('/^\d{3}$/', $cvv)) {

        $msg = "رمز الأمان يجب أن يكون 3 أرقام.";
        $type = "error";

    } else {

        /* التحقق من إمكانية الدفع */
        $pay_check_sql = "SELECT payments.payment_id, payments.contract_id
                          FROM payments
                          INNER JOIN e_contract ON payments.contract_id = e_contract.contract_id
                          INNER JOIN scholarship_requests ON e_contract.request_id = scholarship_requests.request_id
                          INNER JOIN scholarship_opps ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
                          INNER JOIN academic_report ON payments.payment_id = academic_report.payment_id
                          WHERE payments.payment_id = ?
                          AND scholarship_requests.request_id = ?
                          AND scholarship_opps.inv_id = ?
                          AND academic_report.report_appoval = 'معتمد'
                          AND payments.payment_status != 'تم الدفع'";

        /* تجهيز الاستعلام */
        $pay_check_stmt = mysqli_prepare($con, $pay_check_sql);

        /* ربط البيانات */
        mysqli_stmt_bind_param($pay_check_stmt, "iii", $payment_id, $request_id, $inv_id);

        /* تنفيذ الاستعلام */
        mysqli_stmt_execute($pay_check_stmt);

        /* جلب النتائج */
        $pay_check_result = mysqli_stmt_get_result($pay_check_stmt);

        /* جلب بيانات الدفع */
        $pay_row = mysqli_fetch_assoc($pay_check_result);

        /** التحقق من صلاحية الدفع **/
        if ($pay_row) {

            /* الحصول على رقم العقد */
            $contract_id_for_payment = (int)$pay_row["contract_id"];

            /* تحديث حالة الدفع */
            $pay_sql = "UPDATE payments SET payment_status = 'تم الدفع', payment_date = NOW() WHERE payment_id = ?";

            /* تجهيز الاستعلام */
            $pay_stmt = mysqli_prepare($con, $pay_sql);

            /* ربط رقم الدفعة */
            mysqli_stmt_bind_param($pay_stmt, "i", $payment_id);

            /** التحقق من نجاح عملية الدفع **/
            if (mysqli_stmt_execute($pay_stmt)) {

                /* التحقق من وجود دفعات متبقية */
                $remaining_sql = "SELECT payment_id FROM payments WHERE contract_id = ? AND payment_status != 'تم الدفع' LIMIT 1";

                /* تجهيز الاستعلام */
                $remaining_stmt = mysqli_prepare($con, $remaining_sql);

                /* ربط رقم العقد */
                mysqli_stmt_bind_param($remaining_stmt, "i", $contract_id_for_payment);

                /* تنفيذ الاستعلام */
                mysqli_stmt_execute($remaining_stmt);

                /* جلب النتائج */
                $remaining_result = mysqli_stmt_get_result($remaining_stmt);

                /** التحقق من انتهاء جميع الدفعات **/
                if (mysqli_num_rows($remaining_result) == 0) {

                    /* إنهاء العقد */
                    $finish_sql = "UPDATE e_contract SET ctr_status = 'منتهي' WHERE contract_id = ?";

                    /* تجهيز الاستعلام */
                    $finish_stmt = mysqli_prepare($con, $finish_sql);

                    /* ربط رقم العقد */
                    mysqli_stmt_bind_param($finish_stmt, "i", $contract_id_for_payment);

                    /* تنفيذ الاستعلام */
                    mysqli_stmt_execute($finish_stmt);

                    /* إنهاء الطلب */
                    $finish_request_sql = "UPDATE scholarship_requests SET request_status = 'منتهي' WHERE request_id = ?";

                    /* تجهيز الاستعلام */
                    $finish_request_stmt = mysqli_prepare($con, $finish_request_sql);

                    /* ربط رقم الطلب */
                    mysqli_stmt_bind_param($finish_request_stmt, "i", $request_id);

                    /* تنفيذ الاستعلام */
                    mysqli_stmt_execute($finish_request_stmt);

                    $msg = "تم سداد الدفعة الأخيرة بنجاح وتم إنهاء العقد والطلب.";
                    $type = "success";

                } else {

                    $msg = "تم سداد الدفعة بنجاح.";
                    $type = "success";
                }

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

/* جلب بيانات العقد */
$main_sql = "SELECT scholarship_requests.request_id,scholarship_requests.major_name,scholarship_requests.univ_name,beneficiary.f_name,beneficiary.l_name,scholarship_opps.sch_name,e_contract.contract_id,e_contract.payments_count,e_contract.amount,e_contract.ctr_status
             FROM e_contract
             INNER JOIN scholarship_requests ON e_contract.request_id = scholarship_requests.request_id
             INNER JOIN scholarship_opps ON scholarship_requests.scholarship_id = scholarship_opps.scholarship_id
             INNER JOIN beneficiary ON scholarship_requests.bnf_id = beneficiary.bnf_id
             WHERE e_contract.request_id = ? AND scholarship_opps.inv_id = ?";

/* تجهيز الاستعلام */
$main_stmt = mysqli_prepare($con, $main_sql);

/* ربط البيانات */
mysqli_stmt_bind_param($main_stmt, "ii", $request_id, $inv_id);

/* تنفيذ الاستعلام */
mysqli_stmt_execute($main_stmt);

/* جلب النتائج */
$main_result = mysqli_stmt_get_result($main_stmt);

/* تحويل البيانات إلى مصفوفة */
$main_data = mysqli_fetch_assoc($main_result);

/** التحقق من وجود بيانات العقد **/
if (!$main_data) {

    die("لا يمكن الوصول إلى هذه البيانات.");
}

/* جلب الدفعات */
$rows = [];

$details_sql = "SELECT payments.payment_id,payments.installment_number,payments.payment_amount,payments.payment_status,payments.payment_date,academic_report.report_id,academic_report.report_file,academic_report.report_appoval
                FROM payments
                LEFT JOIN academic_report ON payments.payment_id = academic_report.payment_id
                WHERE payments.contract_id = ?
                ORDER BY payments.installment_number ASC";

/* تجهيز الاستعلام */
$details_stmt = mysqli_prepare($con, $details_sql);

/* ربط رقم العقد */
mysqli_stmt_bind_param($details_stmt, "i", $main_data['contract_id']);

/* تنفيذ الاستعلام */
mysqli_stmt_execute($details_stmt);

/* جلب النتائج */
$details_result = mysqli_stmt_get_result($details_stmt);

/* تخزين البيانات داخل المصفوفة */
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
<link rel="stylesheet" href="CSS01Layout.css?v=4">
<style>
.header{display:flex;justify-content:space-between;align-items:center}.page-heading{display:flex;flex-direction:column;align-items:flex-start;text-align:right}.header-icons{display:flex;align-items:center}.pay-details-page{padding:30px}.page-top{display:flex;justify-content:flex-end;align-items:center;margin-bottom:20px}.content-grid{display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:stretch}.info-card{background:#FFFFFF;border:1px solid #EAEAEA;border-radius:16px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);height:100%}.info-card h3{color:#3E2454;font-size:16px;font-weight:700;text-align:center;margin-bottom:18px;padding-bottom:10px;border-bottom:1px solid #F0F0F0}.contract-box{direction:rtl;text-align:right}.data-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;font-size:14px}.data-row:last-child{margin-bottom:0}.data-row label{color:#70A0AF;font-weight:700;flex-shrink:0}.data-row span{color:#333333;font-weight:600;text-align:left}.table-wrap{background:#FFFFFF;border:1px solid #EAEAEA;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:10px 16px 16px;height:100%}.styled-table{width:100%;border-collapse:collapse}.styled-table th{padding:16px 14px;text-align:center;color:#3E2454;font-size:15px;font-weight:700;border-bottom:1px solid #EAEAEA;background:#FFFFFF}.styled-table td{padding:18px 14px;text-align:center;border-bottom:1px solid #F1F1F1;color:#444444;font-size:14px;background:#FFFFFF;vertical-align:middle}.styled-table tbody tr:last-child td{border-bottom:none}.installment-number{font-size:16px;font-weight:700;color:#333333}.small-btn,.action-btn{display:inline-block;width:120px;padding:8px 0;border:1px solid #999;border-radius:10px;background:#fff;color:#3E2454;text-decoration:none;font-size:13px;font-weight:600;font-family:'Noto Kufi Arabic',sans-serif;transition:0.3s;cursor:pointer;text-align:center}.small-btn:hover,.action-btn:hover{background:#f4f0f7}.status-badge{display:inline-block;min-width:120px;padding:7px 14px;border-radius:20px;color:#fff;font-size:13px;font-weight:700;font-family:'Noto Kufi Arabic',sans-serif;text-align:center}.done{background:#63B68B}.wait{background:#E9BE66}.gray-badge{background:#FFFFFF;color:#444444;border:1px solid #DDDDDD}.no-action{color:#999999;font-weight:700}.message{text-align:center;padding:10px;margin-bottom:20px;border-radius:6px;font-size:13px}.error{background:#fff3f3;color:#b42318}.success{background:#f1fff3;color:#1f7a2e}.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.35);justify-content:center;align-items:center;z-index:3000}.modal-content{width:700px;max-width:92%;background:#FFFFFF;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.18);padding:28px 34px}.modal-title{text-align:center;font-size:20px;font-weight:700;color:#4a2b63;margin-bottom:26px}.pay-form{display:flex;flex-direction:column;gap:20px}.pay-row{display:flex;gap:20px}.pay-field{flex:1}.pay-field label{display:block;margin-bottom:8px;font-size:15px;font-weight:600;color:#333333}.pay-field input{width:100%;height:52px;border:1px solid #cccccc;border-radius:10px;padding:10px 14px;font-size:15px;outline:none;font-family:"Noto Kufi Arabic",sans-serif;box-sizing:border-box}.confirm-pay-btn{display:inline-block;width:100%;padding:12px 0;border:1px solid #999;border-radius:10px;background:#fff;color:#3E2454;text-decoration:none;font-size:15px;font-weight:700;font-family:'Noto Kufi Arabic',sans-serif;transition:0.3s;cursor:pointer;text-align:center}.confirm-pay-btn:hover{background:#f4f0f7}.close-modal-btn{display:inline-block;width:100%;padding:10px 0;border:1px solid #999;border-radius:10px;background:#fff;color:#3E2454;text-decoration:none;font-size:14px;font-weight:600;font-family:'Noto Kufi Arabic',sans-serif;transition:0.3s;cursor:pointer;text-align:center}.close-modal-btn:hover{background:#f4f0f7}.backbtn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none}@media (max-width:950px){.content-grid{grid-template-columns:1fr}.styled-table{display:block;overflow-x:auto}.pay-row{flex-direction:column}}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
<div class="sidebar-top">
<div class="sidebar-logo">
<img src="شعار نورين.png" alt="نورين">
</div>
<ul class="sidebar-menu">
<li><a href="Inv00_MainPage.php">الرئيسية</a></li>
<li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
<li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
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
<div class="main-content">
<header class="header">
<div class="page-heading">
<div class="page-title">المدفوعات</div>
<div class="page-description">تفاصيل إدارة منحة المستفيد</div>
</div>
<div class="header-icons">
<div class="settings-dropdown">
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
<div class="dropdown-menu">
<a href="Inv02_Profile.php">الملف الشخصي</a>
<a href="support.php">تقديم شكوى او استفسار</a>
</div>
</div>
</div>
</header>
<section class="pay-details-page">
<div class="page-top">
<a href="Inv10_Payments.php" class="backbtn">
<img src="سهم تراجع.svg" class="backicon" alt="رجوع">
</a>
</div>
<?php if($msg != ""): ?>
<div class="message <?php echo $type; ?>"><?php echo $msg; ?></div>
<?php endif; ?>
<div class="content-grid">
<div class="info-card contract-box">
<h3>ملخص العقد</h3>
<div class="data-row">
<label>رقم العقد:</label>
<span><?php echo htmlspecialchars($main_data['contract_id']); ?></span>
</div>
<div class="data-row">
<label>قيمة المنحة الإجمالية:</label>
<span><?php echo number_format($main_data['amount'], 0); ?></span>
</div>
<div class="data-row">
<label>عدد الدفعات:</label>
<span><?php echo htmlspecialchars($main_data['payments_count']); ?></span>
</div>
<div class="data-row">
<label>حالة العقد:</label>
<span><?php echo htmlspecialchars($main_data['ctr_status']); ?></span>
</div>
</div>
<div class="table-wrap">
<table class="styled-table">
<thead>
<tr>
<th>رقم الدفعة</th>
<th>مبلغ الدفعة</th>
<th>حالة التقرير</th>
<th>اعتماد التقرير</th>
<th>حالة الدفعة</th>
<th>الإجراء</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $item): ?>
<tr>
<td><span class="installment-number"><?php echo htmlspecialchars($item['installment_number']); ?></span></td>
<td><?php echo number_format($item['payment_amount'], 0); ?></td>
<td>
<?php if ($item['report_file'] != ""): ?>
<a href="<?php echo htmlspecialchars($item['report_file']); ?>" target="_blank" class="small-btn">تنزيل الملف</a>
<?php else: ?>
<span class="status-badge gray-badge">لا يوجد تقرير</span>
<?php endif; ?>
</td>
<td>
<?php if ($item['report_file'] == ""): ?>
<span class="status-badge gray-badge">لا يوجد تقرير</span>
<?php elseif ($item['report_appoval'] == "معتمد"): ?>
<span class="status-badge done">معتمد</span>
<?php else: ?>
<form method="post" style="margin:0;">
<input type="hidden" name="report_id" value="<?php echo $item['report_id']; ?>">
<button type="submit" name="approve_report" class="action-btn">اعتماد</button>
</form>
<?php endif; ?>
</td>
<td>
<?php if ($item['payment_status'] == "تم الدفع"): ?>
<span class="status-badge done">مدفوعة</span>
<?php else: ?>
<span class="status-badge wait">غير مدفوعة</span>
<?php endif; ?>
</td>
<td>
<?php if ($item['payment_status'] == "تم الدفع"): ?>
<span class="no-action">—</span>
<?php elseif ($item['report_appoval'] == "معتمد"): ?>
<button type="button" class="action-btn openPayModal" data-payment="<?php echo $item['payment_id']; ?>">دفع الدفعة</button>
<?php else: ?>
<span class="no-action">—</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</section>
</div>
</div>
<div class="modal" id="paymentModal">
<div class="modal-content">
<div class="modal-title">رجاء قم بإدخال بيانات البطاقة لسداد دفعة المنحة للمستفيد</div>
<form method="post" class="pay-form" id="paymentForm">
<input type="hidden" name="payment_id" id="modal_payment_id">
<div class="pay-field">
<label>الاسم على البطاقة</label>
<input type="text" name="card_name" id="card_name" maxlength="100">
</div>
<div class="pay-field">
<label>رقم البطاقة</label>
<input type="text" name="card_number" id="card_number" maxlength="16" inputmode="numeric">
</div>
<div class="pay-row">
<div class="pay-field">
<label>تاريخ الانتهاء</label>
<input type="text" name="exp_date" id="exp_date" placeholder="MM/YY" maxlength="5" inputmode="numeric">
</div>
<div class="pay-field">
<label>رمز الأمان</label>
<input type="text" name="cvv" id="cvv" maxlength="3" inputmode="numeric">
</div>
</div>
<button type="submit" name="confirm_payment" class="confirm-pay-btn">تأكيد السداد</button>
<button type="button" class="close-modal-btn" id="closeModalBtn">إلغاء</button>
</form>
</div>
</div>
<script>
 /* فتح وإغلاق نافذة الدفع */
const paymentModal=document.getElementById("paymentModal");
const modalPaymentId=document.getElementById("modal_payment_id");
const closeModalBtn=document.getElementById("closeModalBtn");
document.querySelectorAll(".openPayModal").forEach(function(btn){
btn.addEventListener("click",function(){
modalPaymentId.value=this.getAttribute("data-payment");
paymentModal.style.display="flex";
});
});
closeModalBtn.addEventListener("click",function(){
paymentModal.style.display="none";
});
paymentModal.addEventListener("click",function(e){
if(e.target===paymentModal){ paymentModal.style.display="none"; }
});
/* تنسيق بيانات البطاقة */
document.getElementById("card_number").addEventListener("input",function(){
this.value=this.value.replace(/\D/g,"").slice(0,16);
});
document.getElementById("cvv").addEventListener("input",function(){
this.value=this.value.replace(/\D/g,"").slice(0,3);
});
document.getElementById("exp_date").addEventListener("input",function(){
let value=this.value.replace(/\D/g,"").slice(0,4);
this.value=value.length>=3 ? value.slice(0,2)+"/"+value.slice(2) : value;
});
</script>
</body>
</html>