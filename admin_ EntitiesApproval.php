<?php
session_start();

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");


/* =========================================
   تحديث حالة الاعتماد عند الضغط على الأزرار
========================================= */
if (isset($_POST['approve']) || isset($_POST['reject'])) {

    $entity_id   = (int) $_POST['entity_id'];
    $entity_type = $_POST['entity_type'];

    if (isset($_POST['approve'])) {
        $new_status = 'معتمد';
    } else {
        $new_status = 'مرفوض';
    }

    if ($entity_type == 'مستثمر') {
        $update = "UPDATE investor SET approval_status='$new_status' WHERE inv_id=$entity_id";
    } else {
        $update = "UPDATE consulting_office SET approval_status='$new_status' WHERE office_id=$entity_id";
    }

    mysqli_query($con, $update);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


/* =========================================
   جلب المستثمرين + المكاتب الاستشارية مع بعض
========================================= */
$sql = "
SELECT 
    inv_id AS entity_id,
    inv_name AS entity_name,
    ccr_number,
    approval_status,
    'مستثمر' AS entity_type
FROM investor

UNION ALL

SELECT 
    office_id AS entity_id,
    office_name AS entity_name,
    ccr_number,
    approval_status,
    'مكتب استشاري' AS entity_type
FROM consulting_office
";

$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>اعتماد الجهات</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css">

  <style>
    *{
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body{
      font-family: "Noto Kufi Arabic", sans-serif;
      background: #ffffff;
      direction: rtl;
    }

    .page-content{
      margin-right: 250px;
      padding: 45px 35px 30px 35px;
      min-height: 100vh;
      background-color: #fff;
    }

    .top-bar{
      width: 100%;
      display: flex;
      justify-content: flex-end;
      margin-bottom: 18px;
    }

    

    .page-title{
      color: #3E2454;
      font-size: 42px;
      font-weight: 500;
      text-align: right;
      margin-bottom: 30px;
      margin-right: 15px;
      
    }

    .table-wrapper{
      width: 100%;
      display: flex;
      justify-content: center;
    }

    .table-box{
      width: 980px;
      min-height: 610px;
      border: 1px solid #EEEEEE;
      background-color: #fff;
      overflow: hidden;
    }

    table{
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    
    tbody td{
      min-height: 92px;
      text-align: center;
      vertical-align: middle;
      border-bottom: 1px solid #F2F2F2;
      border-left: 1px solid #F7F7F7;
      padding: 18px 12px;
      font-size: 17px;
      font-weight: 400;
      color: #595959;
      background-color: #fff;
    }

    
    .company-name,
    .data-text{
      color: #595959;
      font-size: 17px;
      font-weight: 400;
    }

    .actions{
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .btn{
      width: 95px;
      height: 45px;
      border-radius: 12px;
      font-family: "Noto Kufi Arabic", sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.2s;
    }

    .btn-accept{
      background-color: #ffffff;
      color: #3E2454;
      border: 1px solid #D9D9D9;
    }

    .btn-reject{
      background-color: #A53A3A;
      color: #ffffff;
      border: none;
      box-shadow: inset 0 -2px 0 rgba(0,0,0,0.08);
    }

    .status-badge{
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 150px;
      height: 46px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      color: #fff;
      padding: 0 16px;
      white-space: nowrap;
    }

    .st-pending{
      background-color: #E7C06E;
    }

    .st-approved{
      background-color: #4CAF50;
    }

    .st-rejected{
      background-color: #D64545;
    }

    .col-name{ width: 24%; }
    .col-type{ width: 18%; }
    .col-cr{ width: 20%; }
    .col-status{ width: 18%; }
    .col-actions{ width: 20%; }

    .logout-btn{
      background: transparent;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      font-family: "Noto Kufi Arabic", sans-serif;
    }

    .logout-icon{
      width: 18px;
      height: 18px;
    }
  </style>
</head>
<body>

  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-logo">
        <img src="شعار نورين.png" alt="شعار نورين">
      </div>

      <ul class="sidebar-menu">
        <li><a href="contracts_manage.php">إدارة العقود</a></li>
        <li><a href="complaints.php">الشكاوى والاستفسارات</a></li>
        <li><a href="users_manage.php">إدارة المستخدمين</a></li>
        <li><a href="admin_ EntitiesApproval.php"class="active">اعتماد الجهات</a></li>
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

  <div class="page-content">

    <div class="top-bar">
      <a href="admin_profile.php" class="profile-btn">بيانات الحساب</a>
    </div>

    <h1 class="page-title">اعتماد الجهات</h1>

    <div class="table-wrapper">
      <div class="table-box">
        <table>
          <thead>
            <tr>
              <th class="col-name">اسم الجهة</th>
              <th class="col-type">نوع الجهة</th>
              <th class="col-cr two-lines">رقم السجل<br>التجاري</th>
              <th class="col-status">حالة الاعتماد</th>
              <th class="col-actions">الإجراءات</th>
            </tr>
          </thead>

          <tbody>
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
              while($row = mysqli_fetch_assoc($result)) {

                $status = trim((string)$row['approval_status']);

                /* إذا كانت فاضية أو NULL نعتبرها بانتظار المراجعة */
                if ($status === "" || $status === null || $status === "بانتظار" || $status === "قيد المراجعة") {
                    $statusText  = "بانتظار المراجعة";
                    $statusClass = "st-pending";
                }
                elseif ($status === "معتمد") {
                    $statusText  = "معتمد";
                    $statusClass = "st-approved";
                }
                elseif ($status === "مرفوض") {
                    $statusText  = "مرفوض";
                    $statusClass = "st-rejected";
                }
                else {
                    $statusText  = "بانتظار المراجعة";
                    $statusClass = "st-pending";
                }
            ?>
            <tr>
              <td class="company-name"><?php echo htmlspecialchars($row['entity_name']); ?></td>
              <td class="data-text"><?php echo htmlspecialchars($row['entity_type']); ?></td>
              <td class="data-text"><?php echo htmlspecialchars($row['ccr_number']); ?></td>
              <td>
                <span class="status-badge <?php echo $statusClass; ?>">
                  <?php echo $statusText; ?>
                </span>
              </td>
              <td>
                <div class="actions">
                  <form method="post" style="display:inline-block;">
                    <input type="hidden" name="entity_id" value="<?php echo $row['entity_id']; ?>">
                    <input type="hidden" name="entity_type" value="<?php echo $row['entity_type']; ?>">
                    <button type="submit" name="approve" class="btn btn-accept">اعتماد</button>
                  </form>

                  <form method="post" style="display:inline-block;">
                    <input type="hidden" name="entity_id" value="<?php echo $row['entity_id']; ?>">
                    <input type="hidden" name="entity_type" value="<?php echo $row['entity_type']; ?>">
                    <button type="submit" name="reject" class="btn btn-reject">رفض</button>
                  </form>
                </div>
              </td>
            </tr>


            <?php
              }
            } else {
            ?>
            <tr>
              <td colspan="5" class="data-text">لا توجد جهات لعرضها</td>
            </tr>
            <?php } ?>
          </tbody>

        </table>
      </div>
    </div>
  </div>

</body>
</html>