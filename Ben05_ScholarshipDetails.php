<?php
session_start();

$conn = new mysqli("localhost", "root", "", "noreen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

$bnf_id = (int) $_SESSION['bnf_id'];
$sch_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($sch_id <= 0) {
    die("رقم المنحة غير صحيح");
}

$details_sql = "
    SELECT s.*, i.inv_name
    FROM scholarship_opps s
    LEFT JOIN investor i ON s.inv_id = i.inv_id
    WHERE s.scholarship_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($details_sql);
$stmt->bind_param("i", $sch_id);
$stmt->execute();
$result = $stmt->get_result();
$opp = $result->fetch_assoc();

if (!$opp) {
    die("المنحة غير موجودة");
}

$student_sql = "SELECT degree_level FROM beneficiary WHERE bnf_id = ? LIMIT 1";
$stmt_student = $conn->prepare($student_sql);
$stmt_student->bind_param("i", $bnf_id);
$stmt_student->execute();
$student_result = $stmt_student->get_result();
$student = $student_result->fetch_assoc();

$provider_name = !empty($opp['inv_name']) ? $opp['inv_name'] : 'غير محدد';

$can_apply = true;
$apply_message = "";

$accepted_sql = "
    SELECT request_id
    FROM scholarship_requests
    WHERE bnf_id = ?
      AND request_status = 'مقبول'
    LIMIT 1
";
$stmt_accepted = $conn->prepare($accepted_sql);
$stmt_accepted->bind_param("i", $bnf_id);
$stmt_accepted->execute();
$accepted_result = $stmt_accepted->get_result();

if ($accepted_result->num_rows > 0) {
    $can_apply = false;
    $apply_message = "لا يمكنك التقديم لوجود منحة سارية لديك حاليًا";
}

if ($can_apply && $student) {
    $degree_level = trim($student['degree_level']);
    $study_level = trim($opp['study_level']);

    if ($degree_level == 'ثانوي' && ($study_level == 'ماجستير' || $study_level == 'دكتوراه')) {
        $can_apply = false;
        $apply_message = "هذه المنحة غير متوافقة مع مؤهلك";
    }

    if ($degree_level == 'بكالوريوس' && $study_level == 'دكتوراه') {
        $can_apply = false;
        $apply_message = "هذه المنحة غير متوافقة مع مؤهلك";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل المنحة</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css?v=4">
<style>
.page {
  padding: 20px 40px;
  position: relative;
}

.page-top {
  display: flex;
  justify-content: flex-end;
  padding: 0 0 10px 0;
  margin: 0;
}

.back-btn-details {
  display: inline-block;
  cursor: pointer;
  text-decoration: none;
  background: none;
  border: none;
}

.back-btn-details img {
  width: 38px;
  height: 38px;
  display: block;
}

.details-box {
  background: #FFFFFF;
  border: 1px solid #E5E5E5;
  border-radius: 16px;
  padding: 28px 30px;
  margin-top: 5px;
  text-align: right;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  max-width: 980px;
  margin-right: auto;
  margin-left: auto;
}

.det-header-title {
  color: #3E2454;
  font-size: 20px;
  font-weight: 700;
  text-align: center;
  margin-bottom: 26px;
  line-height: 1.8;
}

.det-top-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  align-items: stretch;
  margin-bottom: 20px;
}

.det-box {
  background: #FAFAFA;
  border: 1px solid #E8E8E8;
  border-radius: 12px;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-height: 86px;
  justify-content: center;
}

.det-label {
  color: #6B6B6B;
  font-size: 14px;
  font-weight: 600;
}

.det-value {
  color: #70A0AF;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.8;
}

.det-specialization-box {
  background: #FAFAFA;
  border: 1px solid #E8E8E8;
  border-radius: 12px;
  padding: 16px 18px;
  margin-bottom: 18px;
}

.det-specialization-title {
  color: #595959;
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 8px;
}

.det-specialization-text {
  color: #70A0AF;
  font-size: 14px;
  font-weight: 600;
  line-height: 2;
  white-space: normal;
  word-break: break-word;
}

.section-divider {
  border-top: 1px solid #D9D9D9;
  margin: 18px 0 20px;
}

.conditions-sec {
  margin-top: 10px;
  text-align: right;
}

.conditions-title {
  margin: 0 0 14px 0;
  color: #000;
  font-size: 15px;
  font-weight: 700;
}

.conditions-text {
  font-size: 13px;
  color: #555;
  line-height: 1.8;
  text-align: right;
  white-space: pre-wrap;
}

.apply-btn-wrap {
  text-align: center;
  margin-top: 34px;
}

.btn-action {
  width: 250px;
  background: #70A0AF;
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
  text-decoration: none;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-family: inherit;
}

.apply-note {
  max-width: 520px;
  margin: 34px auto 0;
  background: #FFF4E5;
  color: #8A5A00;
  border: 1px solid #F0D19B;
  border-radius: 8px;
  padding: 14px 18px;
  text-align: center;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.8;
}

@media (max-width: 900px) {
  .page {
    padding: 20px;
  }

  .det-top-grid {
    grid-template-columns: 1fr;
  }
}
</style>
</head>

<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
            <ul class="sidebar-menu">
                <li><a href="Ben00_MainPage.php">الرئيسية</a></li>
                <li><a href="Ben04_BrowseScholarships.php" class="active">التقديم على المنح</a></li>
                <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
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
                <div class="page-title">تفاصيل المنحة</div>
                <div class="page-description">صفحة استعراض تفاصيل المنحة قبل التقديم</div>
            </div>

            <div class="header-icons">
                <div class="settings-dropdown">
                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben03_MyScholarshipWallet.php">محفظة منحتي</a>
                        <a href="support.php">تقديم شكوى او استفسار</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page">
            <div class="page-top">
                <a href="javascript:history.back()" class="back-btn-details">
                    <img src="سهم تراجع.svg" alt="رجوع">
                </a>
            </div>

            <div class="details-box">
                <div class="det-header-title">اسم المنحة: <?php echo htmlspecialchars($opp['sch_name']); ?></div>

                <div class="det-top-grid">
                    <div class="det-box">
                        <div class="det-label">الجهة المانحة:</div>
                        <div class="det-value"><?php echo htmlspecialchars($provider_name); ?></div>
                    </div>

                    <div class="det-box">
                        <div class="det-label">الدرجة المستهدفة:</div>
                        <div class="det-value"><?php echo htmlspecialchars($opp['study_level']); ?></div>
                    </div>
                </div>

                <div class="det-top-grid">
                    <div class="det-box">
                        <div class="det-label">التخصص الرئيسي:</div>
                        <div class="det-value"><?php echo htmlspecialchars($opp['sch_field']); ?></div>
                    </div>

                    <div class="det-box">
                        <div class="det-label">آخر موعد للتقديم:</div>
                        <div class="det-value"><?php echo date("Y-m-d", strtotime($opp['app_deadline'])); ?></div>
                    </div>
                </div>

                <div class="det-specialization-box">
                    <div class="det-specialization-title">التخصصات الدقيقة:</div>
                    <div class="det-specialization-text"><?php echo htmlspecialchars($opp['Specializations']); ?></div>
                </div>

                <div class="section-divider"></div>

                <div class="conditions-sec">
                    <h4 class="conditions-title">الشروط:</h4>
                    <div class="conditions-text"><?php echo nl2br(htmlspecialchars($opp['requirements'])); ?></div>
                </div>

                <div class="apply-btn-wrap">
                    <?php if ($can_apply) { ?>
                        <a href="Ben06_ApplyScholarship.php?sch_id=<?php echo $opp['scholarship_id']; ?>" class="btn-action">
                            التقديم الآن
                        </a>
                    <?php } else { ?>
                        <div class="apply-note"><?php echo $apply_message; ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>