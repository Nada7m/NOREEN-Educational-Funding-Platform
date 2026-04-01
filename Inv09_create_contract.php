<?php
session_start();

if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "noreen");
if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}
mysqli_set_charset($con, "utf8mb4");

$inv_id = $_SESSION['inv_id'];
$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

if ($request_id <= 0) {
    die("رقم الطلب غير صحيح");
}

$success_message = "";
$error_message = "";

/* جلب اسم المستفيد */
$sql = "SELECT b.f_name, b.l_name
        FROM scholarship_requests sr
        INNER JOIN scholarship_opps so ON sr.scholarship_id = so.scholarship_id
        INNER JOIN beneficiary b ON sr.bnf_id = b.bnf_id
        WHERE sr.request_id = ? AND so.inv_id = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ii", $request_id, $inv_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$request_data = mysqli_fetch_assoc($result);

if (!$request_data) {
    die("لا يمكن الوصول إلى هذا الطلب");
}

/* حفظ العقد */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : "";
    $funding_duration = isset($_POST['funding_duration']) ? trim($_POST['funding_duration']) : "";
    $payments_count = isset($_POST['payments_count']) ? trim($_POST['payments_count']) : "";
    $terms = isset($_POST['terms']) ? trim($_POST['terms']) : "";
    $agree = isset($_POST['agree']) ? 1 : 0;
    $ctr_status = "نشط";

    if ($amount === "" || $funding_duration === "" || $payments_count === "" || $terms === "") {
        $error_message = "يرجى تعبئة جميع الحقول المطلوبة";
    } elseif ($agree != 1) {
        $error_message = "يجب الإقرار بصحة بيانات العقد قبل الحفظ";
    } elseif (!is_numeric($amount)) {
        $error_message = "قيمة الدعم الإجمالية يجب أن تكون رقمًا";
    } else {

        $check_sql = "SELECT contract_id FROM e_contract WHERE request_id = ?";
        $check_stmt = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $request_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "تم إنشاء عقد مسبقًا لهذا الطلب";
        } else {
            $insert_sql = "INSERT INTO e_contract
                           (request_id, payments_count, funding_duration, ctr_status, terms, amount)
                           VALUES (?, ?, ?, ?, ?, ?)";

            $insert_stmt = mysqli_prepare($con, $insert_sql);
            mysqli_stmt_bind_param(
                $insert_stmt,
                "iiissd",
                $request_id,
                $payments_count,
                $funding_duration,
                $ctr_status,
                $terms,
                $amount
            );

            if (mysqli_stmt_execute($insert_stmt)) {
                $success_message = "تم حفظ بيانات العقد بنجاح";
                $_POST = array();
            } else {
                $error_message = "حدث خطأ أثناء حفظ العقد";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إنشاء العقد الإلكتروني</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=2">

<style>
*{
    box-sizing:border-box;
    font-family:"Noto Kufi Arabic",sans-serif;
}

body{
    margin:0;
    direction:rtl;
    background:#f7f7f7;
}

/* نفس الهيدر من الملف المرجعي */
.header{
    display:flex;
    flex-direction:row-reverse;
    justify-content:space-between;
    align-items:center;
}

.page-heading{
    text-align:right;
    align-items:flex-end;
}

.header-icons{
    display:flex;
    align-items:center;
}

/* نفس زر الرجوع من الملف المرجعي */
.back-icon{
    width:26px;
    height:26px;
}

.back-btn{
    width:32px;
    height:32px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.create-page{
    padding:35px;
}

.page-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
}

.contract-card{
    width:100%;
    background:#FFFFFF;
    border:1px solid #CFCFCF;
    border-radius:8px;
    padding:28px;
}

.message{
    padding:12px 14px;
    border-radius:6px;
    margin-bottom:16px;
    font-size:14px;
    font-weight:600;
}

.success{
    background:#eaf7ea;
    color:#237a2c;
    border:1px solid #b8e0bc;
}

.error{
    background:#fdecec;
    color:#b42323;
    border:1px solid #f1b7b7;
}

.contract-top{
    display:flex;
    justify-content:flex-start;
    align-items:center;
    gap:35px;
    margin-bottom:18px;
}

.contract-title{
    font-size:20px;
    font-weight:700;
    color:#472764;
}

.student-name{
    font-size:20px;
    font-weight:700;
    color:#111111;
}

.contract-divider{
    border:none;
    border-top:1px solid #dddddd;
    margin:18px 0 24px;
}

.contract-form{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px;
    align-items:start;
}

.form-group{
    margin-bottom:20px;
}

.form-label{
    display:block;
    margin-bottom:10px;
    font-size:18px;
    font-weight:700;
    color:#111111;
}

.req{
    color:#d9534f;
}

.form-input,
.form-select,
.form-textarea{
    width:100%;
    border:1px solid #b8d2dd;
    border-radius:4px;
    background:#ffffff;
    padding:13px 14px;
    font-size:14px;
    color:#111111;
    outline:none;
    direction:rtl;
    text-align:right;
}

/* نفس فكرة placeholder الهادئة */
::placeholder{
    color:#999;
    font-size:13px;
}

.form-input{
    height:50px;
}

.form-select{
    height:58px;
}

.form-textarea{
    height:235px;
    resize:none;
}

.contract-check{
    display:flex;
    align-items:flex-start;
    gap:10px;
    margin:12px 0 20px;
    font-size:15px;
    color:#111111;
    line-height:1.9;
}

.contract-check input{
    margin-top:6px;
    width:18px;
    height:18px;
    flex-shrink:0;
    accent-color:#472764;
}

.save-btn{
    width:220px;
    height:55px;
    border:none;
    border-radius:6px;
    background:#472764;
    color:#FFFFFF;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
}

@media (max-width: 992px){
    .contract-top{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }

    .contract-form{
        grid-template-columns:1fr;
    }

    .save-btn{
        width:100%;
    }
}
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
                <li><a href="Inv06_ManageScholarships.php" class="active">إدارة المنح</a></li>
                <li><a href="#">المدفوعات</a></li>
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

    <main class="main-content">

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
                <h1 class="page-title">إدارة المنح</h1>
                <p class="page-description">صفحة إنشاء العقد الإلكتروني</p>
            </div>

        </header>

        <section class="create-page">

            <div class="page-top">
                <div></div>

                <div class="back-btn">
                    <a href="Inv06_ManageScholarships.php">
                        <img src="سهم تراجع.svg" alt="رجوع" class="back-icon">
                    </a>
                </div>
            </div>

            <div class="contract-card">

                <?php if($success_message != ""): ?>
                    <div class="message success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if($error_message != ""): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="contract-top">
                    <div class="contract-title">بيانات إنشاء العقد الإلكتروني للمستفيد</div>
                    <div class="student-name">
                        <?php echo htmlspecialchars($request_data['f_name']) . " " . htmlspecialchars($request_data['l_name']); ?>
                    </div>
                </div>

                <hr class="contract-divider">

                <form method="POST">
                    <div class="contract-form">

                        <div>
                            <div class="form-group">
                                <label class="form-label"><span class="req">*</span> قيمة الدعم الإجمالية</label>
                                <input type="text" name="amount" class="form-input" placeholder="ااكتب المبلغ الإجمالي المتفق عليه (بالريال)" value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label"><span class="req">*</span> مدة تمويل المنحة(عدد سنوات الدراسة)</label>
                                <select name="funding_duration" class="form-select">
                                    <option value="">اختر مدة التمويل</option>
                                    <option value="1" <?php if(isset($_POST['funding_duration']) && $_POST['funding_duration']=="1") echo "selected"; ?>>1</option>
                                    <option value="2" <?php if(isset($_POST['funding_duration']) && $_POST['funding_duration']=="2") echo "selected"; ?>>2</option>
                                    <option value="3" <?php if(isset($_POST['funding_duration']) && $_POST['funding_duration']=="3") echo "selected"; ?>>3</option>
                                    <option value="4" <?php if(isset($_POST['funding_duration']) && $_POST['funding_duration']=="4") echo "selected"; ?>>4</option>
                                    <option value="5" <?php if(isset($_POST['funding_duration']) && $_POST['funding_duration']=="5") echo "selected"; ?>>5</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label"><span class="req">*</span> عدد الدفعات المطلوبة</label>
                                <select name="payments_count" class="form-select">
                                    <option value="">اختر عدد الدفعات</option>
                                    <option value="2" <?php if(isset($_POST['payments_count']) && $_POST['payments_count']=="2") echo "selected"; ?>>2</option>
                                    <option value="3" <?php if(isset($_POST['payments_count']) && $_POST['payments_count']=="3") echo "selected"; ?>>3</option>
                                    <option value="4" <?php if(isset($_POST['payments_count']) && $_POST['payments_count']=="4") echo "selected"; ?>>4</option>
                                    <option value="5" <?php if(isset($_POST['payments_count']) && $_POST['payments_count']=="5") echo "selected"; ?>>5</option>
                                   <option value="5" <?php if(isset($_POST['payments_count']) && $_POST['payments_count']=="6") echo "selected"; ?>>6</option>

                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label class="form-label"><span class="req">*</span> شروط العقد</label>
                                <textarea name="terms" class="form-textarea" placeholder="  تُسجَّل الشروط المدخلة هنا كمرجع رسمي للعقد "><?php echo isset($_POST['terms']) ? htmlspecialchars($_POST['terms']) : ''; ?></textarea>
                            </div>

                            <label class="contract-check">
                                <input type="checkbox" name="agree" <?php if(isset($_POST['agree'])) echo "checked"; ?>>
                                <span>أقر أنا ممثل الجهة الممولة بصحة بيانات هذا العقد واعتماده في نظام نورين.</span>
                            </label>

                            <button type="submit" class="save-btn">حفظ بيانات العقد و إنشاؤه</button>
                        </div>

                    </div>
                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>