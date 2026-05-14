<?php
session_start();

/** التحقق من وجود جلسة للمستفيد قبل عرض الصفحة **/
if (!isset($_SESSION['bnf_id'])) {
    header("Location: login.php");
    exit();
}

/* الاتصال بقاعدة البيانات */
$conn = new mysqli("localhost", "root", "", "noreen");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

/* الاسم الافتراضي في حال تعذر جلب بيانات المستفيد */
$userName = 'المستفيد';

/** جلب اسم المستفيد المسجل دخوله من قاعدة البيانات **/
$bnf_id = $_SESSION['bnf_id'];

$stmt = $conn->prepare("
    SELECT f_name
    FROM beneficiary
    WHERE bnf_id = ?
");

/* ربط رقم المستفيد بالاستعلام */
$stmt->bind_param("i", $bnf_id);

/* تنفيذ استعلام جلب الاسم */
$stmt->execute();

/* تحويل النتيجة إلى بيانات قابلة للاستخدام */
$result = $stmt->get_result();

/** حفظ اسم المستفيد لاستخدامه داخل رسالة الترحيب **/
if ($row = $result->fetch_assoc()) {
    $userName = $row['f_name'];
}

/* إغلاق الاستعلام بعد الانتهاء */
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<title>نورين - الرئيسية</title>

<link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS01Layout.css?v=4">
<link rel="stylesheet" href="CSS02Home.css?v=2">

<style>

/* النص الفرعي أسفل عنوان الصفحة */
.page-subtitle{ font-size:16px; color:#6E6E6E; margin-top:6px; font-weight:500; }

</style>
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

                <div class="welcome-box">

                    <!-- عرض اسم المستفيد داخل رسالة الترحيب -->
                    <div class="welcome-text">
                        أهلًا بك، <?php echo htmlspecialchars($userName); ?>
                    </div>

                </div>

            </div>

            <div class="header-icons">

                <div class="settings-dropdown">

                    <img src="ايقونة قائمة الاعدادات.png" class="menu-icon">

                    <div class="dropdown-menu">
                        <a href="Ben02_Profile.php">الملف الشخصي</a>
                        <a href="Ben20_MyScholarshipWallet.php">محفظة منحتي</a>
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

        </div>

    </div>

</div>

</body>
</html>