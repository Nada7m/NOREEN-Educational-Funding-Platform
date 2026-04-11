<!DOCTYPE html>
<html lang="ar" dir="rtl">
<meta charset="UTF-8">

<head>
  <title>نورين - الرئيسية</title>

  <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="CSS01Layout.css?v=4">  <link rel="stylesheet" href="CSS02Home.css?v=4">
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
          <li><a href="Inv00_MainPage.php" class="active">الرئيسية</a></li>
          <li><a href="Inv04_CreateScholarship.php">عرض المنح</a></li>
          <li><a href="Inv06_ManageScholarships.php">إدارة المنح</a></li>
<li><a href="Inv10_Payments.php">المدفوعات</a></li>
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
      <img src="ايقونة قائمة الاعدادات.png" class="menu-icon" alt="الإعدادات">

      <div class="dropdown-menu">
        <a href="Inv02_Profile.php">الملف الشخصي</a>
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
    المستثمر
    <img src="سهم مدبل.svg">
  </summary>

  <div class="faq-ans">

    <div class="faq-q">سؤال 1: كيف يمكنني تقديم منحة و دعم المستفيدين، من خلال المنصة؟</div>
<div class="faq-a"> يمكنك تقديم منحة من خلال الدخول إلى صفحة "عرض المنح"، ثم إنشاء منحة جديدة وتحديد بياناتها، وبعد نشرها يستطيع المستفيدين التقديم عليها، ويمكنك مراجعة الطلبات واختيار المستفيد المناسب.</div>
    <div class="faq-q">سؤال 2: متى يتم توقيع العقد مع المستفيد؟</div>
    <div class="faq-a"> يتم توقيع العقد الإلكتروني بعد القبول المبدئي والتواصل لإجراء المقابلة، ويجب على المستفيد الموافقة على الشروط قبل بدء الدعم.</div>

    <div class="faq-q">سؤال 3: كيف تتم متابعة المستفيد بعد القبول؟</div>
    <div class="faq-a"> يمكنك متابعة المستفيد من خلال صفحة إدارة المنح، حيث يتم عرض بيانات التواصل والتقارير الأكاديمية التي يرفعها المستفيد خلال فترة الدراسة.</div>

    <div class="faq-q">سؤال 4: كيف تتم عملية الدفع؟</div>
    <div class="faq-a"> يتم الدفع على شكل دفعات، ويتم صرف كل دفعة بعد رفع المستفيد للتقارير الأكاديمية المطلوبة والتحقق منها.</div>

    <div class="faq-q">سؤال 5: هل يمكنني إلغاء الدعم بعد الموافقة؟</div>
    <div class="faq-a"> نعم، يمكن ذلك عن طريق رفع شكوى عبر النظام مع توضيح الأسباب، ويتم مراجعتها واتخاذ الإجراء المناسب وفقًا لسياسات العقد،.</div>

    <div class="faq-q">سؤال 6: كيف أتواصل مع المستفيد؟</div>
    <div class="faq-a"> يمكنك التواصل مع المستفيد من خلال نظام الرسائل داخل المنصة بعد القبول المبدئي، حيث يتم فتح قناة تواصل مباشرة بين الطرفين.</div>

     <div class="faq-q">سؤال 11: كيف يمكنني إنهاء المنحة إذا واجهت بعض المشاكل؟</div>
     <div class="faq-a">  يمكنك طلب إنهاء منحة من خلال صفحة "تقديم شكوى أو استفسار" مع التأكد من تضمن الطلب رقم العقد المرتبط بالمنحة، مع توضيح المشكلة بشكل واضح و ستتم مراجعة الطلب من الجهة المختصة، واتخاذ الإجراء المناسب وفقًا لشروط العقد والسياسات المعتمدة.</div>
  </div>
</details>

      </div>

    </div>

  </div>

</body>
</html>