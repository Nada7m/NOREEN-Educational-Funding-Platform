<?php
session_start();

/* الاتصال بقاعدة البيانات */
$con = mysqli_connect("localhost", "root", "", "noreen");

if (!$con) {
    die("فشل الاتصال بقاعدة البيانات");
}

mysqli_set_charset($con, "utf8mb4");

/* تحديد التبويب الحالي */
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';

if ($tab == 'replied') {
    $status_filter = "تم الرد";
} else {
    $status_filter = "بانتظار الرد";
    $tab = 'pending';
}

/* جلب الشكاوى والاستفسارات مع اسم المرسل */
$sql = "
SELECT 
    c.ticket_id,
    c.subject,
    c.message,
    c.submission_date,
    c.status,
    c.admin_reply,
    c.bnf_id,
    c.inv_id,
    c.office_id,

    CASE
        WHEN c.bnf_id IS NOT NULL THEN CONCAT(b.f_name, ' ', b.l_name)
        WHEN c.inv_id IS NOT NULL THEN i.inv_name
        WHEN c.office_id IS NOT NULL THEN o.office_name
        ELSE 'غير معروف'
    END AS sender_name

FROM complaints_inquiries c

LEFT JOIN beneficiary b ON c.bnf_id = b.bnf_id
LEFT JOIN investor i ON c.inv_id = i.inv_id
LEFT JOIN consulting_office o ON c.office_id = o.office_id

WHERE c.status = '$status_filter'
ORDER BY c.ticket_id DESC
";

$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الشكاوى والاستفسارات</title>

    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS01Layout.css">

    <style>
        body{
            margin: 0;
            background-color: #f6f4f1;
            font-family: "Noto Kufi Arabic", sans-serif;
        }

        .permissions-container{
            margin-right: 270px;
            padding: 35px 45px;
            min-height: 100vh;
        }

        .permissions-title{
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

        /* التبويبات */
        .tabs-box{
            display: flex;
            width: 100%;
            border: 1px solid #d0d0d0;
            background: #f3f3f3;
            margin-bottom: 18px;
            overflow: hidden;
        }

        .tab-btn{
            flex: 1;
            text-align: center;
            padding: 14px 10px;
            text-decoration: none;
            color: #3E2454;
            font-size: 15px;
            font-weight: 700;
            background: #ffffff;
            border-left: 1px solid #d0d0d0;
            transition: 0.2s;
        }

        .tab-btn:last-child{
            border-left: none;
        }

        .tab-btn.active{
            background: #F2F2F2;
        }

        .permissions-card{
            background: #ffffff;
            border: 1px solid #d8d8d8;
            border-radius: 6px;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .permissions-table{
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            table-layout: fixed;
        }

        .permissions-table thead th{
            color: #3E2454;
            font-size: 15px;
            font-weight: 700;
            padding: 16px 10px;
            background-color: #fbfbfb;
            border-bottom: 1px solid #cfcfcf;
        }

        .permissions-table thead th:not(:last-child){
            border-left: 1px solid #cfcfcf;
        }

        .permissions-table tbody td{
            color: #595959;
            font-size: 14px;
            font-weight: 500;
            padding: 18px 10px;
            background-color: #fff;
            vertical-align: middle;
            border-bottom: 1px solid #d9d9d9;
        }

        .ticket-subject{
            line-height: 1.9;
            word-break: break-word;
        }

        .btn{
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 13px;
            font-family: "Noto Kufi Arabic", sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            min-width: 130px;
            text-align: center;
        }

        .btn-outline{
            background: #ffffff;
            color: #3E2454;
            border: 1px solid #8f8f8f;
        }

        .empty-row td{
            height: 62px;
            background-color: #fff;
            border-bottom: 1px solid #d9d9d9;
        }

        .no-data{
            color: #8d8d8d;
            font-size: 14px;
            padding: 25px 10px !important;
        }

        @media (max-width: 1100px){
            .permissions-container{
                margin-right: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- السايدبار -->
    <aside class="sidebar">
        <div class="sidebar-top">

            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="شعار نورين">
            </div>

                <ul class="sidebar-menu">
                <li><a href="Admin3_Contracts.php">إدارة العقود</a></li>
                <li><a href="Admin5_Complaints.php"class="active">الشكاوى والاستفسارات</a></li>
                <li><a href="Admin4_UsersManage.php" >إدارة المستخدمين</a></li>
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

    <!-- المحتوى -->
    <div class="permissions-container">

        <div class="top-bar">
            <a href="admin_profile.php" class="profile-btn">الملف الشخصي</a>
        </div>

        <h2 class="permissions-title">الشكاوى والاستفسارات</h2>
        <div class="title-line"></div>

        <!-- التبويبات -->
        <div class="tabs-box">
            <a href="Admin5_Complaints.php?tab=replied" class="tab-btn <?php echo ($tab == 'replied') ? 'active' : ''; ?>">
                تم الرد
            </a>
            <a href="Admin5_Complaints.php?tab=pending" class="tab-btn <?php echo ($tab == 'pending') ? 'active' : ''; ?>">
                بانتظار الرد
            </a>
        </div>

        <!-- الجدول -->
        <div class="permissions-card">
            <table class="permissions-table">
                <thead>
                    <tr>
                        <th>رقم التذكرة</th>
                        <th>المرسل</th>
                        <th>تاريخ الإرسال</th>
                        <th>العنوان</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                        <td><?php echo "TKT-" . str_pad($row['ticket_id'], 3, "0", STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($row['sender_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        <td class="ticket-subject"><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td>
                            <?php if ($tab == 'pending') { ?>
                                <a href="Admin4_ReplyTicket.php?id=<?php echo $row['ticket_id']; ?>" class="btn btn-outline">الرد على التذكرة</a>
                            <?php } else { ?>
                                <a href="Admin4_ViewReply.php?id=<?php echo $row['ticket_id']; ?>" class="btn btn-outline">عرض</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" class="no-data">لا توجد تذاكر في هذا القسم</td>
                    </tr>

                    <tr class="empty-row"><td colspan="5"></td></tr>
                    <tr class="empty-row"><td colspan="5"></td></tr>
                    <tr class="empty-row"><td colspan="5"></td></tr>

                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>