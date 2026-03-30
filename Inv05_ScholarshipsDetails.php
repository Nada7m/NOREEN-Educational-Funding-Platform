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

/* ===================================================
   هذا الجزء خاص بتحديث حالة الطلب
   إذا ضغط المستثمر قبول أو رفض
   =================================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action_type"]) && isset($_POST["request_id"])) {

    /* رقم الطلب */
    $request_id = (int)$_POST["request_id"];

    /* نوع الإجراء */
    $action_type = $_POST["action_type"];

    /* نحدد الحالة الجديدة */
    if ($action_type == "accept") {
        $new_status = "مقبول";
    } elseif ($action_type == "reject") {
        $new_status = "مرفوض";
    } else {
        $new_status = "";
    }

    /* نحدث الحالة فقط إذا كانت القيمة صحيحة */
    if ($new_status != "") {

        /* نربط التحديث بنفس المنحة حتى لا يتعدل طلب مختلف */
        $update_stmt = mysqli_prepare($con, "UPDATE scholarship_requests 
                                             SET request_status = ?
                                             WHERE request_id = ? AND scholarship_id = ?");

        mysqli_stmt_bind_param($update_stmt, "sii", $new_status, $request_id, $scholarship_id);
        mysqli_stmt_execute($update_stmt);
    }
}

/* ===================================================
   جلب بيانات المنحة
   =================================================== */
$stmt = mysqli_prepare($con, "SELECT scholarship_id, sch_name, sch_field, study_level, Specializations, requirements, app_deadline
                              FROM scholarship_opps
                              WHERE scholarship_id = ? AND inv_id = ?");

mysqli_stmt_bind_param($stmt, "ii", $scholarship_id, $inv_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$scholarship = mysqli_fetch_assoc($result);

/* إذا لم نجد المنحة نوقف الصفحة */
if (!$scholarship) {
    die("لم يتم العثور على بيانات هذه المنحة.");
}

/* ===================================================
   جلب الطلبات الخاصة بهذه المنحة
   =================================================== */
$applicants = [];

$app_stmt = mysqli_prepare($con, "SELECT request_id, univ_name, major_name, request_status, Submit_date
                                  FROM scholarship_requests
                                  WHERE scholarship_id = ?
                                  ORDER BY request_id DESC");

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
  <link rel="stylesheet" href="CSS01Layout.css?v=2">

  <style>
    /* ترتيب الهيدر:
       العنوان يكون يمين
       والإعدادات تكون يسار */
    .header{
      display:flex;
      flex-direction:row-reverse;
      justify-content:space-between;
      align-items:center;
    }

    /* عنوان الصفحة */
    .page-heading{
      text-align:right;
      align-items:flex-end;
    }

    /* أيقونة الإعدادات */
    .header-icons{
      display:flex;
      align-items:center;
    }

    /* تصغير زر الرجوع */
    .back-btn{
      width:34px;
      height:34px;
      display:flex;
      align-items:center;
      justify-content:center;
    }

    /* تصغير السهم نفسه */
    .back-icon{
      width:24px;
      height:24px;
      object-fit:contain;
    }

    /* التبويبات */
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

    /* نخفي كل محتويات التبويبات */
    .tab-content{
      display:none;
    }

    /* نظهر التبويب المفتوح فقط */
    .tab-content.active{
      display:block;
    }

    /* هذا التنسيق خاص بالتاريخ حتى يظهر بالترتيب الصحيح */
    .deadline-value{
      direction:ltr;
      unicode-bidi:embed;
      display:inline;
    }

    /* هذا أيضًا لتاريخ الطلبات */
    .date-cell{
      direction:ltr;
      unicode-bidi:embed;
      white-space:nowrap;
    }

    /* صندوق الطلبات */
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

    /* أزرار الإجراءات */
    .actions-box{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
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

    /* شكل الحالة */
    .status-text{
      font-weight:700;
      color:#3E2454;
    }
  </style>
</head>

<body>
  <div class="layout">

    <!-- الشريط الجانبي -->
    <aside class="sidebar">
      <div class="sidebar-top">

        <!-- الشعار -->
        <div class="sidebar-logo">
          <img src="شعار نورين.png" alt="نورين">
        </div>

        <!-- روابط القائمة -->
        <ul class="sidebar-menu">
          <li><a href="Inv00_MainPage.php">الرئيسية</a></li>
          <li><a href="Inv04_CreateScholarship.php" class="active">عرض المنح</a></li>
          <li><a href="#">إدارة المنح</a></li>
          <li><a href="#">المدفوعات</a></li>
        </ul>

      </div>

      <!-- زر تسجيل الخروج -->
      <div class="sidebar-bottom">
        <form action="logout.php" method="post">
          <button type="submit" class="logout-btn">
            <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="تسجيل الخروج">
            <b>تسجيل الخروج</b>
          </button>
        </form>
      </div>
    </aside>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">

      <!-- الهيدر -->
      <header class="header">

        <!-- عنوان الصفحة -->
        <div class="page-heading">
          <h1 class="page-title">عرض المنح</h1>
          <p class="page-description">صفحة تقديم عروض فرص المنح</p>
        </div>

        <!-- الإعدادات -->
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

        <!-- زر الرجوع -->
        <div class="page-top">
          <div></div>

          <div class="back-btn">
            <a href="Inv04_CreateScholarship.php">
              <img src="سهم تراجع.svg" alt="رجوع" class="back-icon">
            </a>
          </div>
        </div>

        <!-- التبويبات -->
        <div class="tabs-row">

          <!-- تبويب المنحة -->
          <div class="tab tab-active" data-target="scholarship-tab">
            تفاصيل المنحة
          </div>

          <!-- تبويب المتقدمين -->
          <div class="tab" data-target="requests-tab">
            تفاصيل المتقدمين
          </div>

        </div>

        <!-- =========================
             تبويب تفاصيل المنحة
             ========================= -->
        <div id="scholarship-tab" class="tab-content active">

          <div class="scholarship-details-box">

            <div class="top-info-row">

              <!-- بيانات الموعد -->
              <div class="deadline-box">
                <span class="deadline-icon">🗓</span>
                <span class="deadline-label">آخر موعد للتقديم:</span>
                <div class="deadline-value">
                  <?php echo date("d-m-Y", strtotime($scholarship['app_deadline'])); ?>
                </div>
              </div>

              <!-- بيانات المنحة -->
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

            <!-- الشروط -->
            <div class="conditions-box">
              <h3 class="conditions-title">الشروط:</h3>

              <?php
              /* تحويل كل سطر في الشروط إلى نقطة مستقلة */
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

        <!-- =========================
             تبويب تفاصيل المتقدمين
             ========================= -->
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

                      <!-- اسم الجامعة -->
                      <td><?php echo htmlspecialchars($applicant['univ_name']); ?></td>

                      <!-- التخصص -->
                      <td><?php echo htmlspecialchars($applicant['major_name']); ?></td>

                      <!-- الحالة الحالية -->
                      <td class="status-text">
                        <?php echo htmlspecialchars($applicant['request_status']); ?>
                      </td>

                      <!-- تاريخ التقديم -->
                      <td class="date-cell">
                        <?php echo date("d-m-Y", strtotime($applicant['Submit_date'])); ?>
                      </td>

                      <!-- أزرار القبول والرفض -->
                      <td>
                        <div class="actions-box">

                          <!-- زر قبول -->
                          <form method="post" style="margin:0;">
                            <input type="hidden" name="request_id" value="<?php echo $applicant['request_id']; ?>">
                            <input type="hidden" name="action_type" value="accept">
                            <button type="submit" class="action-btn accept-btn">قبول</button>
                          </form>

                          <!-- زر رفض -->
                          <form method="post" style="margin:0;">
                            <input type="hidden" name="request_id" value="<?php echo $applicant['request_id']; ?>">
                            <input type="hidden" name="action_type" value="reject">
                            <button type="submit" class="action-btn reject-btn">رفض</button>
                          </form>

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

  <script>
    /* هذا الجزء يشغل التبويبات
       عندما يضغط المستخدم على اسم التبويب
       نخفي الباقي ونظهر المطلوب فقط */

    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(function(tab){
      tab.addEventListener("click", function(){

        /* إزالة التحديد من كل التبويبات */
        tabs.forEach(function(item){
          item.classList.remove("tab-active");
        });

        /* إخفاء كل المحتويات */
        contents.forEach(function(content){
          content.classList.remove("active");
        });

        /* تحديد التبويب الحالي */
        tab.classList.add("tab-active");

        /* إظهار المحتوى المرتبط به */
        const targetId = tab.getAttribute("data-target");
        document.getElementById(targetId).classList.add("active");
      });
    });
  </script>

</body>
</html>