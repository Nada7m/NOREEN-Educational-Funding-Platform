<?php
session_start();

/* التأكد أن المستثمر مسجل دخول */
if (!isset($_SESSION['inv_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

/* ضبط الترميز للعربي */
mysqli_set_charset($con, "utf8mb4");

/* رقم المستثمر الحالي */
$inv_id = $_SESSION['inv_id'];

/* التأكد أن رقم المنحة موجود في الرابط */
if (!isset($_GET['id']) || $_GET['id'] == "") {
    die("رقم المنحة غير موجود.");
}

/* تحويل رقم المنحة إلى رقم صحيح */
$scholarship_id = (int)$_GET['id'];

/* هذا الجزء خاص بتحديث حالة الطلب عند الضغط على قبول أو رفض */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action_type"]) && isset($_POST["request_id"])) {

    $request_id = (int)$_POST["request_id"];
    $action_type = $_POST["action_type"];

    if ($action_type == "accept") {
        $new_status = "مقبول";
    } elseif ($action_type == "reject") {
        $new_status = "مرفوض";
    } else {
        $new_status = "";
    }

    /* تحديث حالة الطلب */
    if ($new_status != "") {
        $update_stmt = mysqli_prepare($con, "UPDATE scholarship_requests
                                             SET request_status = ?
                                             WHERE request_id = ? AND scholarship_id = ?");

        mysqli_stmt_bind_param($update_stmt, "sii", $new_status, $request_id, $scholarship_id);
        mysqli_stmt_execute($update_stmt);
    }
}

/* جلب بيانات المنحة */
$stmt = mysqli_prepare($con, "SELECT scholarship_id, sch_name, sch_field, study_level, Specializations, requirements, app_deadline
                              FROM scholarship_opps
                              WHERE scholarship_id = ? AND inv_id = ?");

mysqli_stmt_bind_param($stmt, "ii", $scholarship_id, $inv_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$scholarship = mysqli_fetch_assoc($result);

/* إذا لم نجد المنحة */
if (!$scholarship) {
    die("لم يتم العثور على بيانات هذه المنحة.");
}

/* جلب الطلبات الخاصة بهذه المنحة مع الاسم الأول والاسم الأخير */
$applicants = [];

$app_stmt = mysqli_prepare($con, "SELECT
                                    scholarship_requests.request_id,
                                    scholarship_requests.univ_name,
                                    scholarship_requests.major_name,
                                    scholarship_requests.request_status,
                                    scholarship_requests.Submit_date,
                                    beneficiary.f_name,
                                    beneficiary.l_name
                                  FROM scholarship_requests
                                  LEFT JOIN beneficiary
                                    ON scholarship_requests.bnf_id = beneficiary.bnf_id
                                  WHERE scholarship_requests.scholarship_id = ?
                                  ORDER BY scholarship_requests.request_id DESC");

mysqli_stmt_bind_param($app_stmt, "i", $scholarship_id);
mysqli_stmt_execute($app_stmt);
$app_result = mysqli_stmt_get_result($app_stmt);

while ($row = mysqli_fetch_assoc($app_result)) {
    $applicants[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تفاصيل المنحة</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="CSS01Layout.css?v=3">

  <style>
    .back-btn{
      width:34px;
      height:34px;
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .back-icon{
      width:24px;
      height:24px;
      object-fit:contain;
    }

    .tabs-row{
      width:90%;
      margin:0 auto 18px;
      display:flex;
      flex-direction:row-reverse;
      justify-content:center;
    }

    .tab{
      width:50%;
      height:64px;
      border:1px solid #D9D9D9;
      display:flex;
      justify-content:center;
      align-items:center;
      font-size:18px;
      font-weight:700;
      color:#3E2454;
      background:#FFFFFF;
      cursor:pointer;
      transition:0.2s;
    }

    .tab-active{
      background:#F2F2F2;
    }

    .tab-content{
      display:none;
    }

    .tab-content.active{
      display:block;
    }

    .scholarship-details-box{
      width:90%;
      min-height:430px;
      margin:0 auto;
      background:#FFFFFF;
      border:1px solid #E3E3E3;
      border-radius:6px;
      padding:34px 36px 30px;
      box-sizing:border-box;
    }

    .top-info-row{
      display:flex;
      flex-direction:row-reverse;
      justify-content:space-between;
      align-items:flex-start;
      gap:40px;
    }

    .main-info-box{
      width:68%;
      text-align:right;
    }

    .main-title{
      margin:0 0 18px 0;
      font-size:22px;
      font-weight:700;
      color:#3E2454;
      line-height:1.9;
    }

    .double-info-row{
      display:flex;
      flex-direction:row-reverse;
      justify-content:flex-end;
      gap:70px;
      margin-bottom:18px;
    }

    .info-item{
      display:flex;
      flex-direction:column;
      align-items:flex-end;
    }

    .info-label{
      font-size:16px;
      font-weight:500;
      color:#666666;
      margin-bottom:6px;
    }

    .info-value{
      font-size:17px;
      font-weight:700;
      color:#70A0AF;
    }

    .specialization-line{
      font-size:16px;
      line-height:2;
      text-align:right;
    }

    .specialization-label{
      color:#555555;
      font-weight:600;
    }

    .specialization-value{
      color:#70A0AF;
      font-weight:500;
    }

    .deadline-box{
      width:28%;
      text-align:right;
      direction:rtl;
      padding-top:50px;
      display:flex;
      align-items:center;
      justify-content:flex-start;
      gap:6px;
      flex-wrap:wrap;
    }

    .deadline-label{
      font-size:15px;
      font-weight:700;
      color:#3E2454;
      direction:rtl;
      white-space:nowrap;
    }

    .deadline-value{
      font-size:15px;
      font-weight:500;
      color:#70A0AF;
      direction:ltr;
      unicode-bidi:isolate;
      white-space:nowrap;
      display:inline-block;
    }

    .section-divider{
      width:100%;
      height:1px;
      background:#DCDCDC;
      margin:28px 0 22px;
    }

    .conditions-box{
      text-align:right;
    }

    .conditions-title{
      margin:0 0 14px 0;
      font-size:17px;
      font-weight:700;
      color:#3E2454;
    }

    .conditions-list{
      margin:0;
      padding-right:22px;
      list-style-type:disc;
    }

    .conditions-list li{
      font-size:16px;
      color:#444444;
      line-height:2;
      margin-bottom:2px;
    }

    .date-cell{
      direction:ltr;
      unicode-bidi:isolate;
      white-space:nowrap;
    }

    .requests-box{
      width:90%;
      margin:0 auto;
      background:#FFFFFF;
      border:1px solid #E3E3E3;
      border-radius:6px;
      padding:30px;
      box-sizing:border-box;
    }

    .requests-title{
      margin:0 0 18px 0;
      font-size:22px;
      font-weight:700;
      color:#3E2454;
      text-align:right;
    }

    .requests-table{
      width:100%;
      border-collapse:collapse;
    }

    .requests-table th,
    .requests-table td{
      border:1px solid #E4E4E4;
      padding:12px 10px;
      text-align:right;
      font-size:14px;
      vertical-align:middle;
    }

    .requests-table th{
      background:#F6F6F6;
      color:#3E2454;
      font-weight:700;
    }

    .empty-requests{
      text-align:center;
      font-size:18px;
      color:#777777;
      padding:30px 10px;
    }

    .actions-box{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      justify-content:center;
    }

    .action-btn{
      border:none;
      border-radius:6px;
      padding:8px 14px;
      font-size:13px;
      font-weight:600;
      cursor:pointer;
      font-family:"Noto Kufi Arabic",sans-serif;
    }

    .accept-btn{
      background:#63B27A;
      color:#FFFFFF;
    }

    .reject-btn{
      background:#B54747;
      color:#FFFFFF;
    }

    .view-btn{
      background:#3E2454;
      color:#FFFFFF;
      min-width:110px;
    }

    .status-text{
      font-weight:700;
      color:#3E2454;
    }

    /* النافذة المنبثقة */
    .modal-overlay{
      display:none;
      position:fixed;
      top:0;
      right:0;
      width:100%;
      height:100%;
      background:rgba(0,0,0,0.45);
      z-index:9999;
      align-items:center;
      justify-content:center;
      padding:20px;
      box-sizing:border-box;
    }

    .modal-overlay.show{
      display:flex;
    }

    .modal-box{
      width:100%;
      max-width:760px;
      max-height:90vh;
      overflow-y:auto;
      background:#FFFFFF;
      border-radius:12px;
      padding:26px;
      box-sizing:border-box;
      position:relative;
    }

    .modal-close{
      position:absolute;
      top:14px;
      left:14px;
      width:36px;
      height:36px;
      border:none;
      border-radius:50%;
      background:#F0F0F0;
      color:#3E2454;
      font-size:20px;
      cursor:pointer;
      font-family:"Noto Kufi Arabic",sans-serif;
    }

    .modal-title{
      margin:0 0 20px 0;
      font-size:22px;
      font-weight:700;
      color:#3E2454;
      text-align:right;
    }

    .details-grid{
      display:grid;
      grid-template-columns:repeat(2, 1fr);
      gap:14px;
      margin-bottom:20px;
    }

    .detail-card{
      background:#FAFAFA;
      border:1px solid #E7E7E7;
      border-radius:8px;
      padding:14px;
      box-sizing:border-box;
      text-align:right;
    }

    .detail-label{
      display:block;
      font-size:14px;
      color:#777777;
      margin-bottom:6px;
      font-weight:500;
    }

    .detail-value{
      display:block;
      font-size:16px;
      color:#3E2454;
      font-weight:700;
      line-height:1.8;
      word-break:break-word;
    }

    .modal-actions{
      display:flex;
      gap:10px;
      justify-content:flex-start;
      flex-wrap:wrap;
      margin-top:10px;
    }

    .modal-actions form{
      margin:0;
    }

    @media (max-width:700px){
      .details-grid{
        grid-template-columns:1fr;
      }

      .modal-box{
        padding:22px 16px;
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
          <li><a href="Inv04_CreateScholarship.php" class="active">عرض المنح</a></li>
          <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>  
          <li><a href="Inv10_Payments.php">المدفوعات</a></li>
        </ul>
      </div>

      <div class="sidebar-bottom">
        <form action="logout.php" method="post">
          <button type="submit" class="logout-btn">
            <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="تسجيل الخروج">
            <b>تسجيل الخروج</b>
          </button>
        </form>
      </div>
    </aside>

    <div class="main-content">

      <header class="header">
        <div class="page-heading">
          <h1 class="page-title">عرض المنح</h1>
          <p class="page-description">صفحة تقديم عروض فرص المنح</p>
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

      <section class="page">

        <div class="page-top">
          <div></div>
          <div class="back-btn">
            <a href="Inv04_CreateScholarship.php">
              <img src="سهم تراجع.svg" width="40">
            </a>
          </div>
        </div>

        <div class="tabs-row">
          <div class="tab tab-active" data-target="scholarship-tab">
            تفاصيل المنحة
          </div>

          <div class="tab" data-target="requests-tab">
            تفاصيل المتقدمين
          </div>
        </div>

        <div id="scholarship-tab" class="tab-content active">
          <div class="scholarship-details-box">
            <div class="top-info-row">              
              <div class="deadline-box">
                <span class="deadline-label">آخر موعد للتقديم:</span>
                <span class="deadline-value"><?php echo date("d-m-Y", strtotime($scholarship['app_deadline'])); ?></span>
              </div>

              <div class="main-info-box">
                <h2 class="main-title">
                  <?php echo htmlspecialchars($scholarship['sch_name']); ?>
                </h2>

                <div class="double-info-row">
                  <div class="info-item">
                    <span class="info-label">المجال الرئيسي:</span>
                    <span class="info-value">
                      <?php echo htmlspecialchars($scholarship['sch_field']); ?>
                    </span>
                  </div>

                  <div class="info-item">
                    <span class="info-label">الدرجة المستهدفة:</span>
                    <span class="info-value">
                      <?php echo htmlspecialchars($scholarship['study_level']); ?>
                    </span>
                  </div>
                </div>

                <div class="specialization-line">
                  <span class="specialization-label">التخصصات الدقيقة:</span>
                  <span class="specialization-value">
                    <?php echo htmlspecialchars($scholarship['Specializations']); ?>
                  </span>
                </div>
              </div>
            </div>

            <div class="section-divider"></div>

            <div class="conditions-box">
              <h3 class="conditions-title">الشروط:</h3>

              <?php
              $requirements_lines = preg_split("/\r\n|\n|\r/", $scholarship['requirements']);
              ?>

              <ul class="conditions-list">
                <?php foreach ($requirements_lines as $line): ?>
                  <?php if (trim($line) != ""): ?>
                    <li><?php echo htmlspecialchars(trim($line)); ?></li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>

        <div id="requests-tab" class="tab-content">
          <div class="requests-box">

            <h2 class="requests-title">تفاصيل المتقدمين على هذه المنحة</h2>

            <?php if (count($applicants) == 0): ?>
              <div class="empty-requests">
                لا يوجد متقدمون على هذه المنحة حتى الآن.
              </div>
            <?php else: ?>

              <table class="requests-table">
                <thead>
                  <tr>
                    <th>الاسم الأول</th>
                    <th>الاسم الأخير</th>
                    <th>الجامعة</th>
                    <th>التخصص</th>
                    <th>حالة الطلب</th>
                    <th>تاريخ التقديم</th>
                    <th>الإجراءات</th>
                  </tr>
                </thead>
                <tbody>

                  <?php foreach ($applicants as $applicant): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($applicant['f_name']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['l_name']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['univ_name']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['major_name']); ?></td>

                      <td class="status-text">
                        <?php echo htmlspecialchars($applicant['request_status']); ?>
                      </td>

                      <td class="date-cell">
                        <?php echo date("d-m-Y", strtotime($applicant['Submit_date'])); ?>
                      </td>

                      <td>
                        <div class="actions-box">
                          <button
                            type="button"
                            class="action-btn view-btn open-details-btn"
                            data-request-id="<?php echo $applicant['request_id']; ?>"
                            data-fname="<?php echo htmlspecialchars($applicant['f_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-lname="<?php echo htmlspecialchars($applicant['l_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-univ="<?php echo htmlspecialchars($applicant['univ_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-major="<?php echo htmlspecialchars($applicant['major_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-status="<?php echo htmlspecialchars($applicant['request_status'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-date="<?php echo date("d-m-Y", strtotime($applicant['Submit_date'])); ?>"
                          >
                            عرض الإجراءات
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>

                </tbody>
              </table>

            <?php endif; ?>

          </div>
        </div>

      </section>

    </div>
  </div>

  <!-- النافذة المنبثقة -->
  <div class="modal-overlay" id="detailsModal">
    <div class="modal-box">
      <button type="button" class="modal-close" id="closeModalBtn">×</button>

      <h2 class="modal-title">تفاصيل تقديم الطلب</h2>

      <div class="details-grid">
        <div class="detail-card">
          <span class="detail-label">رقم الطلب</span>
          <span class="detail-value" id="modalRequestId">-</span>
        </div>

        <div class="detail-card">
          <span class="detail-label">الاسم الكامل</span>
          <span class="detail-value" id="modalFullName">-</span>
        </div>

        <div class="detail-card">
          <span class="detail-label">الجامعة</span>
          <span class="detail-value" id="modalUniversity">-</span>
        </div>

        <div class="detail-card">
          <span class="detail-label">التخصص</span>
          <span class="detail-value" id="modalMajor">-</span>
        </div>

        <div class="detail-card">
          <span class="detail-label">حالة الطلب الحالية</span>
          <span class="detail-value" id="modalStatus">-</span>
        </div>

        <div class="detail-card">
          <span class="detail-label">تاريخ التقديم</span>
          <span class="detail-value" id="modalSubmitDate">-</span>
        </div>
      </div>

      <div class="modal-actions">
        <form method="post">
          <input type="hidden" name="request_id" id="acceptRequestId" value="">
          <input type="hidden" name="action_type" value="accept">
          <button type="submit" class="action-btn accept-btn">قبول الطلب</button>
        </form>

        <form method="post">
          <input type="hidden" name="request_id" id="rejectRequestId" value="">
          <input type="hidden" name="action_type" value="reject">
          <button type="submit" class="action-btn reject-btn">رفض الطلب</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    /* تشغيل التبويبات */
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(function(tab){
      tab.addEventListener("click", function(){
        tabs.forEach(function(item){
          item.classList.remove("tab-active");
        });

        contents.forEach(function(content){
          content.classList.remove("active");
        });

        tab.classList.add("tab-active");

        const targetId = tab.getAttribute("data-target");
        document.getElementById(targetId).classList.add("active");
      });
    });

    /* النافذة المنبثقة */
    const modal = document.getElementById("detailsModal");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const openButtons = document.querySelectorAll(".open-details-btn");

    const modalRequestId = document.getElementById("modalRequestId");
    const modalFullName = document.getElementById("modalFullName");
    const modalUniversity = document.getElementById("modalUniversity");
    const modalMajor = document.getElementById("modalMajor");
    const modalStatus = document.getElementById("modalStatus");
    const modalSubmitDate = document.getElementById("modalSubmitDate");

    const acceptRequestId = document.getElementById("acceptRequestId");
    const rejectRequestId = document.getElementById("rejectRequestId");

    openButtons.forEach(function(btn){
      btn.addEventListener("click", function(){
        const requestId = this.getAttribute("data-request-id");
        const fName = this.getAttribute("data-fname");
        const lName = this.getAttribute("data-lname");
        const univ = this.getAttribute("data-univ");
        const major = this.getAttribute("data-major");
        const status = this.getAttribute("data-status");
        const submitDate = this.getAttribute("data-date");

        modalRequestId.textContent = requestId;
        modalFullName.textContent = fName + " " + lName;
        modalUniversity.textContent = univ;
        modalMajor.textContent = major;
        modalStatus.textContent = status;
        modalSubmitDate.textContent = submitDate;

        acceptRequestId.value = requestId;
        rejectRequestId.value = requestId;

        modal.classList.add("show");
      });
    });

    closeModalBtn.addEventListener("click", function(){
      modal.classList.remove("show");
    });

    modal.addEventListener("click", function(e){
      if (e.target === modal) {
        modal.classList.remove("show");
      }
    });
  </script>

</body>
</html>