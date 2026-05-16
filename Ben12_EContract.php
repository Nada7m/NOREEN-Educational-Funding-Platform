<?php
session_start();

/** التحقق من تشغيل الجلسة قبل استخدامها **/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال");
}

/* دعم اللغة العربية */
$conn->set_charset("utf8mb4");

/* رقم الطلب الحالي */
$request_id = $_GET['request_id'] ?? null;

/* رقم المستفيد الحالي */
$bnf_id = $_SESSION['bnf_id'] ?? null;

/* تحديد حالة النافذة الحالية */
$step = $_GET['step'] ?? 'view';

/* بيانات العقد */
$contract_data = null;

/* الاسم الافتراضي */
$user_name = "المستخدم";

/** جلب بيانات العقد المرتبط بالمستفيد الحالي **/
if ($request_id && $bnf_id) {

    $sql = "
        SELECT
            r.request_id,
            r.major_name,
            r.univ_name,
            c.contract_id,
            c.amount,
            c.terms,
            c.funding_duration,
            c.payments_count,
            c.approval_status,
            b.f_name,
            b.l_name
        FROM scholarship_requests r
        JOIN e_contract c
            ON r.request_id = c.request_id
        JOIN beneficiary b
            ON r.bnf_id = b.bnf_id
        WHERE r.request_id = ?
        AND r.bnf_id = ?
    ";

    $stmt = $conn->prepare($sql);

    /* ربط رقم الطلب والمستفيد بالاستعلام */
    $stmt->bind_param("ii", $request_id, $bnf_id);

    /* تنفيذ الاستعلام */
    $stmt->execute();

    /* تحويل النتائج إلى بيانات */
    $result = $stmt->get_result();

    /** تخزين بيانات العقد لاستخدامها داخل الصفحة **/
    if ($result->num_rows > 0) {

        $contract_data = $result->fetch_assoc();

        $user_name = $contract_data['f_name'] . " " . $contract_data['l_name'];
    }
}

/* تنفيذ الموافقة النهائية على العقد */
if (isset($_POST['confirm_final']) && $contract_data) {

    /* بدء تنفيذ العمليات كعملية واحدة */
    $conn->begin_transaction();

    try {

        /** تحديث حالة العقد إلى تمت الموافقة **/
        $update_sql = "
            UPDATE e_contract
            SET approval_status = 'تمت الموافقة'
            WHERE request_id = ?
        ";

        $stmt_upd = $conn->prepare($update_sql);

        $stmt_upd->bind_param("i", $contract_data['request_id']);

        $stmt_upd->execute();

        /* رقم العقد */
        $c_id = $contract_data['contract_id'];

        /* عدد الدفعات */
        $p_count = $contract_data['payments_count'];

        /* المبلغ الكلي */
        $total_amt = $contract_data['amount'];

        /* حساب قيمة كل دفعة */
        $each_amt = $total_amt / $p_count;

        /* التحقق من وجود دفعات مسبقة */
        $check_existing = $conn->query("
            SELECT COUNT(*) as existing
            FROM payments
            WHERE contract_id = $c_id
        ");

        /** إنشاء الدفعات فقط إذا لم تكن موجودة مسبقًا **/
        if ($check_existing->fetch_assoc()['existing'] == 0) {

            $status_default = "بانتظار الدفع";

            /* إنشاء جميع الدفعات حسب العدد المحدد */
            for ($i = 1; $i <= $p_count; $i++) {

                $ins_p = $conn->prepare("
                    INSERT INTO payments
                    (contract_id, installment_number, payment_amount, payment_status)
                    VALUES (?, ?, ?, ?)
                ");

                $ins_p->bind_param("iids", $c_id, $i, $each_amt, $status_default);

                $ins_p->execute();
            }
        }

        /* حفظ جميع العمليات */
        $conn->commit();

        header("Location: Ben12_EContract.php?request_id=" . $request_id . "&step=view");

        exit();

    } catch (Exception $e) {

        /* التراجع عن العمليات عند حدوث خطأ */
        $conn->rollback();

        echo "خطأ في النظام: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<title>نورين - العقد الإلكتروني</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="CSS01Layout.css?v=4">

<style>

/* الصفحة */
.page{ padding:20px 25px; }

/* الشريط العلوي */
.page-top{ display:flex; justify-content:flex-end; padding:0 0 10px 0; margin:0; }

/* زر التراجع */
.backbtn{ display:inline-block; cursor:pointer; text-decoration:none; background:none; border:none; margin-left:20px; }

/* أيقونة التراجع */
.backicon{ width:40px; height:40px; display:block; }

/* توزيع المحتوى */
.content-grid{ display:flex; gap:25px; align-items:flex-start; }

/* العمود الجانبي */
.side-col{ flex:1; display:flex; flex-direction:column; gap:20px; }

/* البطاقات */
.info-card,.terms-card{ background:#FFFFFF; border-radius:12px; padding:25px; border:1px solid #E0E0E0; box-shadow:0 2px 4px rgba(0,0,0,0.05); }

/* بطاقة الشروط */
.terms-card{ flex:1.8; }

/* عناوين البطاقات */
.info-card h3,.terms-card h3{ font-size:16px; font-weight:700; color:#333333; margin-bottom:20px; text-align:center; border-bottom:1px solid #F0F0F0; padding-bottom:10px; }

/* صندوق بيانات العقد */
.contract-box{ direction:rtl; text-align:right; }

/* صف البيانات */
.data-row{ display:flex; justify-content:flex-start; gap:15px; margin-bottom:15px; font-size:13.5px; }

/* إزالة المسافة من آخر صف */
.data-row:last-child{ margin-bottom:0; }

/* عنوان الحقل */
.data-row label{ color:#8EB4C2; font-weight:600; min-width:110px; }

/* قيمة الحقل */
.data-row span{ color:#666666; font-weight:500; text-align:right; }

/* النصوص */
.terms-box,.agree-text{ font-size:13.5px; line-height:1.8; color:#555555; text-align:right; }

/* نص الشروط */
.terms-box{ white-space:pre-line; }

/* نص الإقرار */
.agree-text{ margin-bottom:18px; }

/* زر الموافقة */
.btn-approve{ width:100%; height:48px; background:#472764; color:#FFFFFF; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; text-decoration:none; display:flex; align-items:center; justify-content:center; }

/* تأثير المرور */
.btn-approve:hover{ background:#3F2556; }

/* حالة الموافقة */
.approved-box{ display:block; width:100%; text-align:center; background:#D4F4E2; color:#55A082; padding:12px; border-radius:8px; font-size:14px; font-weight:700; }

/* خلفية المودال */
.modal-bg{ position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:9999; padding:20px; }

/* صندوق المودال */
.modal-box{ width:100%; max-width:430px; background:#FFFFFF; border-radius:18px; padding:30px 26px; text-align:center; box-shadow:0 8px 24px rgba(0,0,0,0.15); }

/* أيقونة المودال */
.modal-icon{ width:58px; height:58px; border-radius:50%; background:#C96B6B; color:#FFFFFF; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; margin:0 auto 16px; }

/* نص المودال */
.modal-text{ font-size:14px; color:#444444; line-height:1.9; margin-bottom:22px; }

/* أزرار المودال */
.modal-actions{ display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }

/* الأزرار */
.modal-btn{ min-width:120px; padding:10px 18px; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; text-decoration:none; }

/* زر التأكيد */
.confirm-btn{ background:#69B38A; color:#FFFFFF; }

/* زر التراجع */
.cancel-btn{ background:#F3E6DD; color:#3E2454; }

/* رسالة عدم وجود بيانات */
.empty-box{ background:#FFFFFF; border:1px solid #E0E0E0; border-radius:12px; padding:30px; box-shadow:0 2px 4px rgba(0,0,0,0.05); text-align:center; color:#666666; font-size:14px; font-weight:600; }
</style>

</head>

<body>

<div class="layout">

    <!-- الشريط الجانبي -->
    <aside class="sidebar">

        <div class="sidebar-top">

            <!-- شعار النظام -->
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="نورين">
            </div>

            <!-- روابط التنقل -->
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
                <li><a href="Ben19_Consultations.php">الاستشارات</a></li>
            </ul>

        </div>

        <!-- زر تسجيل الخروج -->
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

        <!-- الهيدر -->
        <header class="header">

            <div class="page-heading">

                <div class="page-title">متابعة المنح</div>

                <div class="page-description">
                    صفحة العقد الإلكتروني
                </div>

            </div>

            <!-- قائمة الإعدادات -->
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

        <div class="page">

            <!-- زر الرجوع -->
            <div class="page-top">

                <a href="Ben09_TrackScholarship.php" class="backbtn">
                    <img src="سهم تراجع.svg" class="backicon" alt="رجوع">
                </a>

            </div>

            <?php if ($contract_data): ?>

            <!-- توزيع محتوى الصفحة -->
            <div class="content-grid">

                <!-- العمود الجانبي -->
                <div class="side-col">

                    <!-- بطاقة ملخص العقد -->
                    <div class="info-card contract-box">

                        <h3>ملخص العقد</h3>

                        <div class="data-row">
                            <label>رقم العقد:</label>
                            <span><?php echo $contract_data['contract_id']; ?></span>
                        </div>

                        <div class="data-row">
                            <label>اسم المستفيد:</label>
                            <span><?php echo htmlspecialchars($user_name); ?></span>
                        </div>

                        <div class="data-row">
                            <label>قيمة التمويل:</label>
                            <span><?php echo number_format($contract_data['amount']); ?> ريال</span>
                        </div>

                        <div class="data-row">
                            <label>مدة التمويل:</label>
                            <span><?php echo htmlspecialchars($contract_data['funding_duration']); ?> سنوات</span>
                        </div>

                        <div class="data-row">
                            <label>عدد الدفعات:</label>
                            <span><?php echo htmlspecialchars($contract_data['payments_count']); ?> دفعات</span>
                        </div>

                    </div>

                    <!-- بطاقة الموافقة -->
                    <div class="info-card contract-box">

                        <h3>الإقرار والموافقة</h3>

                        <div class="agree-text">

                            أقر أنا
                            <b><?php echo htmlspecialchars($user_name); ?></b>
                            على الشروط والأحكام الواردة في هذا العقد، وأتعهد بالالتزام بها التزامًا كاملًا.

                        </div>

                        <?php if ($contract_data['approval_status'] === 'تمت الموافقة'): ?>

                        <!-- حالة الموافقة -->
                        <div class="approved-box">
                            تمت الموافقة ✓
                        </div>

                        <?php else: ?>

                        <!-- زر الموافقة -->
                        <a href="?request_id=<?php echo $request_id; ?>&step=confirm" class="btn-approve">
                            أوافق على الشروط والأحكام
                        </a>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- بطاقة الشروط -->
                <div class="terms-card">

                    <h3>شروط العقد</h3>

                    <div class="terms-box">
                        <?php echo nl2br(htmlspecialchars($contract_data['terms'])); ?>
                    </div>

                </div>

            </div>

            <?php else: ?>

            <!-- رسالة عدم وجود عقد -->
            <div class="empty-box">
                لا توجد بيانات عقد متاحة لهذا الطلب.
            </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php if ($step == 'confirm' && $contract_data): ?>

<!-- نافذة تأكيد الموافقة -->
<div class="modal-bg">

    <div class="modal-box">

        <!-- أيقونة التنبيه -->
        <div class="modal-icon">
            !
        </div>

        <!-- نص التنبيه -->
        <div class="modal-text">

            بموافقتك على هذا العقد، فإنك تقر بالتزامك الكامل بجميع الشروط والأحكام،
            وتتحمل المسؤولية النظامية في حال الإخلال بها.

        </div>

        <!-- أزرار المودال -->
        <div class="modal-actions">

            <!-- زر الموافقة النهائية -->
            <form method="POST" action="Ben12_EContract.php?request_id=<?php echo $request_id; ?>">

                <button type="submit" name="confirm_final" class="modal-btn confirm-btn">
                    موافق
                </button>

            </form>

            <!-- زر التراجع -->
            <a href="?request_id=<?php echo $request_id; ?>&step=view" class="modal-btn cancel-btn">
                تراجع
            </a>

        </div>

    </div>

</div>

<?php endif; ?>

</body>
</html>