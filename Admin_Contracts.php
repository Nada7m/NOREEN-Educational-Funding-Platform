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

        /* البحث صار فوق الصندوق الأبيض */
        .search-box{
            margin-bottom: 18px;
            position: relative;
        }

        .search-box input{
            width: 100%;
            height: 50px;
            border: 1px solid #bba8c8;
            border-radius: 3px;
            padding: 0 18px;
            padding-right: 50px;
            color: #595959;
            font-size: 15px;
            outline: none;
            background-color: #fff;
            font-family: "Noto Kufi Arabic", sans-serif;
        }

        .search-box input::placeholder{
            color: #c6c6c6;
        }

        .search-box::before{
            content: "⌕";
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #8b6f99;
            font-size: 20px;
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

        /* عناوين الأعمدة */
        .permissions-table thead th{
            color: #3E2454;
            font-size: 15px;
            font-weight: 700;
            padding: 16px 10px;
            background-color: #fbfbfb;
            border-bottom: 1px solid #cfcfcf;
        }

        /* خطوط فقط بين عناوين الأعمدة */
        .permissions-table thead th:not(:last-child){
            border-left: 1px solid #cfcfcf;
        }

        /* بيانات الجدول: بدون خطوط عمودية */
        .permissions-table tbody td{
            color: #595959;
            font-size: 14px;
            font-weight: 500;
            padding: 18px 10px;
            background-color: #fff;
            vertical-align: middle;
            border-bottom: 1px solid #d9d9d9;
        }

        .status{
            display: inline-block;
            min-width: 95px;
            background: #6db387;
            color: #fff;
            padding: 8px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .btn{
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 13px;
            font-family: "Noto Kufi Arabic", sans-serif;
            font-weight: 600;
        }

        .btn-delete{
            background: #c4474f;
            color: #fff;
        }

        .empty-row td{
            height: 62px;
            background-color: #fff;
            border-bottom: 1px solid #d9d9d9;

        }
       

    .top-bar{
      width: 100%;
      display: flex;
      justify-content: flex-end;
      margin-bottom: 50px;
      margin-top: auto;
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

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-logo">
                <img src="شعار نورين.png" alt="شعار نورين">
            </div>

            <ul class="sidebar-menu">
                <li><a href="Admin_Contracts.php" class="active">إدارة العقود</a></li>
                <li><a href="complaints.php">الشكاوى والاستفسارات</a></li>
                <li><a href="users_manage.php">إدارة المستخدمين</a></li>
                <li><a href="Admin2_ EntitiesApproval.php">اعتماد الجهات</a></li>
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
    
  <div class="page-content">

    <div class="top-bar">
      <a href="admin_profile.php" class="profile-btn">بيانات الحساب</a>
    </div>


    <div class="permissions-container">
        <div class="permissions-title">إدارة العقود</div>
        <div class="title-line"></div>

        <div class="search-box">
            <input type="text" placeholder="أدخل اسم المنحة">
        </div>

        <div class="permissions-card">
            <table class="permissions-table">
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

</body>
</html>