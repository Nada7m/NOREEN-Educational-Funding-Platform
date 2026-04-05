<?php
session_start();

/* الاتصال */
$con = mysqli_connect("localhost","root","","noreen");
mysqli_set_charset($con,"utf8mb4");

/* تحديث الحالة */
if(isset($_POST['approve']) || isset($_POST['reject'])){
    $id = (int)$_POST['entity_id'];
    $type = $_POST['entity_type'];
    $status = isset($_POST['approve']) ? 'معتمد' : 'مرفوض';

    if($type=="مستثمر"){
        mysqli_query($con,"UPDATE investor SET approval_status='$status' WHERE inv_id=$id");
    } else {
        mysqli_query($con,"UPDATE consulting_office SET approval_status='$status' WHERE office_id=$id");
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* جلب البيانات */
$result = mysqli_query($con,"
SELECT inv_id AS entity_id, inv_name AS entity_name, ccr_number, approval_status,'مستثمر' AS entity_type FROM investor
UNION ALL
SELECT office_id, office_name, ccr_number, approval_status,'مكتب استشاري' FROM consulting_office
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>اعتماد الجهات</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css">

<style>
body{
    margin:0;
    background:#ffffff;
    font-family:"Noto Kufi Arabic", sans-serif;
}

.layout{
    display:flex;
    min-height:100vh;
}

.main-content{
    flex:1;
}

/* الهيدر */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:25px 30px;
    border-bottom:1px solid #ddd;
}

/* العنوان */
.page-title{
    font-size:22px;
    font-weight:800;
    color:#3E2454;
}

/* زر الحساب */
.profile-btn{
    background:#efe7da;
    color:#6fa5be;
    border:1px solid #b9b1a5;
    border-radius:16px;
    padding:8px 18px;
    text-decoration:none;
    font-size:14px;
    font-weight:700;
    white-space:nowrap;
}

/* المحتوى */
.page-wrapper{
    padding:40px;
}

/* الجدول (بدون سكرول) */
.table-box{
    width:100%;
    max-width:1050px;
    margin:0 auto;
    background:#fff;
    border:1px solid #e6e0e6;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

thead th{
    padding:15px;
    background:#fafafa;
    border-bottom:1px solid #ddd;
}

tbody td{
    padding:16px;
    border-bottom:1px solid #eee;
    text-align:center;
}

/* الحالة */
.status{
    display:flex;
    align-items:center;
    justify-content:center;

    width:100px;     /* نفس عرض الأزرار */
    height:42px;     /* نفس الطول */
    
    border-radius:12px;
    color:#fff;
    font-size:14px;
    font-weight:600;
}

.pending{ background:#D8B35E; }
.approved{ background:#2E8B57; }
.rejected{ background:#C23B3B; }

/* الأزرار */
.actions{
    display:flex;
    justify-content:center;
    gap:10px;
    flex-wrap:nowrap;
}

.btn{
    width:100px;
    height:42px;
    border-radius:12px;
    font-size:14px;
    cursor:pointer;
}

.accept{
    background:#fff;
    border:1px solid #ccc;
}

.reject{
    background:#A53A3A;
    color:#fff;
    border:none;
}
</style>
</head>

<body>

<div class="layout">

<!-- السايدبار -->
<aside class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-logo">
            <img src="شعار نورين.png">
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
                <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
                <b>تسجيل الخروج</b>
            </button>
        </form>
    </div>
</aside>

<!-- المحتوى -->
<div class="main-content">

    <div class="header">
        <div class="page-title">اعتماد الجهات</div>
        <a href="Admin1_profile.php" class="profile-btn">بيانات الحساب</a>
    </div>

    <div class="page-wrapper">

        <div class="table-box">

            <table>
                <thead>
                    <tr>
                        <th>اسم الجهة</th>
                        <th>النوع</th>
                        <th>السجل</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($row=mysqli_fetch_assoc($result)){

                    $status=$row['approval_status'];

                    if($status=="معتمد"){ $class="approved"; }
                    elseif($status=="مرفوض"){ $class="rejected"; }
                    else{ $status="بانتظار"; $class="pending"; }

                ?>

                <tr>
                    <td><?= $row['entity_name'] ?></td>
                    <td><?= $row['entity_type'] ?></td>
                    <td><?= $row['ccr_number'] ?></td>

                    <td><span class="status <?= $class ?>"><?= $status ?></span></td>

                    <td>
                        <?php if($status=="بانتظار"){ ?>
                        <div class="actions">

                            <form method="post">
                                <input type="hidden" name="entity_id" value="<?= $row['entity_id'] ?>">
                                <input type="hidden" name="entity_type" value="<?= $row['entity_type'] ?>">
                                <button name="approve" class="btn accept">اعتماد</button>
                            </form>

                            <form method="post">
                                <input type="hidden" name="entity_id" value="<?= $row['entity_id'] ?>">
                                <input type="hidden" name="entity_type" value="<?= $row['entity_type'] ?>">
                                <button name="reject" class="btn reject">رفض</button>
                            </form>

                        </div>
                        <?php } ?>
                    </td>
                </tr>

                <?php } ?>

                </tbody>
            </table>

        </div>

    </div>

</div>

</div>

</body>
</html>