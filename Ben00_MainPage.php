<?php
session_start();
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<meta charset="UTF-8">

<head>
  <title>نورين - الرئيسية</title>

  <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="CSS01Layout.css?v=3">  <link rel="stylesheet" href="CSS02Home.css">
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
          <li><a href="Ben00_MainPage.php" class="active">الرئيسية</a></li>
          <li><a href="Ben04_BrowseScholarships.php">التقديم على المنح</a></li>
          <li><a href="Ben09_TrackScholarship.php">متابعة المنح</a></li>
          <li><a href="Ben013_ConsultingOffices.php">المكاتب الاستشارية</a></li>
          <li><a href="Ben16_AdmissionList.php">طلبات إصدار القبول</a></li>
          <li><a href="Ben19_Consultations.php">الاستشارات</a></li>
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

    <!-- المحتوى الرئيسي -->
    <div class="main-content">

      <!-- الهيدر -->
      <header class="header">

        <div class="page-heading">
          <div class="page-title">الرئيسية</div>
        </div>

        <div class="header-icons">
          <div class="settings-dropdown">
            <img src="ايقونة قائمة الاعدادات.png" class="menu-icon">

            <div class="dropdown-menu">
              <a href="Ben02_Profile.php">الملف الشخصي</a>
              <a href="Ben03_MyScholarshipWallet.php">محفظة منحتي</a>
              <a href="support.php">تقديم شكوى او استفسار</a>
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
                نورين منظومة رقمية تمكّن الطلاب المتميزين من الوصول إلى فرص تعليمية مدعومة ضمن بيئة موثوقة ومميزة
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
                نورين منصة إلكترونية تهدف إلى ربط الطلاب بالمستثمرين
                والمكاتب الاستشارية لتسهيل الحصول على المنح الدراسية.
              </p>
            </div>

          </div>

          <div class="ab-row">
            <div class="ab-info">
              <h3>رؤيتنا</h3>
              <p>
                أن تكون نورين المنصة الرائدة في دعم وتمكين الطلاب
                للحصول على فرص تعليمية من خلال بيئة رقمية متكاملة
              </p>
            </div>

            <div class="ab-info">
              <h3>رسالتنا</h3>
              <p>
                توفير منصة موثوقة تجمع بين الطلاب والمستثمرين
                والمكاتب الاستشارية لإدارة المنح الدراسية بكفاءة
              </p>
            </div>
          </div>

          <div class="ab-title">
            من يخدم نورين؟
          </div>

          <div class="ab-btns">
            <button>الطلاب</button>
            <button>المستثمرين</button>
            <button>المكاتب الاستشارية</button>
          </div>

        </section>

        <!-- خدماتنا -->
        <section class="serv-sec" id="services">

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
                  منصة رقمية شاملة لمتابعة أداء الطلاب المبتعثين وضمان جودة مخرجاتهم التعليمية
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
                المستفيد
                <img src="سهم مدبل.svg">
              </summary>
              <div class="faq-ans">
                سؤال 1: اكتب السؤال هنا<br>
                الإجابة: اكتب الإجابة هنا
                <br><br>
                سؤال 2: اكتب السؤال هنا<br>
                الإجابة: اكتب الإجابة هنا
              </div>
            </details>

            <details class="faq-item">
              <summary>
                المستثمر
                <img src="سهم مدبل.svg">
              </summary>
              <div class="faq-ans">
                سؤال 1: اكتب السؤال هنا<br>
                الإجابة: اكتب الإجابة هنا
                <br><br>
                سؤال 2: اكتب السؤال هنا<br>
                الإجابة: اكتب الإجابة هنا
              </div>
            </details>

            <details class="faq-item">
              <summary>
                المكتب الاستشاري
                <img src="سهم مدبل.svg">
              </summary>
              <div class="faq-ans">
                سؤال 1: اكتب السؤال هنا<br>
                الإجابة: اكتب الإجابة هنا
                <br><br>
                سؤال 2: اكتب السؤال هنا<br>
                الإجابة: اكتب الإجابة هنا
              </div>
            </details>

          </div>

        </section>

      </div>

    </div>

  </div>

</body>
</html>