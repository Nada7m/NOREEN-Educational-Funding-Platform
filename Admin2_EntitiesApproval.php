<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال */
$con = mysqli_connect("localhost","root","","noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con,"utf8mb4");

/* تنفيذ الاعتماد أو الرفض */
if(isset($_POST['approve']) || isset($_POST['reject'])){
    $id = (int)$_POST['entity_id'];
    $type = $_POST['entity_type'];
    $status = isset($_POST['approve']) ? 'معتمد' : 'مرفوض';
    if($type == "مستثمر"){
    // تحديث حالة المستثمر
        mysqli_query($con,"UPDATE investor SET approval_status='$status' WHERE inv_id=$id");
    } else {
   // تحديث حالة المكتب الاستشاري
        mysqli_query($con,"UPDATE consulting_office SET approval_status='$status' WHERE office_id=$id");
    }
    // تحديث الصفحة بعد التنفيذ
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
/* اجيب بيانات المستثمرين والمكاتب */
$result = mysqli_query($con,"
SELECT inv_id AS entity_id, inv_name AS entity_name, ccr_number, approval_status, 'مستثمر' AS entity_type FROM investor
UNION ALL
SELECT office_id, office_name, ccr_number, approval_status, 'مكتب استشاري' FROM consulting_office
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>اعتماد الجهات</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS_AdminLayout.css?v=3">

<style>

/* الحاوية العامة */
.page-wrapper{ padding:40px; }

/* صندوق الجدول */
.table-box{ width:100%; max-width:1050px; margin:0 auto; background:#FFFFFF; border:1px solid #E6E0E6; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }

/* الجدول */
table{ width:100%; border-collapse:collapse; }

/* صف العناوين */
.table-head th{ padding:15px; background:#FAFAFA; border-bottom:1px solid #DDDDDD; color:#3E2454; font-size:15px; font-weight:700; text-align:center; }

/* خلايا الجدول */
table td{ padding:16px; border-bottom:1px solid #EEEEEE; text-align:center; color:#444444; font-size:14px; }

/* شكل الحالة */
.status{ display:flex; align-items:center; justify-content:center; width:100px; height:42px; margin:0 auto; border-radius:12px; color:#FFFFFF; font-size:14px; font-weight:600; }

/* بانتظار */
.pending{ background:#D8B35E; }

/* معتمد */
.approved{ background:#2E8B57; }

/* مرفوض */
.rejected{ background:#C23B3B; }

/* الأزرار */
.actions{ display:flex; justify-content:center; align-items:center; gap:10px; }

/* الفورم */
.actions form{ margin:0; }

/* زر عام */
.btn{ width:100px; height:42px; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; }

/* زر اعتماد */
.accept{ background:#FFFFFF; border:1px solid #CCCCCC; color:#333333; }

/* زر رفض */
.reject{ background:#A53A3A; color:#FFFFFF; border:none; }

</style>
</head>

<body>

<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-logo">
        <img src="شعار نورين بنفسجي.svg" alt="نورين">
      </div>

      <ul class="sidebar-menu">
        <li><a href="Admin2_EntitiesApproval.php" class="active">اعتماد الجهات</a></li>
        <li><a href="Admin3_Contracts.php">إدارة العقود</a></li>
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
        <div class="page-title">اعتماد الجهات</div>
        <div class="page-description">مراجعة المستثمرين والمكاتب الاستشارية واعتمادها أو رفضها</div>
      </div>

      <div class="header-left">
        <a href="Admin1_profile.php" class="profile-btn">لوحة التحكم</a>
      </div>
    </header>

    <div class="page">
      <div class="page-wrapper">

        <div class="table-box">
          <table>

            <tr class="table-head">
              <th>اسم الجهة</th>
              <th>النوع</th>
              <th>السجل</th>
              <th>الحالة</th>
              <th>الإجراءات</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result)) {
              $status = $row['approval_status'];

              if($status == "معتمد"){
                $class = "approved";
              } elseif($status == "مرفوض"){
                $class = "rejected";
              } else {
                $status = "بانتظار";
                $class = "pending";
              }
            ?>
            <tr>
              <td><?= $row['entity_name'] ?></td>
              <td><?= $row['entity_type'] ?></td>
              <td><?= $row['ccr_number'] ?></td>
              <td>
                <span class="status <?= $class ?>"><?= $status ?></span>
              </td>
              <td>
                <?php if($status == "بانتظار"){ ?>
                <div class="actions">
                  //فورم الاعتماد//
                  <form method="post">
                    <input type="hidden" name="entity_id" value="<?= $row['entity_id'] ?>">
                    <input type="hidden" name="entity_type" value="<?= $row['entity_type'] ?>">
                    <button type="submit" name="approve" class="btn accept">اعتماد</button>
                  </form>
                  //فورم الرفض//
                  <form method="post">
                    <input type="hidden" name="entity_id" value="<?= $row['entity_id'] ?>">
                    <input type="hidden" name="entity_type" value="<?= $row['entity_type'] ?>">
                    <button type="submit" name="reject" class="btn reject">رفض</button>
                  </form>

                </div>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>

          </table>
        </div>

      </div>
    </div>

  </div>

</div>

</body>
</html>