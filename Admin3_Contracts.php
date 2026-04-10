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
<link rel="stylesheet" href="CSS_AdminLayout.css">

<style>
.page-wrapper{
  padding:40px;
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
  border:1px solid #D8D8D8;
  border-radius:8px;
  padding:0 18px;
  padding-right:48px;
  color:#595959;
  font-size:14px;
  outline:none;
  background:#FFFFFF;
  font-family:"Noto Kufi Arabic",sans-serif;
}

.search-box input::placeholder{
  color:#B8B8B8;
}

.search-box::before{
  content:"⌕";
  position:absolute;
  right:16px;
  top:50%;
  transform:translateY(-50%);
  color:#8B6F99;
  font-size:18px;
}

.table-box{
  width:100%;
  max-width:1050px;
  margin:0 auto;
  background:#FFFFFF;
  border:1px solid #E6E0E6;
  border-radius:8px;
  box-shadow:0 2px 10px rgba(0,0,0,0.05);
  overflow:hidden;
}

table{
  width:100%;
  border-collapse:collapse;
  table-layout:fixed;
  background:#FFFFFF;
}

thead th{
  padding:15px 12px;
  background:#FAFAFA;
  border-bottom:1px solid #DDDDDD;
  font-size:15px;
  font-weight:700;
  color:#3E2454;
  text-align:center;
}

tbody td{
  padding:16px 12px;
  border-bottom:1px solid #EEEEEE;
  text-align:center;
  vertical-align:middle;
  font-size:14px;
  font-weight:500;
  color:#595959;
  background:#FFFFFF;
}

.status{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:100px;
  height:42px;
  border-radius:12px;
  color:#FFFFFF;
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
  font-family:"Noto Kufi Arabic",sans-serif;
  font-weight:600;
}

.btn-delete{
  background:#A53A3A;
  color:#FFFFFF;
}

.empty-row td{
  height:62px;
  background:#FFFFFF;
  border-bottom:1px solid #EEEEEE;
}


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
</div>

</body>
</html>