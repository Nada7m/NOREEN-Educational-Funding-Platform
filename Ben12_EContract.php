<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال");
}
$conn->set_charset("utf8mb4");

$request_id = $_GET['request_id'] ?? null;
$bnf_id = $_SESSION['bnf_id'] ?? null;
$step = $_GET['step'] ?? 'view';

$contract_data = null;
$user_name = "المستخدم";

if ($request_id && $bnf_id) {
    $sql = "
        SELECT r.request_id, r.major_name, r.univ_name,
               c.contract_id, c.amount, c.terms, c.funding_duration, c.payments_count, c.approval_status,
               b.f_name, b.l_name
        FROM scholarship_requests r
        JOIN e_contract c ON r.request_id = c.request_id
        JOIN beneficiary b ON r.bnf_id = b.bnf_id
        WHERE r.request_id = ? AND r.bnf_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $bnf_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $contract_data = $result->fetch_assoc();
        $user_name = $contract_data['f_name'] . " " . $contract_data['l_name'];
    }
}

if (isset($_POST['confirm_final']) && $contract_data) {
    $conn->begin_transaction();

    try {
        $update_sql = "UPDATE e_contract SET approval_status = 'تمت الموافقة' WHERE request_id = ?";
        $stmt_upd = $conn->prepare($update_sql);
        $stmt_upd->bind_param("i", $contract_data['request_id']);
        $stmt_upd->execute();

        $c_id = $contract_data['contract_id'];
        $p_count = $contract_data['payments_count'];
        $total_amt = $contract_data['amount'];
        $each_amt = $total_amt / $p_count;

        $check_existing = $conn->query("SELECT COUNT(*) as existing FROM payments WHERE contract_id = $c_id");

        if ($check_existing->fetch_assoc()['existing'] == 0) {
            $status_default = "بانتظار الدفع";

            for ($i = 1; $i <= $p_count; $i++) {
                $ins_p = $conn->prepare("INSERT INTO payments (contract_id, installment_number, payment_amount, payment_status) VALUES (?, ?, ?, ?)");
                $ins_p->bind_param("iids", $c_id, $i, $each_amt, $status_default);
                $ins_p->execute();
            }
        }

        $conn->commit();
        header("Location: Ben12_EContract.php?request_id=" . $request_id . "&step=view");
        exit();
    } catch (Exception $e) {
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
        .page{
          padding:30px;
        }

        .page-top{
          display:flex;
          justify-content:flex-end;
          align-items:center;
          padding:0;
          margin-bottom:20px;
        }

        .content-grid{
          display:grid;
          grid-template-columns:340px 1fr;
          gap:20px;
          align-items:start;
        }

        .side-col{
          display:flex;
          flex-direction:column;
          gap:20px;
        }

        .info-card{
          background:#FFFFFF;
          border:1px solid #EAEAEA;
          border-radius:16px;
          padding:24px;
          box-shadow:0 2px 8px rgba(0,0,0,0.04);
        }

        .info-card h3{
          color:#3E2454;
          font-size:16px;
          font-weight:700;
          text-align:center;
          margin-bottom:18px;
          padding-bottom:10px;
          border-bottom:1px solid #F0F0F0;
        }

        .contract-box{
          direction:rtl;
          text-align:right;
        }

        .data-row{
          display:flex;
          align-items:center;
          justify-content:space-between;
          gap:12px;
          margin-bottom:14px;
          font-size:14px;
        }

        .data-row:last-child{
          margin-bottom:0;
        }

        .data-row label{
          color:#70A0AF;
          font-weight:700;
          flex-shrink:0;
        }

        .data-row span{
          color:#333333;
          font-weight:600;
          text-align:left;
        }

        .terms-card{
          background:#FFFFFF;
          border:1px solid #EAEAEA;
          border-radius:16px;
          box-shadow:0 2px 8px rgba(0,0,0,0.04);
          padding:24px;
          min-height:100%;
        }

        .terms-card h3{
          color:#3E2454;
          font-size:16px;
          font-weight:700;
          text-align:center;
          margin-bottom:18px;
          padding-bottom:10px;
          border-bottom:1px solid #F0F0F0;
        }

        .terms-box{
          font-size:14px;
          line-height:2;
          color:#555555;
          white-space:pre-line;
          text-align:right;
        }

        .agree-text{
          font-size:14px;
          color:#444444;
          line-height:1.9;
          text-align:right;
          margin-bottom:18px;
        }

        .btn-approve{
          width:100%;
          background:#3E2454;
          color:#FFFFFF;
          border:none;
          border-radius:12px;
          padding:12px 16px;
          font-size:14px;
          font-weight:700;
          cursor:pointer;
          text-decoration:none;
          display:block;
          text-align:center;
          transition:.3s;
        }

        .btn-approve:hover{
          background:#523067;
        }

        .approved-box{
          display:inline-block;
          width:100%;
          text-align:center;
          background:#D4F4E2;
          color:#55A082;
          padding:12px 16px;
          border-radius:20px;
          font-size:13px;
          font-weight:700;
        }

        .modal-bg{
          position:fixed;
          top:0;
          left:0;
          width:100%;
          height:100%;
          background:rgba(0,0,0,0.45);
          display:flex;
          align-items:center;
          justify-content:center;
          z-index:9999;
          padding:20px;
        }

        .modal-box{
          width:100%;
          max-width:430px;
          background:#FFFFFF;
          border-radius:18px;
          padding:30px 26px;
          text-align:center;
          box-shadow:0 8px 24px rgba(0,0,0,0.15);
        }

        .modal-icon{
          width:58px;
          height:58px;
          border-radius:50%;
          background:#C96B6B;
          color:#FFFFFF;
          display:flex;
          align-items:center;
          justify-content:center;
          font-size:28px;
          font-weight:700;
          margin:0 auto 16px;
        }

        .modal-text{
          font-size:14px;
          color:#444444;
          line-height:1.9;
          margin-bottom:22px;
        }

        .modal-actions{
          display:flex;
          justify-content:center;
          gap:10px;
          flex-wrap:wrap;
        }

        .modal-btn{
          min-width:120px;
          padding:10px 18px;
          border:none;
          border-radius:10px;
          font-size:14px;
          font-weight:700;
          cursor:pointer;
          text-decoration:none;
        }

        .confirm-btn{
          background:#69B38A;
          color:#FFFFFF;
        }

        .cancel-btn{
          background:#F3E6DD;
          color:#3E2454;
        }

        .empty-box{
          background:#FFFFFF;
          border:1px solid #EAEAEA;
          border-radius:16px;
          padding:30px;
          box-shadow:0 2px 8px rgba(0,0,0,0.04);
          text-align:center;
          color:#666666;
          font-size:14px;
          font-weight:600;
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

        <div class="page">
            <div class="page-top">
                <a href="Ben09_TrackScholarship.php" class="backbtn">
                    <img src="سهم تراجع.svg" class="backicon" alt="رجوع">
                </a>
            </div>

            <?php if ($contract_data): ?>
                <div class="content-grid">

                    <div class="side-col">
                        <div class="info-card contract-box">
                            <h3>ملخص العقد</h3>

                            <div class="data-row">
                                <label>رقم العقد:</label>
                                <span>CNT-<?php echo $contract_data['contract_id']; ?></span>
                            </div>

                            <div class="data-row">
                                <label>اسم الطالب:</label>
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

                        <div class="info-card contract-box">
                            <h3>الإقرار والموافقة</h3>

                            <div class="agree-text">
                                أقر أنا <b><?php echo htmlspecialchars($user_name); ?></b> على الشروط والأحكام الواردة في هذا العقد، وأتعهد بالالتزام بها التزامًا كاملًا.
                            </div>

                            <?php if ($contract_data['approval_status'] === 'تمت الموافقة'): ?>
                                <div class="approved-box">تمت الموافقة ✓</div>
                            <?php else: ?>
                                <a href="?request_id=<?php echo $request_id; ?>&step=confirm" class="btn-approve">أوافق على الشروط والأحكام</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="terms-card">
                        <h3>شروط العقد</h3>
                        <div class="terms-box"><?php echo nl2br(htmlspecialchars($contract_data['terms'])); ?></div>
                    </div>

                </div>
            <?php else: ?>
                <div class="empty-box">لا توجد بيانات عقد متاحة لهذا الطلب.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($step == 'confirm' && $contract_data): ?>
    <div class="modal-bg">
        <div class="modal-box">
            <div class="modal-icon">!</div>

            <div class="modal-text">
                بموافقتك على هذا العقد، فإنك تقر بالتزامك الكامل بجميع الشروط والأحكام، وتتحمل المسؤولية النظامية في حال الإخلال بها.
            </div>

            <div class="modal-actions">
                <form method="POST" action="Ben12_EContract.php?request_id=<?php echo $request_id; ?>">
                    <button type="submit" name="confirm_final" class="modal-btn confirm-btn">موافق</button>
                </form>

                <a href="?request_id=<?php echo $request_id; ?>&step=view" class="modal-btn cancel-btn">تراجع</a>
            </div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>