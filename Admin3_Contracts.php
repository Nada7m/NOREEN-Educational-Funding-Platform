<?php
session_start();

/* التحقق من دخول الأدمن قبل فتح الصفحة */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");

/* تحديث العقود المنتهية بعد دفع كل الدفعات */
$sqlEndContracts = "
    UPDATE e_contract c
    INNER JOIN scholarship_requests sr
        ON c.request_id = sr.request_id
    SET c.ctr_status = 'منتهي', sr.request_status = 'منتهي'
    WHERE c.ctr_status = 'نشط'
    AND sr.request_status = 'مقبول'
    AND (
        SELECT COUNT(*)
        FROM payments p
        WHERE p.contract_id = c.contract_id
    ) > 0
    AND (
        SELECT COUNT(*)
        FROM payments p
        WHERE p.contract_id = c.contract_id
        AND p.payment_status = 'تم الدفع'
    ) = (
        SELECT COUNT(*)
        FROM payments p
        WHERE p.contract_id = c.contract_id
    )
";

mysqli_query($con, $sqlEndContracts);

/* تنفيذ إلغاء العقد */
if (isset($_POST['end_contract'])) {

    /* تحويل رقم العقد ورقم الطلب */
    $contract_id = (int) $_POST['contract_id'];
    $request_id = (int) $_POST['request_id'];

    /** التأكد من صحة الأرقام **/
    if ($contract_id > 0 && $request_id > 0) {

        /* تحديث حالة العقد إلى ملغي إذا كان نشطًا فقط */
        $stmt_contract = mysqli_prepare($con, "
            UPDATE e_contract
            SET ctr_status = 'ملغي'
            WHERE contract_id = ?
            AND ctr_status = 'نشط'
        ");

        if ($stmt_contract) {
            mysqli_stmt_bind_param($stmt_contract, "i", $contract_id);
            mysqli_stmt_execute($stmt_contract);

            /* تحديث الطلب فقط إذا تم إلغاء العقد فعليًا */
            if (mysqli_stmt_affected_rows($stmt_contract) > 0) {

                /* تحديث حالة طلب المنحة إلى ملغى */
                $stmt_request = mysqli_prepare($con, "
                    UPDATE scholarship_requests
                    SET request_status = 'ملغى'
                    WHERE request_id = ?
                ");

                if ($stmt_request) {
                    mysqli_stmt_bind_param($stmt_request, "i", $request_id);
                    mysqli_stmt_execute($stmt_request);
                    mysqli_stmt_close($stmt_request);
                }
            }

            mysqli_stmt_close($stmt_contract);
        }
    }

    /* إعادة تحميل الصفحة */
    header("Location: Admin3_Contracts.php");
    exit();
}

/* جلب العقود */
$sql = "
SELECT 
    c.contract_id,
    c.ctr_status,
    c.request_id,
    i.inv_name,
    CONCAT(b.f_name, ' ', b.l_name) AS beneficiary_name
FROM e_contract c
JOIN scholarship_requests r ON c.request_id = r.request_id
JOIN beneficiary b ON r.bnf_id = b.bnf_id
JOIN scholarship_opps s ON r.scholarship_id = s.scholarship_id
JOIN investor i ON s.inv_id = i.inv_id
ORDER BY c.contract_id DESC
";

/* تنفيذ استعلام العقود */
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إدارة العقود</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS_AdminLayout.css?v=3">

<style>
/* الحاوية العامة */
.page-wrapper{ padding:40px; }

/* صندوق الجدول */
.table-box{ width:100%; max-width:1050px; margin:0 auto; background:#FFFFFF; border:1px solid #E6E0E6; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }

/* الجدول */
table{ width:100%; border-collapse:collapse; table-layout:fixed; background:#FFFFFF; }

/* رأس الجدول */
thead th{ padding:15px 12px; background:#FAFAFA; border-bottom:1px solid #DDDDDD; font-size:15px; font-weight:700; color:#3E2454; text-align:center; }

/* خلايا الجدول */
tbody td{ padding:16px 12px; border-bottom:1px solid #EEEEEE; text-align:center; vertical-align:middle; font-size:14px; font-weight:500; color:#595959; background:#FFFFFF; }

/* مربع الحالة */
.status{ display:inline-flex; align-items:center; justify-content:center; width:100px; height:42px; border-radius:12px; color:#FFFFFF; font-size:14px; font-weight:600; }

/* حالة نشط */
.status-active{ background:#2E8B57; }

/* حالة ملغي */
.status-cancel{ background:#C4474F; }

/* حالة منتهي */
.status-ended{ background:#9E9E9E; }

/* الأزرار */
.btn{ width:100px; height:42px; border:none; border-radius:12px; cursor:pointer; font-size:14px; font-family:"Noto Kufi Arabic", sans-serif; font-weight:600; }

/* زر الإلغاء */
.btn-delete{ background:#A53A3A; color:#FFFFFF; }

/* صف فارغ */
.empty-row td{ height:62px; background:#FFFFFF; border-bottom:1px solid #EEEEEE; }

/* خلفية التأكيد */
.confirm-modal{ display:none; position:fixed; inset:0; z-index:9999; justify-content:center; align-items:center; }

/* صندوق التأكيد */
.confirm-box{ width:420px; max-width:92%; background:#FFFFFF; border-radius:10px; padding:28px 24px; text-align:center; box-shadow:0 8px 25px rgba(0,0,0,0.15); }

/* نص التأكيد */
.confirm-title{ color:#3E2454; font-size:18px; font-weight:700; margin-bottom:25px; }

/* أزرار التأكيد */
.confirm-actions{ display:flex; justify-content:center; gap:14px; }

/* زر التأكيد */
.confirm-btn{ min-width:110px; height:42px; border:none; border-radius:10px; cursor:pointer; font-size:14px; font-family:"Noto Kufi Arabic", sans-serif; font-weight:700; }

/* زر نعم */
.confirm-yes{ background:#A53A3A; color:#FFFFFF; }

/* زر لا */
.confirm-no{ background:#ECECEC; color:#3E2454; }
</style>
</head>

<body>

<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-logo">
        <img src="شعار نورين بنفسجي.svg" alt="شعار نورين">
      </div>

      <ul class="sidebar-menu">
        <li><a href="Admin2_EntitiesApproval.php">اعتماد الجهات</a></li>
        <li><a href="Admin3_Contracts.php" class="active">إدارة العقود</a></li>
        <li><a href="Admin4_UsersManage.php">إدارة المستخدمين</a></li>
        <li><a href="Admin5_Complaints.php">الشكاوى والاستفسارات</a></li>
      </ul>
    </div>

    <div class="sidebar-bottom">
      <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">
          <img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="خروج">
          تسجيل الخروج
        </button>
      </form>
    </div>
  </aside>

  <div class="main-content">

    <header class="header">
      <div class="page-heading">
        <div class="page-title">إدارة العقود</div>
        <div class="page-description">عرض العقود الحالية ومتابعة حالتها وإجراءاتها</div>
      </div>

      <div class="header-left">
        <a href="Admin1_profile.php" class="profile-btn">لوحة التحكم</a>
      </div>
    </header>

    <div class="page">
      <div class="page-wrapper">

        <div class="table-box">
          <table>
            <thead>
              <tr>
                <th>رقم الطلب</th>
                <th>رقم العقد</th>
                <th>اسم المستثمر</th>
                <th>اسم المستفيد</th>
                <th>حالة العقد</th>
                <th>الإجراءات</th>
              </tr>
            </thead>

            <tbody>

            <?php
            /* عرض العقود */
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {

                    /* حالة العقد */
                    $status = $row['ctr_status'];
                    $class = "status-active";

                    if ($status == "ملغي") {
                        $class = "status-cancel";
                    } elseif ($status == "منتهي") {
                        $class = "status-ended";
                    }
            ?>

              <tr>
                <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                <td><?php echo htmlspecialchars($row['contract_id']); ?></td>
                <td><?php echo htmlspecialchars($row['inv_name']); ?></td>
                <td><?php echo htmlspecialchars($row['beneficiary_name']); ?></td>

                <td>
                  <div class="status <?php echo $class; ?>">
                    <?php echo htmlspecialchars($status); ?>
                  </div>
                </td>

                <td>
                  <?php
                  /* الإلغاء يظهر للعقد النشط فقط */
                  if ($status == "نشط") {
                  ?>
                  <form method="POST" class="cancel-form">
                    <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars($row['contract_id']); ?>">
                    <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($row['request_id']); ?>">
                    <button type="button" class="btn btn-delete open-confirm">إلغاء العقد</button>
                  </form>
                  <?php } ?>
                </td>
              </tr>

            <?php
                }
            } else {
            ?>

              <tr>
                <td colspan="6">لا توجد عقود حاليًا</td>
              </tr>

            <?php } ?>

              <tr class="empty-row"><td colspan="6"></td></tr>
              <tr class="empty-row"><td colspan="6"></td></tr>
              <tr class="empty-row"><td colspan="6"></td></tr>

            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</div>

<div class="confirm-modal" id="confirmModal">
  <div class="confirm-box">
    <div class="confirm-title">هل أنت متأكد من إلغاء العقد؟</div>

    <div class="confirm-actions">
      <button type="button" class="confirm-btn confirm-yes" id="confirmYes">نعم</button>
      <button type="button" class="confirm-btn confirm-no" onclick="closeConfirm()">لا</button>
    </div>
  </div>
</div>

<script>
let selectedForm = null;

/* فتح نافذة التأكيد */
document.querySelectorAll(".open-confirm").forEach(function(btn){
    btn.addEventListener("click", function(){
        selectedForm = this.closest("form");
        document.getElementById("confirmModal").style.display = "flex";
    });
});

/* تنفيذ الإلغاء */
document.getElementById("confirmYes").addEventListener("click", function(){
    if(selectedForm){
        const hiddenBtn = document.createElement("button");
        hiddenBtn.type = "submit";
        hiddenBtn.name = "end_contract";
        hiddenBtn.style.display = "none";
        selectedForm.appendChild(hiddenBtn);
        hiddenBtn.click();
    }
});

/* إغلاق النافذة */
function closeConfirm(){
    document.getElementById("confirmModal").style.display = "none";
}

/* إغلاق عند الضغط خارج النافذة */
window.onclick = function(e){
    if(e.target.id === "confirmModal"){
        closeConfirm();
    }
}
</script>

</body>
</html>