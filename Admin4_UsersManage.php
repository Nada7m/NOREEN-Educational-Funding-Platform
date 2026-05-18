<?php
session_start();

/* التحقق من دخول الأدمن */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost","root","","noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con,"utf8mb4");

/* تحديث حالة المستخدم */
if (isset($_POST['block']) || isset($_POST['activate'])) {

    $id = (int)$_POST['entity_id'];
    $type = $_POST['entity_type'];
    $status = isset($_POST['block']) ? 'محظور' : 'نشط';

    if ($type == 'مستفيد') {
        mysqli_query($con,"UPDATE beneficiary SET account_status='$status' WHERE bnf_id=$id");
    } elseif ($type == 'مستثمر') {
        mysqli_query($con,"UPDATE investor SET account_status='$status' WHERE inv_id=$id");
    } else {
        mysqli_query($con,"UPDATE consulting_office SET account_status='$status' WHERE office_id=$id");
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* جلب بيانات المستخدمين */
$result = mysqli_query($con,"
SELECT bnf_id AS entity_id, CONCAT(f_name,' ',l_name) AS entity_name, 'مستفيد' AS entity_type, account_status, '-' AS register_date FROM beneficiary
UNION ALL
SELECT inv_id, inv_name, 'مستثمر', account_status, '-' FROM investor
UNION ALL
SELECT office_id, office_name, 'مكتب استشاري', account_status, '-' FROM consulting_office
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إدارة المستخدمين</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS_AdminLayout.css?v=3">

<style>

/* الحاوية العامة */
.page-wrapper{ padding:40px; }

/* صندوق الجدول */
.table-box{ width:100%; max-width:1050px; margin:0 auto; background:#FFFFFF; border:1px solid #E6E0E6; border-radius:8px; overflow:hidden; }

/* الجدول */
table{ width:100%; background:#FFFFFF; }

/* صف العناوين */
.table-head th{ padding:15px; background:#FAFAFA; border-bottom:1px solid #DDDDDD; font-size:15px; font-weight:700; color:#3E2454; text-align:center; }

/* خلايا الجدول */
table td{ padding:16px; border-bottom:1px solid #EEEEEE; text-align:center; font-size:14px; color:#444444; vertical-align:middle; }

/* شكل الحالة */
.status{ display:flex; align-items:center; justify-content:center; width:100px; height:42px; margin:0 auto; border-radius:12px; color:#FFFFFF; font-size:14px; font-weight:600; }

/* حالة نشط */
.status-active{ background:#2E8B57; }

/* حالة محظور */
.status-blocked{ background:#C23B3B; }

/* حاوية الأزرار */
.actions{ display:flex; justify-content:center; align-items:center; gap:10px; }

/* الفورم داخل الإجراء */
.actions form{ margin:0; }

/* زر عام */
.btn{ width:100px; height:42px; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; }

/* زر الحظر */
.block{ background:#A53A3A; color:#FFFFFF; border:none; }

/* زر التنشيط */
.activate{ background:#FFFFFF; color:#333333; border:1px solid #CCCCCC; }

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
        <li><a href="Admin3_Contracts.php">إدارة العقود</a></li>
        <li><a href="Admin4_UsersManage.php" class="active">إدارة المستخدمين</a></li>
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
        <div class="page-title">إدارة المستخدمين</div>
        <div class="page-description">عرض حسابات النظام وإدارة حالة المستخدمين</div>
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
              <th>اسم المستخدم</th>
              <th>نوع الحساب</th>
              <th>الحالة</th>
              <th>الإجراءات</th>
            </tr>

            <?php
            /* عرض المستخدمين داخل الجدول */
            while($row = mysqli_fetch_assoc($result)){

              /* قراءة حالة الحساب */
              $status = $row['account_status'];

              /* تحديد لون ونص الحالة */
              if($status == "محظور"){
                $class = "status-blocked";
                $text = "محظور";
              } else {
                $class = "status-active";
                $text = "نشط";
              }
            ?>
            <tr>
              <td><?= $row['entity_name'] ?></td>
              <td><?= $row['entity_type'] ?></td>

              <td>
                <div class="status <?= $class ?>"><?= $text ?></div>
              </td>

              <td>
                <div class="actions">
                  <?php
                  /* إذا كان الحساب نشطًا يظهر زر الحظر */
                  if($text == "نشط"){
                  ?>
                  <form method="post">
                    <input type="hidden" name="entity_id" value="<?= $row['entity_id'] ?>">
                    <input type="hidden" name="entity_type" value="<?= $row['entity_type'] ?>">
                    <button type="submit" name="block" class="btn block">حظر</button>
                  </form>
                  <?php
                  /* إذا كان الحساب محظورًا يظهر زر التنشيط */
                  } else {
                  ?>
                  <form method="post">
                    <input type="hidden" name="entity_id" value="<?= $row['entity_id'] ?>">
                    <input type="hidden" name="entity_type" value="<?= $row['entity_type'] ?>">
                    <button type="submit" name="activate" class="btn activate">تنشيط</button>
                  </form>
                  <?php } ?>
                </div>
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