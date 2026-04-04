<?php
session_start();

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");


/* =========================================
   تحديث حالة الحساب عند الضغط على الأزرار
========================================= */
if (isset($_POST['block']) || isset($_POST['activate'])) {

    $entity_id   = (int) $_POST['entity_id'];
    $entity_type = $_POST['entity_type'];

    if (isset($_POST['block'])) {
        $new_status = 'محظور';
    } else {
        $new_status = 'نشط';
    }

    if ($entity_type == 'مستفيد') {
        $update = "UPDATE beneficiary SET account_status='$new_status' WHERE bnf_id=$entity_id";
    } elseif ($entity_type == 'مستثمر') {
        $update = "UPDATE investor SET account_status='$new_status' WHERE inv_id=$entity_id";
    } else {
        $update = "UPDATE consulting_office SET account_status='$new_status' WHERE office_id=$entity_id";
    }

    mysqli_query($con, $update);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


/* =========================================
   جلب المستخدمين من الجداول الثلاثة مع بعض
   ملاحظة:
   لو اسم عمود الاسم عند المستفيد مختلف عندك،
   غيري bnf_name إلى الاسم الصحيح.
========================================= */
$sql = "
SELECT 
    bnf_id AS entity_id,
    CONCAT(f_name, ' ', l_name) AS entity_name,
    'مستفيد' AS entity_type,
    account_status,
    '-' AS register_date
FROM beneficiary

UNION ALL

SELECT 
    inv_id AS entity_id,
    inv_name AS entity_name,
    'مستثمر' AS entity_type,
    account_status,
    '-' AS register_date
FROM investor

UNION ALL

SELECT 
    office_id AS entity_id,
    office_name AS entity_name,
    'مكتب استشاري' AS entity_type,
    account_status,
    '-' AS register_date
FROM consulting_office
";

$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>

    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">

    <style>
        body{
            margin: 0;
            background-color: #f6f4f1;
            font-family: "Noto Kufi Arabic", sans-serif;
        }

        .users-container{
            margin-right: 270px;
            padding: 35px 45px;
            min-height: 100vh;
        }

        .users-title{
            text-align:right;
            color: #3E2454;
            font-size: 30px;
            font-weight: 700;
            margin-top: -50px;
            margin-bottom: 8px;
        }

        .title-line{
            width: 100%;
            height: 2px;
            background: #bfbfbf;
            margin: 0 0 25px 0;
        }

        .top-bar{
            width: 100%;
            display: flex;
            justify-content: flex-end;
            margin-bottom: 50px;
            margin-top: auto;
        }

        .profile-btn{
            background: #efe7da;
            color: #6fa5be;
            border: 1px solid #b9b1a5;
            border-radius: 16px;
            padding: 8px 18px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .users-card{
            background: #ffffff;
            border: 1px solid #d8d8d8;
            border-radius: 6px;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .users-table{
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            table-layout: fixed;
        }

        .users-table thead th{
            color: #3E2454;
            font-size: 15px;
            font-weight: 700;
            padding: 16px 10px;
            background-color: #fbfbfb;
            border-bottom: 1px solid #cfcfcf;
        }

        .users-table thead th:not(:last-child){
            border-left: 1px solid #cfcfcf;
        }

        .users-table tbody td{
            color: #595959;
            font-size: 14px;
            font-weight: 500;
            padding: 18px 10px;
            background-color: #fff;
            vertical-align: middle;
            border-bottom: 1px solid #d9d9d9;
        }

        .account-status{
            display: inline-block;
            min-width: 95px;
            padding: 8px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
        }

        .status-active{
            background: #6db387;
        }

        .status-blocked{
            background: #c4474f;
        }

       .actions-box{
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.btn{
    border: none;
    padding: 10px 18px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 13px;
    font-family: "Noto Kufi Arabic", sans-serif;
    font-weight: 600;
    min-width: 125px;
}

.btn-block{
    background: #c4474f;
    color: #fff;
}

.btn-activate{
    background: #ffffff;
    color: #3E2454;
    border: 1px solid #9a9a9a;
}

        .empty-row td{
            height: 62px;
            background-color: #fff;
            border-bottom: 1px solid #d9d9d9;
        }

        @media (max-width: 1100px){
            .users-container{
                margin-right: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- إذا عندك السايدبار جاهز استبدليه بهذا الجزء -->
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="شعار نورين">
            </div>

            <ul class="sidebar-menu">
                <li><a href="Admin3_Contracts.php">إدارة العقود</a></li>
                <li><a href="Admin5_Complaints.php">الشكاوى والاستفسارات</a></li>
                <li><a href="Admin4_UsersManage.php" class="active">إدارة المستخدمين</a></li>
                <li><a href="Admin2_ EntitiesApproval.php">اعتماد الجهات</a></li>
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

    <div class="users-container">

        <div class="top-bar">
            <a href="admin_profile.php" class="profile-btn">الملف الشخصي</a>
        </div>

        <h2 class="users-title">إدارة المستخدمين</h2>
        <div class="title-line"></div>

        <div class="users-card">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>اسم المستخدم</th>
                        <th>نوع الحساب</th>
                        <th>تاريخ التسجيل</th>
                        <th>حالة الحساب</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {

                            $status = trim((string)$row['account_status']);

                            if ($status === "محظور") {
                                $statusText = "محظور";
                                $statusClass = "status-blocked";
                            } else {
                                $statusText = "نشط";
                                $statusClass = "status-active";
                            }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['entity_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['entity_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['register_date']); ?></td>
                        <td>
                            <span class="account-status <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions-box">

                               

                                <?php if ($statusText == "نشط") { ?>
                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="entity_id" value="<?php echo $row['entity_id']; ?>">
                                        <input type="hidden" name="entity_type" value="<?php echo $row['entity_type']; ?>">
                                        <button type="submit" name="block" class="btn btn-block">حظر الحساب</button>
                                    </form>
                                <?php } else { ?>
                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="entity_id" value="<?php echo $row['entity_id']; ?>">
                                        <input type="hidden" name="entity_type" value="<?php echo $row['entity_type']; ?>">
                                        <button type="submit" name="activate" class="btn btn-activate">تنشيط الحساب</button>
                                    </form>
                                <?php } ?>

                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="5">لا توجد بيانات لعرضها</td>
                    </tr>
                    <?php } ?>

                    <tr class="empty-row">
                        <td colspan="5"></td>
                    </tr>
                    <tr class="empty-row">
                        <td colspan="5"></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>