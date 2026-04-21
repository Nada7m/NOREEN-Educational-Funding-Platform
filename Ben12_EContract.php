<?php
session_start();

$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

$bnf_id = (int) $_SESSION['bnf_id'];
$request_id = isset($_GET['request_id']) ? (int) $_GET['request_id'] : 0;
$step = isset($_GET['step']) ? $_GET['step'] : 'view';

if ($request_id <= 0) {
    die("رقم الطلب غير صالح");
}

$contract_data = null;
$user_name = "المستخدم";

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
    INNER JOIN e_contract c
        ON r.request_id = c.request_id
    INNER JOIN beneficiary b
        ON r.bnf_id = b.bnf_id
    WHERE r.request_id = ?
      AND r.bnf_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $bnf_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $contract_data = $result->fetch_assoc();
    $user_name = $contract_data['f_name'] . " " . $contract_data['l_name'];
}

if (!$contract_data) {
    die("لم يتم العثور على العقد");
}

/* معالجة الموافقة النهائية */
if (isset($_POST['confirm_final'])) {
    $conn->begin_transaction();

    try {
        $update_sql = "UPDATE e_contract SET approval_status = 'تمت الموافقة' WHERE request_id = ?";
        $stmt_upd = $conn->prepare($update_sql);
        $stmt_upd->bind_param("i", $request_id);
        $stmt_upd->execute();

        $contract_id = (int) $contract_data['contract_id'];
        $payments_count = (int) $contract_data['payments_count'];
        $total_amount = (float) $contract_data['amount'];

        if ($payments_count > 0) {
            $each_amount = $total_amount / $payments_count;

            $check_sql = "SELECT COUNT(*) AS existing_count FROM payments WHERE contract_id = ?";
            $stmt_check = $conn->prepare($check_sql);
            $stmt_check->bind_param("i", $contract_id);
            $stmt_check->execute();
            $check_result = $stmt_check->get_result();
            $existing_count = (int) $check_result->fetch_assoc()['existing_count'];

            if ($existing_count === 0) {
                $payment_status = "بانتظار الدفع";

                for ($i = 1; $i <= $payments_count; $i++) {
                    $insert_sql = "
                        INSERT INTO payments (contract_id, installment_number, payment_amount, payment_status)
                        VALUES (?, ?, ?, ?)
                    ";
                    $stmt_insert = $conn->prepare($insert_sql);
                    $stmt_insert->bind_param("iids", $contract_id, $i, $each_amount, $payment_status);
                    $stmt_insert->execute();
                }
            }
        }

        $conn->commit();
        header("Location: Ben12_EContract.php?request_id=" . $request_id . "&step=view");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("خطأ في النظام: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>العقد الإلكتروني</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css?v=4">
    <style>
        .page {
            padding: 30px 20px;
        }

        .contract-layout {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 20px;
            align-items: flex-start;
        }

        .right-col {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .left-col {
            display: flex;
            flex-direction: column;
        }

        .card {
            background: #FFFFFF;
            border: 1px solid #EAEAEA;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .card-h {
            color: #3E2454;
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #F0F0F0;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-line:last-child {
            margin-bottom: 0;
        }

        .info-line label {
            color: #70A0AF;
            font-weight: 700;
            flex-shrink: 0;
        }

        .info-line span {
            color: #333333;
            font-weight: 600;
            text-align: left;
        }

        .terms-box {
            font-size: 14px;
            line-height: 2;
            color: #555555;
            white-space: pre-line;
        }

        .agree-text {
            font-size: 13px;
            color: #444444;
            line-height: 1.8;
            margin-bottom: 20px;
            text-align: right;
        }

        .btn-blue {
            background: #3E2454;
            color: #FFFFFF;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            display: block;
            text-align: center;
            text-decoration: none;
            font-family: "Noto Kufi Arabic", sans-serif;
        }

        .signed-msg {
            background: #D4F4E2;
            color: #55A082;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            text-align: center;
        }

        .modal-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-inner {
            background: #FFFFFF;
            padding: 35px;
            border-radius: 16px;
            text-align: center;
            max-width: 430px;
            width: calc(100% - 30px);
        }

        .warn-icon {
            width: 52px;
            height: 52px;
            background: #C96B6B;
            color: #FFFFFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            font-weight: 700;
        }

        .modal-text {
            font-size: 14px;
            color: #444444;
            line-height: 1.9;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 22px;
        }

        .m-btn {
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            font-size: 14px;
            font-family: "Noto Kufi Arabic", sans-serif;
        }

        .m-btn-confirm {
            background: #69B38A;
            color: #FFFFFF;
        }

        .m-btn-cancel {
            background: #F88A8A;
            color: #FFFFFF;
        }
.backbtn{
  display:flex;
  align-items:center;
  margin-left:10px;
}

.backicon{
  width:32px;
  cursor:pointer;
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
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php" class="active">متابعة المنح</a></li>
                <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
                <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
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
    <div class="page-title">متابعة المنح</div>
    <div class="page-description">صفحة العقد الإلكتروني</div>
  </div>

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

    <a href="Ben09_TrackScholarship.php" class="backbtn">
      <img src="سهم تراجع.svg" class="backicon" alt="رجوع">
    </a>
        <div class="page">
            <div class="contract-layout">
                <div class="right-col">
                    <div class="card">
                        <div class="card-h">ملخص العقد</div>

                        <div class="info-line">
                            <label>قيمة التمويل:</label>
                            <span><?php echo number_format($contract_data['amount']); ?> ريال</span>
                        </div>

                        <div class="info-line">
                            <label>مدة التمويل:</label>
                            <span><?php echo htmlspecialchars($contract_data['funding_duration']); ?> سنوات</span>
                        </div>

                        <div class="info-line">
                            <label>عدد الدفعات:</label>
                            <span><?php echo (int) $contract_data['payments_count']; ?> دفعات</span>
                        </div>

                        <div class="info-line">
                            <label>اسم الطالب:</label>
                            <span><?php echo htmlspecialchars($user_name); ?></span>
                        </div>

                        <div class="info-line">
                            <label>رقم العقد:</label>
                            <span><?php echo (int) $contract_data['contract_id']; ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-h">الإقرار والموافقة</div>

                        <p class="agree-text">
                            أقر أنا <b><?php echo htmlspecialchars($user_name); ?></b> على الشروط والأحكام الواردة في هذا العقد وأتعهد بالالتزام بها تمامًا.
                        </p>

                        <?php if ($contract_data['approval_status'] === 'تمت الموافقة') { ?>
                            <div class="signed-msg">تمت الموافقة ✓</div>
                        <?php } else { ?>
                            <a href="Ben12_EContract.php?request_id=<?php echo $request_id; ?>&step=confirm" class="btn-blue">أوافق على الشروط والأحكام</a>
                        <?php } ?>
                    </div>
                </div>

                <div class="left-col">
                    <div class="card">
                        <div class="card-h">شروط العقد</div>
                        <div class="terms-box"><?php echo nl2br(htmlspecialchars($contract_data['terms'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($step === 'confirm' && $contract_data['approval_status'] !== 'تمت الموافقة') { ?>
<div class="modal-bg">
    <div class="modal-inner">
        <div class="warn-icon">!</div>

        <div class="modal-text">
            بموافقتك على هذا العقد، فإنك تقر بالتزامك الكامل بجميع الشروط وتتحمل المسؤولية النظامية في حال الإخلال بها.
        </div>

        <div class="modal-buttons">
            <form method="POST" action="Ben12_EContract.php?request_id=<?php echo $request_id; ?>">
                <button type="submit" name="confirm_final" class="m-btn m-btn-confirm">موافق</button>
            </form>

            <a href="Ben12_EContract.php?request_id=<?php echo $request_id; ?>&step=view" class="m-btn m-btn-cancel">تراجع</a>
        </div>
    </div>
</div>
<?php } ?>
</body>
</html>