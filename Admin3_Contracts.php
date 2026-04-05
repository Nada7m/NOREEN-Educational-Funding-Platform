<?php
/* بيانات تجريبية */
$contracts = [
    [
        "scholarship_name"   => "منحة تطوير المهارات الرقمية",
        "investor_name"      => "مؤسسة أفق",
        "beneficiary_name"   => "نور الجهني",
        "date"               => "15-12-2025",
        "status"             => "نشط"
    ]
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العقود</title>

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
            background:#ffffff;
        }

        .main-content{
            flex:1;
            background:#ffffff;
        }

        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:25px 30px;
            border-bottom:1px solid #ddd;
            background:#ffffff;
        }

        .page-title{
            font-size:22px;
            font-weight:800;
            color:#3E2454;
        }

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

        .page-wrapper{
            padding:40px;
            background:#ffffff;
        }

        .search-box{
            width:100%;
            max-width:1050px;
            margin:0 auto 18px;
            position:relative;
        }

        .search-box input{
            width:100%;
            height:48px;
            border:1px solid #d8d8d8;
            border-radius:8px;
            padding:0 18px;
            padding-right:48px;
            color:#595959;
            font-size:14px;
            outline:none;
            background:#ffffff;
            font-family:"Noto Kufi Arabic", sans-serif;
        }

        .search-box input::placeholder{
            color:#b8b8b8;
        }

        .search-box::before{
            content:"⌕";
            position:absolute;
            right:16px;
            top:50%;
            transform:translateY(-50%);
            color:#8b6f99;
            font-size:18px;
        }

        .table-box{
            width:100%;
            max-width:1050px;
            margin:0 auto;
            background:#ffffff;
            border:1px solid #e6e0e6;
            border-radius:8px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }

        table{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            background:#ffffff;
        }

        thead th{
            padding:15px 12px;
            background:#fafafa;
            border-bottom:1px solid #ddd;
            font-size:15px;
            font-weight:700;
            color:#3E2454;
            text-align:center;
        }

        tbody td{
            padding:16px 12px;
            border-bottom:1px solid #eee;
            text-align:center;
            vertical-align:middle;
            font-size:14px;
            font-weight:500;
            color:#595959;
            background:#ffffff;
        }

        .status{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:100px;
            height:42px;
            border-radius:12px;
            color:#fff;
            font-size:14px;
            font-weight:600;
            background:#2E8B57;
        }

        .btn{
            width:100px;
            height:42px;
            border:none;
            border-radius:12px;
            cursor:pointer;
            font-size:14px;
            font-family:"Noto Kufi Arabic", sans-serif;
            font-weight:600;
        }

        .btn-delete{
            background:#A53A3A;
            color:#fff;
        }

        .empty-row td{
            height:62px;
            background:#fff;
            border-bottom:1px solid #eee;
        }

        @media (max-width:1100px){
            .page-wrapper{
                padding:25px 15px;
            }

            .table-box,
            .search-box{
                max-width:100%;
                overflow:auto;
            }

            table{
                min-width:900px;
            }
        }
    </style>
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="شعار نورين">
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
                    <b>تسجيل الخروج</b>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content">

        <div class="header">
            <div class="page-title">إدارة العقود</div>
            <a href="Admin1_profile.php" class="profile-btn">بيانات الحساب</a>
        </div>

        <div class="page-wrapper">

            <div class="search-box">
                <input type="text" placeholder="أدخل اسم المنحة">
            </div>

            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>اسم المنحة</th>
                            <th>اسم المستثمر</th>
                            <th>اسم المستفيد</th>
                            <th>التاريخ</th>
                            <th>حالة العقد</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($contracts as $row): ?>
                        <tr>
                            <td><?= $row["scholarship_name"] ?></td>
                            <td><?= $row["investor_name"] ?></td>
                            <td><?= $row["beneficiary_name"] ?></td>
                            <td><?= $row["date"] ?></td>
                            <td><span class="status"><?= $row["status"] ?></span></td>
                            <td>
                                <button class="btn btn-delete">إنهاء العقد</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <tr class="empty-row"><td colspan="6"></td></tr>
                        <tr class="empty-row"><td colspan="6"></td></tr>
                        <tr class="empty-row"><td colspan="6"></td></tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

</body>
</html>