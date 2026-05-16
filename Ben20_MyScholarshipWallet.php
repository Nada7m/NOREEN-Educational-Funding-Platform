<?php
/* بدء الجلسة */
session_start();

/** التحقق من تسجيل الدخول **/
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
$bnf_id = $_SESSION['bnf_id'];

/* جلب بيانات المحفظة */
$sql = "
    SELECT
        r.request_id,
        s.sch_name,
        c.contract_id,
        c.amount,
        c.payments_count,

        /* مجموع المبالغ المدفوعة */
        COALESCE(SUM(
            CASE
                WHEN p.payment_status = 'تم الدفع' THEN p.payment_amount
                ELSE 0
            END
        ), 0) AS paid_amount

    FROM scholarship_requests r

    INNER JOIN scholarship_opps s 
    ON r.scholarship_id = s.scholarship_id

    INNER JOIN e_contract c 
    ON r.request_id = c.request_id

    LEFT JOIN payments p 
    ON c.contract_id = p.contract_id

    WHERE r.bnf_id = ?

    GROUP BY
        r.request_id,
        s.sch_name,
        c.contract_id,
        c.amount,
        c.payments_count

    ORDER BY r.request_id DESC
    LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bnf_id);
$stmt->execute();

$result = $stmt->get_result();
$data = null;

/* جلب البيانات */
if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
}

/* قيم افتراضية */
$sch_name = "لا توجد منحة حالية";
$paid_amount = 0;
$remaining_amount = 0;
$total_amount = 0;
$payments_count = 0;

/* تجهيز البيانات */
if ($data) {

    $sch_name = $data['sch_name'];
    $paid_amount = (float)$data['paid_amount'];
    $total_amount = (float)$data['amount'];
    $payments_count = (int)$data['payments_count'];

    /* المبلغ المتبقي */
    $remaining_amount = $total_amount - $paid_amount;

    /** منع الرقم السالب **/
    if ($remaining_amount < 0) {
        $remaining_amount = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<title>نورين - محفظة منحتي</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">

<style>
/* الصفحة */
body{ margin:0; font-family:'Noto Kufi Arabic',sans-serif; background:#FFFDFB; }

/* التخطيط */
.layout{ display:flex; min-height:100vh; background:#FFFDFB; }

/* المحتوى */
.main-content{ flex:1; background:#FFFDFB; }

/* الهيدر */
.header{ display:flex; justify-content:space-between; align-items:flex-start; padding:28px 34px 20px; border-bottom:1px solid #cfcfcf; background:#FFFDFB; }

/* عنوان الصفحة */
.page-heading{ display:flex; flex-direction:column; gap:8px; }

/* اسم الصفحة */
.page-title{ font-size:22px; font-weight:800; color:#111; }

/* وصف الصفحة */
.page-subtitle{ font-size:17px; color:#222; font-weight:500; }

/* أيقونات الهيدر */
.header-icons{ display:flex; align-items:center; gap:14px; }

/* قائمة الإعدادات */
.settings-dropdown{ position:relative; display:inline-block; }

/* أيقونة القائمة */
.menu-icon{ width:26px; height:26px; cursor:pointer; object-fit:contain; }

/* القائمة المنسدلة */
.dropdown-menu{ display:none; position:absolute; top:34px; left:0; background:#fff; min-width:210px; box-shadow:0 6px 16px rgba(0,0,0,0.12); border:1px solid #e6e6e6; border-radius:10px; overflow:hidden; z-index:1000; }

/* روابط القائمة */
.dropdown-menu a{ display:block; padding:12px 14px; color:#333; text-decoration:none; font-size:14px; background:#fff; }

/* مرور على الرابط */
.dropdown-menu a:hover{ background:#f7f3f9; }

/* إظهار القائمة */
.settings-dropdown:hover .dropdown-menu{ display:block; }

/* تغليف المحفظة */
.wallet-wrapper{ padding:60px 65px; background:#FFFDFB; }

/* بطاقة المحفظة */
.wallet-card{ max-width:980px; margin:0 auto; background:#fff; border:1px solid #e6e0e6; box-shadow:0 2px 10px rgba(0,0,0,0.05); }

/* أعلى البطاقة */
.wallet-top{ text-align:center; padding:28px 25px 10px; }

/* عنوان المنحة */
.wallet-top .title-label{ color:#4D2A67; font-size:18px; font-weight:800; }

/* اسم المنحة */
.wallet-top .title-value{ color:#A8A8A8; font-size:18px; font-weight:700; }

/* صناديق المبالغ */
.money-boxes{ display:flex; justify-content:center; gap:80px; flex-wrap:wrap; padding:28px 20px 38px; }

/* صندوق المبلغ */
.money-box{ width:300px; min-height:185px; background:#FAF7FA; border:1px solid #E9E2EA; border-radius:18px; box-shadow:0 3px 10px rgba(0,0,0,0.06); display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; }

/* عنوان الصندوق */
.money-box h3{ margin:0 0 22px; font-size:20px; color:#4D2A67; font-weight:800; }

/* المبلغ */
.money-box .amount{ font-size:24px; color:#76A8B9; font-weight:800; line-height:1.8; }

/* أسفل البطاقة */
.wallet-bottom{ border-top:1px solid #d7d1d7; display:grid; grid-template-columns:1fr 1fr; }

/* عنصر سفلي */
.bottom-item{ text-align:center; padding:18px 20px 20px; }

/* عنوان العنصر */
.bottom-item .label{ color:#4D2A67; font-size:18px; font-weight:800; margin-bottom:8px; }

/* قيمة العنصر */
.bottom-item .value{ color:#9D9D9D; font-size:18px; font-weight:700; }

/* لا توجد بيانات */
.empty-box{ max-width:980px; margin:0 auto; background:#fff; border:1px solid #e6e0e6; box-shadow:0 2px 10px rgba(0,0,0,0.05); text-align:center; padding:60px 20px; color:#777; font-size:18px; font-weight:600; }

/* تجاوب */
@media (max-width:900px){

    .wallet-wrapper{ padding:30px 20px; }

    .header{ padding:20px; }

    .page-subtitle{ font-size:15px; }

    .money-boxes{ gap:25px; }

    .money-box{ width:100%; max-width:340px; }

    .wallet-bottom{ grid-template-columns:1fr; }
}
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
                <li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
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
                <div class="page-title">محفظة المنحة الحالية</div>
                <div class="page-subtitle">استعراض المبالغ المالية المدفوعة من الجهة الداعمة</div>
            </div>

            <!-- الإعدادات -->
            <div class="header-icons">

                <div class="settings-dropdown">

                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">

                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>

                </div>

            </div>

        </header>

        <!-- محتوى الصفحة -->
        <div class="wallet-wrapper">

            <?php if ($data) { ?>

                <!-- بطاقة المحفظة -->
                <div class="wallet-card">

                    <!-- اسم المنحة -->
                    <div class="wallet-top">
                        <span class="title-label">اسم المنحة:</span>
                        <span class="title-value"><?php echo htmlspecialchars($sch_name); ?></span>
                    </div>

                    <!-- المبالغ -->
                    <div class="money-boxes">

                        <!-- المبالغ المدفوعة -->
                        <div class="money-box">

                            <h3>المبالغ المدفوعة</h3>

                            <div class="amount">
                                <?php echo number_format($paid_amount, 0); ?><br>
                                ريال
                            </div>

                        </div>

                        <!-- المبلغ المتبقي -->
                        <div class="money-box">

                            <h3>المبلغ المتبقي من التمويل</h3>

                            <div class="amount">
                                <?php echo number_format($remaining_amount, 0); ?><br>
                                ريال
                            </div>

                        </div>

                    </div>

                    <!-- أسفل البطاقة -->
                    <div class="wallet-bottom">

                        <!-- إجمالي التمويل -->
                        <div class="bottom-item">
                            <div class="label">إجمالي التمويل</div>
                            <div class="value"><?php echo number_format($total_amount, 0); ?> ريال</div>
                        </div>

                        <!-- عدد الدفعات -->
                        <div class="bottom-item">
                            <div class="label">عدد الدفعات</div>
                            <div class="value"><?php echo $payments_count; ?></div>
                        </div>

                    </div>

                </div>

            <?php } else { ?>

                <!-- لا توجد بيانات -->
                <div class="empty-box">
                    لا توجد بيانات حالية لعرضها
                </div>

            <?php } ?>

        </div>

    </div>
</div>

</body>
</html>