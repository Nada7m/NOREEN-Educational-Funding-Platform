<?php
session_start();
/**  يجب أن يكون المستثمر مسجل دخول **/
if(!isset($_SESSION['inv_id'])){
    header("Location: login.php");
    exit();}
/* الاتصال بقاعدة البيانات */
$con=mysqli_connect("localhost","root","","noreen");
if(!$con){
    die("فشل الاتصال بقاعدة البيانات");
}
mysqli_set_charset($con,"utf8mb4");
/* رقم المستثمر الحالي */
$inv_id=$_SESSION['inv_id'];
/** يجب وجود رقم المنحة في الرابط **/
if(!isset($_GET['id'])||$_GET['id']==""){
    die("رقم المنحة غير موجود.");
}
/* تحويل رقم المنحة إلى رقم صحيح */
$scholarship_id=(int)$_GET['id'];
/* قبول أو رفض الطلب */
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["action_type"]) && isset($_POST["request_id"])){
      /* رقم الطلب */
    $request_id=(int)$_POST["request_id"];
        /* نوع الإجراء */
    $action_type=$_POST["action_type"];
        /* الحالة الجديدة */
    $new_status="";
    /**  حالة الطلب تكون فقط مقبول أو مرفوض **/
    if($action_type=="accept"){   $new_status="مقبول";  }elseif($action_type=="reject"){  $new_status="مرفوض"; }

    if($new_status!=""){
        $update_stmt=mysqli_prepare($con,"
            UPDATE scholarship_requests sr
            INNER JOIN scholarship_opps so ON sr.scholarship_id=so.scholarship_id
            SET sr.request_status=?
            WHERE sr.request_id=? AND sr.scholarship_id=? AND so.inv_id=?
        ");
        mysqli_stmt_bind_param($update_stmt,"siii",$new_status,$request_id,$scholarship_id,$inv_id);
   mysqli_stmt_execute($update_stmt);
    }
}

/* بيانات المنحة */
$stmt=mysqli_prepare($con,"
    SELECT scholarship_id,sch_name,sch_field,study_level,Specializations,requirements,app_deadline
    FROM scholarship_opps
    WHERE scholarship_id=? AND inv_id=?
");
mysqli_stmt_bind_param($stmt,"ii",$scholarship_id,$inv_id);
mysqli_stmt_execute($stmt);
$result=mysqli_stmt_get_result($stmt);
$scholarship=mysqli_fetch_assoc($result);

if(!$scholarship){
    die("لم يتم العثور على بيانات هذه المنحة.");
}

/* جلب المتقدمين */
$applicants=[];
$app_stmt=mysqli_prepare($con,"
    SELECT
        scholarship_requests.request_id,
        scholarship_requests.bnf_id,
        scholarship_requests.univ_name,
        scholarship_requests.major_name,
        scholarship_requests.request_status,
        scholarship_requests.Submit_date,
        beneficiary.f_name,
        beneficiary.l_name,
        beneficiary.email,
        beneficiary.phone_num,
        beneficiary.sch_field,
        beneficiary.degree_level
    FROM scholarship_requests
    LEFT JOIN beneficiary ON scholarship_requests.bnf_id=beneficiary.bnf_id
    WHERE scholarship_requests.scholarship_id=? AND scholarship_requests.request_status<>'مرفوض'
    ORDER BY scholarship_requests.request_id DESC
");
mysqli_stmt_bind_param($app_stmt,"i",$scholarship_id);
mysqli_stmt_execute($app_stmt);
$app_result=mysqli_stmt_get_result($app_stmt);

while($row=mysqli_fetch_assoc($app_result)){

    /* ملفات هذا الطلب */
    $documents=[];
    $doc_stmt=mysqli_prepare($con,"
        SELECT doc_id,doc_type,file_name,file
        FROM scholarship_request_documents
        WHERE request_id=?
        ORDER BY doc_id ASC
    ");
    mysqli_stmt_bind_param($doc_stmt,"i",$row["request_id"]);
    mysqli_stmt_execute($doc_stmt);
    $doc_result=mysqli_stmt_get_result($doc_stmt);

    while($doc_row=mysqli_fetch_assoc($doc_result)){
        $documents[]=$doc_row;
    }

    $row["documents"]=$documents;
    $applicants[]=$row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تفاصيل المنحة</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=3">
<style>
.back-btn{width:34px;height:34px;display:flex;align-items:center;justify-content:center}
.tabs-row{width:90%;margin:0 auto 18px;display:flex;flex-direction:row-reverse;justify-content:center}
.tab{width:50%;height:64px;border:1px solid #D9D9D9;display:flex;justify-content:center;align-items:center;font-size:18px;font-weight:700;color:#3E2454;background:#FFFFFF;cursor:pointer;transition:.2s}
.tab-active{background:#F2F2F2}
.tab-content{display:none}
.tab-content.active{display:block}
.scholarship-details-box{width:90%;min-height:430px;margin:0 auto;background:#FFFFFF;border:1px solid #E3E3E3;border-radius:8px;padding:34px 36px 30px;box-sizing:border-box}
.top-info-row{display:flex;flex-direction:row-reverse;justify-content:space-between;align-items:flex-start;gap:40px}
.main-info-box{width:68%;text-align:right}
.main-title{margin:0 0 18px 0;font-size:22px;font-weight:700;color:#3E2454;line-height:1.9}
.double-info-row{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:70px;margin-bottom:18px}
.info-item{display:flex;flex-direction:column;align-items:flex-end}
.info-label{font-size:16px;font-weight:500;color:#666666;margin-bottom:6px}
.info-value{font-size:17px;font-weight:700;color:#70A0AF}
.specialization-line{font-size:16px;line-height:2;text-align:right}
.specialization-label{color:#555555;font-weight:600}
.specialization-value{color:#70A0AF;font-weight:500}
.deadline-box{width:28%;text-align:right;direction:rtl;padding-top:50px;display:flex;align-items:center;justify-content:flex-start;gap:6px;flex-wrap:wrap}
.deadline-label{font-size:15px;font-weight:700;color:#3E2454;white-space:nowrap}
.deadline-value{font-size:15px;font-weight:500;color:#70A0AF;direction:ltr;unicode-bidi:isolate;white-space:nowrap;display:inline-block}
.section-divider{width:100%;height:1px;background:#DCDCDC;margin:28px 0 22px}
.conditions-box{text-align:right}
.conditions-title{margin:0 0 14px 0;font-size:17px;font-weight:700;color:#3E2454}
.conditions-list{margin:0;padding-right:22px;list-style-type:disc}
.conditions-list li{font-size:16px;color:#444444;line-height:2;margin-bottom:2px}
.date-cell{direction:ltr;unicode-bidi:isolate;white-space:nowrap}
.requests-box{width:90%;margin:0 auto;background:#FFFFFF;border:1px solid #E3E3E3;border-radius:8px;padding:30px;box-sizing:border-box}
.requests-title{margin:0 0 18px 0;font-size:22px;font-weight:700;color:#3E2454;text-align:right}
.requests-table{width:100%;border-collapse:collapse}
.requests-table th,.requests-table td{border:1px solid #E4E4E4;padding:12px 10px;text-align:right;font-size:14px;vertical-align:middle}
.requests-table th{background:#F6F6F6;color:#3E2454;font-weight:700}
.empty-requests{text-align:center;font-size:18px;color:#777777;padding:30px 10px}
.actions-box{display:flex;justify-content:center}
.action-btn{border:none;border-radius:12px;padding:10px 16px;font-size:14px;font-weight:700;cursor:pointer;font-family:"Noto Kufi Arabic",sans-serif}
.view-btn{background:#4a2b63;color:#FFFFFF;min-width:120px}
.accept-btn{background:#65b185;color:#FFFFFF;min-width:120px;height:44px;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;font-family:"Noto Kufi Arabic",sans-serif}
.reject-btn{background:#d83b3b;color:#FFFFFF;min-width:120px;height:44px;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;font-family:"Noto Kufi Arabic",sans-serif}
.accepted-static-btn{background:#65b185;color:#FFFFFF;cursor:default;pointer-events:none}
.status-text{font-weight:700;color:#3E2454}
.download-file-btn{min-width:120px;height:42px;padding:0 16px;border:1px solid #9F9F9F;border-radius:14px;background:#FFFFFF;color:#4a2b63;font-size:14px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;font-family:"Noto Kufi Arabic",sans-serif;box-sizing:border-box}
.download-file-btn:hover{background:#F8F6FB}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);justify-content:center;align-items:center;z-index:3000;padding:20px;box-sizing:border-box}
.modal-overlay.show{display:flex}
.modal-box{width:680px;max-width:94%;max-height:90vh;overflow:auto;background:#FFFFFF;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.18);padding:22px}
.modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.modal-title{font-size:21px;font-weight:700;color:#4a2b63;margin:0}
.modal-close{width:38px;height:38px;border:none;border-radius:50%;background:#f1f1f1;color:#4a2b63;font-size:22px;cursor:pointer;font-family:"Noto Kufi Arabic",sans-serif}
.info-box{background:#FFFFFF;border:1px solid #D9D9D9;border-radius:10px;padding:16px 18px;margin-bottom:16px;line-height:2.1;color:#555555;font-size:16px}
.info-row{margin-bottom:2px}
.info-label-modal{color:#76a6b7;font-weight:700;margin-left:6px}
.files-title{font-size:19px;font-weight:700;color:#4a2b63;text-align:right;margin:0 0 12px 0}
.file-item{background:#FFFFFF;border:1px solid #D9D9D9;border-radius:10px;padding:14px 16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.file-info{flex:1;text-align:right}
.file-type{font-size:15px;color:#777777;margin-bottom:4px}
.file-name{font-size:15px;color:#4a2b63;font-weight:700;word-break:break-word}
.modal-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:16px}
.modal-actions form{margin:0}
.hidden-form{display:none}
@media (max-width:700px){
  .modal-box{padding:18px 14px}
  .file-item{align-items:flex-start}
  .modal-actions form,.modal-actions button{width:100%}
}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
<div class="sidebar-top">
<div class="sidebar-logo"><img src="شعار نورين.png" alt="نورين"></div>
<ul class="sidebar-menu">
<li><a href="Inv00_MainPage.php">الرئيسية</a></li>
<li><a href="Inv04_CreateScholarship.php" class="active">عرض المنح</a></li>
<li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
<li><a href="Inv10_Payments.php">المدفوعات</a></li>
</ul>
</div>
<div class="sidebar-bottom">
<form action="logout.php" method="post">
<button type="submit" class="logout-btn">
<img src="ايقونة تسجيل الخروج.png" class="logout-icon" alt="تسجيل الخروج">
<b>تسجيل الخروج</b>
</button>
</form>
</div>
</aside>

<div class="main-content">
<header class="header">
<div class="page-heading">
<h1 class="page-title">عرض المنح</h1>
<p class="page-description">صفحة تقديم عروض فرص المنح</p>
</div>
<div class="header-icons">
<div class="settings-dropdown">
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">
<div class="dropdown-menu">
<a href="Inv02_Profile.php">الملف الشخصي</a>
<a href="support.php">تقديم شكوى او استفسار</a>
</div>
</div>
</div>
</header>

<section class="page">
<div class="page-top">
<div></div>
<div class="back-btn">
<a href="Inv04_CreateScholarship.php"><img src="سهم تراجع.svg" width="40"></a>
</div>
</div>

<div class="tabs-row">
<div class="tab tab-active" data-target="scholarship-tab">تفاصيل المنحة</div>
<div class="tab" data-target="requests-tab">تفاصيل المتقدمين</div>
</div>

<div id="scholarship-tab" class="tab-content active">
<div class="scholarship-details-box">
<div class="top-info-row">
<div class="deadline-box">
<span class="deadline-label">آخر موعد للتقديم:</span>
<span class="deadline-value"><?php echo date("d-m-Y",strtotime($scholarship['app_deadline'])); ?></span>
</div>
<div class="main-info-box">
<h2 class="main-title"><?php echo e($scholarship['sch_name']); ?></h2>
<div class="double-info-row">
<div class="info-item">
<span class="info-label">المجال الرئيسي:</span>
<span class="info-value"><?php echo e($scholarship['sch_field']); ?></span>
</div>
<div class="info-item">
<span class="info-label">الدرجة المستهدفة:</span>
<span class="info-value"><?php echo e($scholarship['study_level']); ?></span>
</div>
</div>
<div class="specialization-line">
<span class="specialization-label">التخصصات الدقيقة:</span>
<span class="specialization-value"><?php echo e($scholarship['Specializations']); ?></span>
</div>
</div>
</div>

<div class="section-divider"></div>

<div class="conditions-box">
<h3 class="conditions-title">الشروط:</h3>
<?php $requirements_lines=preg_split("/\r\n|\n|\r/",$scholarship['requirements']); ?>
<ul class="conditions-list">
<?php foreach($requirements_lines as $line): ?>
<?php if(trim($line)!=""): ?>
<li><?php echo e(trim($line)); ?></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>
</div>
</div>
</div>

<div id="requests-tab" class="tab-content">
<div class="requests-box">
<h2 class="requests-title">تفاصيل المتقدمين على هذه المنحة</h2>

<?php if(count($applicants)==0): ?>
<div class="empty-requests">لا يوجد متقدمون على هذه المنحة حتى الآن.</div>
<?php else: ?>
<table class="requests-table">
<thead>
<tr>
<th>الاسم الأول</th>
<th>الاسم الأخير</th>
<th>الجامعة</th>
<th>التخصص</th>
<th>حالة الطلب</th>
<th>تاريخ التقديم</th>
<th>الإجراءات</th>
</tr>
</thead>
<tbody>
<?php foreach($applicants as $applicant): ?>
<tr>
<td><?php echo e($applicant['f_name']); ?></td>
<td><?php echo e($applicant['l_name']); ?></td>
<td><?php echo e($applicant['univ_name']); ?></td>
<td><?php echo e($applicant['major_name']); ?></td>
<td class="status-text"><?php echo e($applicant['request_status']); ?></td>
<td class="date-cell"><?php echo date("d-m-Y",strtotime($applicant['Submit_date'])); ?></td>
<td>
<div class="actions-box">
<button type="button" class="action-btn view-btn open-details-btn" data-modal="modal_<?php echo $applicant['request_id']; ?>">
عرض الإجراءات
</button>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>
</section>
</div>
</div>

<?php foreach($applicants as $applicant): ?>
<div class="modal-overlay" id="modal_<?php echo $applicant['request_id']; ?>">
  <div class="modal-box">
    <div class="modal-head">
      <h2 class="modal-title">تفاصيل تقديم الطلب</h2>
      <button type="button" class="modal-close close-modal-btn">×</button>
    </div>

    <div class="info-box">
      <div class="info-row"><span class="info-label-modal">الاسم الكامل:</span><span><?php echo e($applicant['f_name']." ".$applicant['l_name']); ?></span></div>
      <div class="info-row"><span class="info-label-modal">البريد الإلكتروني:</span><span><?php echo e($applicant['email']); ?></span></div>
      <div class="info-row"><span class="info-label-modal">رقم الجوال:</span><span><?php echo e($applicant['phone_num']); ?></span></div>
      <div class="info-row"><span class="info-label-modal">المجال الدراسي:</span><span><?php echo e($applicant['sch_field']); ?></span></div>
      <div class="info-row"><span class="info-label-modal">الدرجة الحالية:</span><span><?php echo e($applicant['degree_level']); ?></span></div>
      <div class="info-row"><span class="info-label-modal">الجامعة:</span><span><?php echo e($applicant['univ_name']); ?></span></div>
      <div class="info-row"><span class="info-label-modal">التخصص:</span><span><?php echo e($applicant['major_name']); ?></span></div>
      <div class="info-row"><span class="info-label-modal">تاريخ التقديم:</span><span><?php echo date("d-m-Y",strtotime($applicant['Submit_date'])); ?></span></div>
    </div>

    <h3 class="files-title">الملفات المرفقة</h3>
    <div>
      <?php if(count($applicant["documents"])>0): ?>
        <?php foreach($applicant["documents"] as $doc): ?>
          <div class="file-item">
            <div class="file-info">
              <div class="file-type">نوع الملف: <?php echo e($doc['doc_type']); ?></div>
              <div class="file-name"><?php echo e($doc['file_name']); ?></div>
            </div>

            <?php if($doc['file']!=""): ?>
                <a href="uploads/<?php echo e($doc['file']); ?>" target="_blank" class="download-file-btn">تنزيل الملف</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="file-item">
          <div class="file-info">
            <div class="file-name">لا توجد ملفات مرفقة لهذا الطلب.</div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="modal-actions">
      <form method="post">
        <input type="hidden" name="request_id" value="<?php echo $applicant['request_id']; ?>">
        <input type="hidden" name="action_type" value="accept">
        <?php if($applicant['request_status']=="مقبول"): ?>
          <button type="button" class="accept-btn accepted-static-btn">مقبول</button>
        <?php else: ?>
          <button type="submit" class="accept-btn">قبول الطلب</button>
        <?php endif; ?>
      </form>

      <?php if($applicant['request_status']!="مقبول"): ?>
      <form method="post">
        <input type="hidden" name="request_id" value="<?php echo $applicant['request_id']; ?>">
        <input type="hidden" name="action_type" value="reject">
        <button type="submit" class="reject-btn">رفض الطلب</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
const tabs=document.querySelectorAll(".tab");
const contents=document.querySelectorAll(".tab-content");

tabs.forEach(function(tab){
  tab.addEventListener("click",function(){
    tabs.forEach(function(item){item.classList.remove("tab-active");});
    contents.forEach(function(content){content.classList.remove("active");});
    tab.classList.add("tab-active");
    document.getElementById(tab.getAttribute("data-target")).classList.add("active");
  });
});

/* فتح النافذة */
document.querySelectorAll(".open-details-btn").forEach(function(btn){
  btn.addEventListener("click",function(){
    const modalId=this.getAttribute("data-modal");
    document.getElementById(modalId).classList.add("show");
  });
});

/* إغلاق النافذة */
document.querySelectorAll(".close-modal-btn").forEach(function(btn){
  btn.addEventListener("click",function(){
    this.closest(".modal-overlay").classList.remove("show");
  });
});

document.querySelectorAll(".modal-overlay").forEach(function(modal){
  modal.addEventListener("click",function(e){
    if(e.target===modal){
      modal.classList.remove("show");
    }
  });
});
</script>
</body>
</html>