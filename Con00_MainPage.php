<?php
session_start();
$conn=new mysqli("localhost","root","","noreen");
if($conn->connect_error){die("فشل الاتصال بقاعدة البيانات");}
$conn->set_charset("utf8mb4");
$userName="المكتب الاستشاري";
if(isset($_SESSION['office_id'])){
$office_id=$_SESSION['office_id'];
$stmt=$conn->prepare("SELECT office_name FROM consulting_office WHERE office_id=?");
$stmt->bind_param("i",$office_id);
$stmt->execute();
$result=$stmt->get_result();
if($row=$result->fetch_assoc()){$userName=$row['office_name'];}
$stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<meta charset="UTF-8">

<head>
  <title>نورين - الرئيسية</title>

  <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="CSS01Layout.css?v=4">  <link rel="stylesheet" href="CSS02Home.css?v=6">
</head>

<body>

  <div class="layout">

    <!-- الشريط الجانبي -->
    <aside class="sidebar">

      <div class="sidebar-top">

        <div class="sidebar-logo">
          <img src="شعار نورين.png">
        </div>

        <ul class="sidebar-menu">
          <li><a href="Con00_MainPage.php" class="active">الرئيسية</a></li>
          <li><a href="Con04_AdmissionReq.php">إدارة طلبات القبول</a></li>
          <li><a href="Con03_Consultations.php">الاستشارات</a></li>
          <li><a href="Con08_ReqRating.php">تقييمات المستفيدين</a></li>
        </ul>

      </div>

      <div class="sidebar-bottom">
        <button class="logout-btn">
          <img src="ايقونة تسجيل الخروج.png" class="logout-icon">
          <b>تسجيل الخروج</b>
        </button>
      </div>

    </aside>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">

      <!-- الهيدر --><header class="header">
<div class="page-heading">
<div class="page-title">الرئيسية</div>
<div class="welcome-box">
<div class="welcome-text">
أهلًا بك، <?php echo htmlspecialchars($userName); ?>
</div>
</div>
</div>
<div class="header-icons">
<div class="settings-dropdown">
<img src="ايقونة قائمة الاعدادات.png" class="menu-icon">
<div class="dropdown-menu">
<a href="Con02_Profile.php">الملف الشخصي</a>
<a href="support.php">تقديم شكوى او استفسار</a>
</div>
</div>
</div>
</header>

      <!-- محتوى الصفحة -->
      <div class="page">

        <!-- القسم الرئيسي -->
        <section class="intro">

          <div class="int-cnt">

            <div class="int-txt">
              <h2>حين يجد الطموح من يدعمه</h2>
              <h1>يولد المستقبل</h1>

              <p>
                نورين منظومة رقمية تمكّن المستفيدين المتميزين من الوصول إلى فرص تعليمية مدعومة ضمن بيئة موثوقة ومميزة
              </p>
            </div>

            <div class="int-img">
              <img src="نورين الرئيسية.svg">
            </div>

          </div>

        </section>

        <div class="gap"></div>

        <!-- من نحن -->
        <section class="ab-sec" id="about">

          <div class="ab-card">

            <div class="ab-ico">
              <img src="بوابة نورين.svg">
            </div>

            <div class="ab-txt">
              <h2>من نحن - نورين</h2>
              <p>
                نورين منصة إلكترونية تهدف إلى ربط المستفيدين بالمستثمرين
                والمكاتب الاستشارية لتسهيل الحصول على المنح الدراسية.
              </p>
            </div>

          </div>

          <div class="ab-row">
            <div class="ab-info">
              <h3>رؤيتنا</h3>
              <p>
                أن تكون نورين المنصة الرائدة في دعم وتمكين المستفيدين
                للحصول على فرص تعليمية من خلال بيئة رقمية متكاملة
              </p>
            </div>

            <div class="ab-info">
              <h3>رسالتنا</h3>
              <p>
                توفير منصة موثوقة تجمع بين المستفيدين والمستثمرين
                والمكاتب الاستشارية لإدارة المنح الدراسية بكفاءة
              </p>
            </div>
          </div>

          <div class="ab-title">
            من يخدم نورين؟
          </div>

          <div class="ab-btns">
            <button>المستفيدين</button>
            <button>المستثمرين</button>
            <button>المكاتب الاستشارية</button>
          </div>

        </section>

        <!-- خدماتنا -->
        <section class="serv-sec">

          <div class="serv-head">
            <h2>خدماتنا - حلول متكاملة لبناء مستقبل واعد</h2>
            <p>في نورين، نؤمن بأن التعليم هو الاستثمار الأفضل</p>
          </div>

          <div class="serv-row">

            <div class="serv-col">
              <h3>خدمات المكاتب</h3>
              <div class="serv-sub">(سهل خطواتك)</div>

              <div class="serv-card">
                <p>
                  <b>استشارات تعليمية متخصصة:</b>
                  دعم أكاديمي مختص لاختيار التخصص والجامعة المناسبة لمسارك الدراسي
                  <br><br>
                  <b>القبول الجامعي:</b>
                  التقديم ومتابعة طلبات القبول مع الجامعات حتى صدور خطاب القبول
                </p>
              </div>
            </div>

            <div class="serv-col">
              <h3>خدمات المستثمرين</h3>
              <div class="serv-sub">(استثمر في المستقبل)</div>

              <div class="serv-card">
                <p>
                  <b>إدارة المنح الذكية:</b>
                  منصة رقمية شاملة لمتابعة أداء المستفيدين المبتعثين وضمان جودة مخرجاتهم التعليمية
                  <br><br>
                  <b>صناعة الكفاءات الوطنية:</b>
                  صمم برنامج ابتعاث خاص بمنشأتك لاستقطاب وتجهيز كوادر متخصصة
                </p>
              </div>
            </div>

            <div class="serv-col">
              <h3>خدمات المستفيدين</h3>
              <div class="serv-sub">(طموحك يبدأ هنا)</div>

              <div class="serv-card">
                <p>
                  <b>منح تعليمية ممولة:</b>
                  نوفر لك فرصة لإكمال دراستك محليًا أو دوليًا بدعم كامل وسلس مع مسار وظيفي مضمون
                  <br><br>
                  <b>استشارات وتسهيلات القبول الجامعي:</b>
                  نساعدك في اختيار تخصصك الأنسب وخدمة متكاملة للتقديم الجامعي
                </p>
              </div>
            </div>

          </div>

        </section>
    <!-- الأسئلة الشائعة -->
        <section class="faq-sec" id="faq">

          <div class="faq-head">
            <h2>أعثر على إجابة سؤالك بسهولة</h2>
          </div>

          <div class="faq-list">

           <details class="faq-item">
  <summary>
    المكتب الاستشاري
    <img src="سهم مدبل.svg">
  </summary>
<div class="faq-ans">

  <div class="faq-q">سؤال 1: كيف أستقبل طلبات القبول؟</div>
  <div class="faq-a"> تظهر طلبات القبول في صفحة "إدارة طلبات القبول"، ويمكنك الدخول على كل طلب لعرض التفاصيل واتخاذ القرار المناسب.</div>

  <div class="faq-q">سؤال 2: كيف أرفع نتيجة القبول؟</div>
  <div class="faq-a"> بعد القبول المبدئي للطلب، يظهر زر "رفع نتيجة التقديم" داخل صفحة تفاصيل الطلب، ومن خلاله يتم رفع ملف القبول.</div>

  <div class="faq-q">سؤال 3: هل يمكن تعديل القرار بعد القبول أو الرفض؟</div>
  <div class="faq-a"> لا، لا يمكن تعديل القرار بعد حفظه، لذلك يجب التأكد قبل اختيار قبول أو رفض الطلب.</div>

  <div class="faq-q">سؤال 4: أين أجد المستندات المرفوعة؟</div>
  <div class="faq-a"> تظهر جميع المستندات داخل صفحة تفاصيل الطلب، ويمكنك فتحها أو تحميلها للتحقق منها قبل اتخاذ القرار.</div>

  <div class="faq-q">سؤال 5: كيف أتابع رسائل واستشارات المستفيدين؟</div>
  <div class="faq-a"> يمكنك الدخول إلى صفحة "الاستشارات" لعرض الرسائل الواردة والرد على المستفيدين، ويتم تمييز الرسائل الجديدة باللون الأخضر.</div>
  <div class="faq-q">سؤال 11: كيف يمكنني إنهاء المنحة إذا واجهت بعض المشاكل؟</div>
  <div class="faq-a"> يمكنك طلب إنهاء منحة من خلال صفحة "تقديم شكوى أو استفسار" مع التأكد من تضمن الطلب رقم العقد المرتبط بالمنحة، مع توضيح المشكلة بشكل واضح و ستتم مراجعة الطلب من الجهة المختصة، واتخاذ الإجراء المناسب وفقًا لشروط العقد والسياسات المعتمدm
  </div>
</div>
</details>
        </section>

      </div>

    </div>

  </div>

</body>
</html>