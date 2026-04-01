-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: 01 أبريل 2026 الساعة 18:00
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `noreen`
--

-- --------------------------------------------------------

--
-- بنية الجدول `academic_report`
--

CREATE TABLE `academic_report` (
  `report_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `report_file` varchar(255) NOT NULL,
  `report_upload` enum('مرفوع','غير مرفوع','','') NOT NULL,
  `submit_date` date NOT NULL,
  `report_appoval` enum('معتمد','غير معتمد','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `password`) VALUES
(1, 'noreenAdmin', 'Admin@123');

-- --------------------------------------------------------

--
-- بنية الجدول `admission_request`
--

CREATE TABLE `admission_request` (
  `request_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `major_name` varchar(100) NOT NULL,
  `univ_name` varchar(100) NOT NULL,
  `Submit_date` date NOT NULL,
  `result_notes` text NOT NULL,
  `Result_status` enum('في انتظار الاصدار','أصدرت') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `admission_request_documents`
--

CREATE TABLE `admission_request_documents` (
  `doc_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `doc_type` enum('CV','High School Certificate','University Degree Certificate','Academic Certificates','Academic Transcript','Language Certificate','Passport','Recommendation Letters','Statement of Purpose','Letter of Intent','Research Proposal','Acceptance Letter','Other Certificates') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `beneficiary`
--

CREATE TABLE `beneficiary` (
  `bnf_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `f_name` varchar(50) NOT NULL,
  `l_name` varchar(50) NOT NULL,
  `phone_num` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `sch_field` enum('تقني و حوسبي','علوم طبيعية','صناعي و تشغيلي','إداري','قانوني','اجتماعي و إنساني','تصميمي','اقتصادي','إعلامي','بيئي','لوجيستي','صحي','لا يوجد') NOT NULL,
  `degree_level` enum('ثانوي','بكالوريوس','ماجستير','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `beneficiary`
--

INSERT INTO `beneficiary` (`bnf_id`, `email`, `f_name`, `l_name`, `phone_num`, `password`, `sch_field`, `degree_level`) VALUES
(1, 'FatmaAlghamdi@gmail.com', 'فاطمة', 'الغامدي', '0502508284', '$2y$10$vS2J608Dv/DImLLpfl9Vu.PcHg0YtAsR2HX8IgX87Lj9Bh1AMhQlS', 'صناعي و تشغيلي', 'بكالوريوس'),
(3, 'Tasneem@gmail.com', 'تسنيم', 'الحربي', '0535246000', '$2y$10$6AxmbjZBFFpd8KP8K.9jWuFUkZ0ifxmunNsiRxq3pzNng6mvo2RhW', 'لا يوجد', 'ثانوي');

-- --------------------------------------------------------

--
-- بنية الجدول `bnf_inv_msg`
--

CREATE TABLE `bnf_inv_msg` (
  `msg_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `inv_id` int(11) NOT NULL,
  `msg_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `msg_text` text NOT NULL,
  `sender_type` enum('beneficiary','investor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bnf_inv_msg`
--

INSERT INTO `bnf_inv_msg` (`msg_id`, `bnf_id`, `inv_id`, `msg_time`, `msg_text`, `sender_type`) VALUES
(1, 1, 1, '2026-04-01 17:57:45', 'السلام عليكم ورحمة الله وبركاته، نبارك لكم قبولكم المبدئي في منحة تطوير الانظمة الصناعية، ويسعدنا التواصل معكم لاستكمال الإجراءات واقتراح  موعد لمقابلة تعريفية لمناقشة التفاصيل والخطوات القادمة. هل يناسبكم يوم الأحد الساعة 10:00 صباحًا؟  شاكرين ومقدّرين تعاونكم.', 'investor'),
(2, 1, 1, '2026-04-01 18:13:26', 'وعليكم السلام ورحمة الله وبركاته، أسعد الله مساءكم بكل خير، وشكرًا لكم على قبولكم وثقتكم. نعم، الموعد المقترح يوم الأحد الساعة 10:00 صباحًا مناسب لي، كما أتطلع لمعرفة تفاصيل المقابلة والآلية المتبعة وتفضلوا بقبول فائق الاحترام', 'beneficiary');

-- --------------------------------------------------------

--
-- بنية الجدول `bnf_off_msg`
--

CREATE TABLE `bnf_off_msg` (
  `msg_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `msg_time` datetime NOT NULL DEFAULT current_timestamp(),
  `msg_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `complaints_inquiries`
--

CREATE TABLE `complaints_inquiries` (
  `ticket_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `bnf_id` int(11) DEFAULT NULL,
  `inv_id` int(11) DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `submission_date` datetime DEFAULT current_timestamp(),
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `status` enum('بانتظار الرد','تم الرد عليها') DEFAULT 'بانتظار الرد'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `complaints_inquiries`
--

INSERT INTO `complaints_inquiries` (`ticket_id`, `office_id`, `bnf_id`, `inv_id`, `admin_reply`, `submission_date`, `subject`, `message`, `status`) VALUES
(31, NULL, 1, NULL, NULL, '2026-03-17 12:33:09', 'مشكله', 'ممممم', 'بانتظار الرد'),
(32, NULL, 1, NULL, NULL, '2026-03-17 12:35:12', 'مشكله', '...', 'بانتظار الرد'),
(33, NULL, 3, NULL, NULL, '2026-03-18 06:04:25', 'اواجه مشكلة في رفع الملفات', 'لا استطيع رفع الملفات عبر خانة التقديم على فرص المنح المعروضة', 'بانتظار الرد'),
(34, 2, NULL, NULL, NULL, '2026-03-18 06:15:15', 'الطلبات الجديدة تظهر بدون اسم', 'الطلبات المقدمة من المستفيدين تظهر بدون اسم المستخدم\r\nما السبب؟ وما الحل؟', 'بانتظار الرد'),
(35, NULL, NULL, 1, NULL, '2026-03-18 06:20:11', 'ارغب في انهاء منحة', 'ارغب في انهاء منحة احد الطلاب بسبب بعض المشاكل، هل يمكنني ذلك ؟ وماهي البيانات المطلوبة؟', 'بانتظار الرد');

-- --------------------------------------------------------

--
-- بنية الجدول `consulting_office`
--

CREATE TABLE `consulting_office` (
  `office_id` int(11) NOT NULL,
  `ccr_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `office_name` varchar(255) NOT NULL,
  `office_description` text NOT NULL,
  `Bachelor_fee` int(11) NOT NULL,
  `Masters_fee` int(11) NOT NULL,
  `Phd_fee` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `consulting_office`
--

INSERT INTO `consulting_office` (`office_id`, `ccr_number`, `email`, `office_name`, `office_description`, `Bachelor_fee`, `Masters_fee`, `Phd_fee`, `password`, `phone`) VALUES
(2, ' 101989710', 'info@asasstudyabroad.com', 'أساس للدراسة بالخارج', 'مكتب رائد في مجال الاستشارات التعليمية والقبول الجامعي، نخدم الطلاب في أكثر من 15 دولة حول العالم، مستشارينا معتمدين من التعليم البريطاني، والتعليم الكندي والتعليم الامريكي ومنظمة ICEF ومؤهلين لتقديم النصيحة والاستشارة المناسبة لكل طالب ونسعى لتقديم النصيحة الأمينة حسب متطلبات الجامعات  ومؤهلات الطلب لضمان انسب خيارات الدراسة لكل طالب.\r\nوكلاء افضل الجامعات والمعاهد في أمريكا، بريطانيا، استراليا، نيوزيلندا، أيرلندا، كندا، ماليزيا، اسبانيا، ايطاليا، فرنسا، الهند، مالطا، جنوب افريقيا، تركيا، وغيرها من الدول، تقديم سهل وسريع على المعاهد والجامعات لضمان توفير القبول المناسب بأسرع وقت ممكن.', 300, 200, 600, '$2y$10$Nb3JPrsRVArQ8nZ1saNTYux42WPRTBnIJPg5kMEEDzR706mx98pVu', '0541722808'),
(3, '1029836153', 'gecs.edu.@outlook.com', 'مكتب الخليج', 'مكتب متخصص في الاستشارات الأكاديمية ومساعدة الطلاب في إجراءات القبول الجامعي نقدّم خدمات تقييم المؤهلات الأكاديمية، ترشيح الجامعات والتخصصات المناسبة، وإرسال طلبات القبول إلى الجامعات\r\nكما نتولى متابعة حالة الطلب حتى صدور قرار القبول، مع تقديم إرشادات حول اختيار الدولة والبرنامج الدراسي الأنسب للطالب', 150, 200, 300, '$2y$10$ha7iXPVAeiRjM0bpPDo5GufT.cvp2awdRfogP0z/bIojRWUy0d4nu', '0549778902'),
(4, '4030257841', ' info@almasarconsult.com', 'المسار الدولي', 'مكتبنا يقدم خدمات الإرشاد الأكاديمي للطلاب الراغبين في الدراسة في الجامعات الآسيوية والعربية ويتضمن في مساعدة الطالب لاختيار الدولة والبرنامج الدراسي المناسب، توضيح متطلبات القبول في الجامعات، وترتيب خطوات التقديم بشكل منظم، إضافة إلى متابعة الطلبات مع الجهات التعليمية حتى استكمال إجراءات القبول.', 200, 300, 500, '$2y$10$cLcV5hj8mNTQ9QFyMZ1ZU.g4kLS02k5VXBwn9LV4vKcbhrJxQ1KS2', '0551234567');

-- --------------------------------------------------------

--
-- بنية الجدول `e_contract`
--

CREATE TABLE `e_contract` (
  `contract_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `payments_count` int(11) NOT NULL,
  `funding_duration` int(11) NOT NULL,
  `ctr_status` enum('نشط','ملغي') NOT NULL,
  `terms` text NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `e_contract`
--

INSERT INTO `e_contract` (`contract_id`, `request_id`, `payments_count`, `funding_duration`, `ctr_status`, `terms`, `amount`) VALUES
(1, 2, 2, 1, 'نشط', 'أولاً: التوظيف بعد التخرج\r\nيلتزم المستفيد بعد إتمام البرنامج الأكاديمي بنجاح بالعمل لدى سابك او إحدى الشركات التابعة لها لمدة لا تقل عن سنتين وذلك وفق عقد عمل مستقل يُبرم بعد التخرج، و في حال امتناع المستفيد عن الالتحاق بالوظيفة دون سبب مشروع تقبله الجهة الممولة، يحق للمستثمر المطالبة باسترداد كامل مبالغ التمويل المصروفة.\r\n\r\nثانياً: باستمرارية الدراسة  2) يلتزم المستفيد بالاستمرار في الدراسة حتى إتمام البرنامج المحدد في هذا العقد، وعدم الانسحاب أو تأجيل الدراسة إلا بموافقة مسبقة.  3) في حال الانسحاب أو الفصل الأكاديمي بسبب تقصير من المستفيد، يحق للمستثمر إيقاف التمويل فورًا والمطالبة باسترداد المبالغ المصروفة.\r\n\r\nثالثاً: استخدام أموال المنحة  4) يقر المستفيد بأن جميع المبالغ المصروفة بموجب هذا العقد مخصصة فقط لتغطية الرسوم الدراسية والمصاريف التعليمية المرتبطة بالبرنامج المحدد، وفي حال ثبوت استخدام أي جزء من المبالغ لأغراض غير تعليمية أو تقديم مستندات غير صحيحة، يعد ذلك إخلالًا فوريًا بالعقد ويعرض المستفيد للمساءلة القانونية.', 147540.00);

-- --------------------------------------------------------

--
-- بنية الجدول `investor`
--

CREATE TABLE `investor` (
  `inv_id` int(11) NOT NULL,
  `ccr_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `inv_number` int(255) NOT NULL,
  `inv_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `investor`
--

INSERT INTO `investor` (`inv_id`, `ccr_number`, `email`, `inv_number`, `inv_name`, `password`) VALUES
(1, '1010010813', 'InvestorRelations@safco.sabic.com', 530014051, 'سابك - الشركة السعودية للصناعات الأساسية', '$2y$10$/8anVADcwa6r5K6nJk/RCO9Tb6fS7G05m9eUS4FIXm90T2iaJ7c4a'),
(2, '4030175741', 'Baggage.Inquiries@Saudia.com', 2147483647, 'الخطوط السعودية', '$2y$10$46r8y.xFY5El.dVX2RJuN.2ijifWQAc8tZgH6GxcpN.vi5D5hJPby'),
(3, '2052101150', 'investor@aramco.com', 567788990, 'أرامكو', '$2y$10$IGfO7u71zWhhE/n3JrK72.0Wh2ZOM9PyPNd.3vGYwGuYarq8SCIT.');

-- --------------------------------------------------------

--
-- بنية الجدول `office_country`
--

CREATE TABLE `office_country` (
  `office_id` int(11) NOT NULL,
  `con_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `office_country`
--

INSERT INTO `office_country` (`office_id`, `con_name`) VALUES
(2, 'استراليا'),
(2, 'الصين'),
(2, 'المانيا'),
(2, 'الهند'),
(2, 'اليابان'),
(2, 'امريكا'),
(2, 'ايرلندا'),
(2, 'بريطانيا'),
(2, 'تركيا'),
(2, 'جنوب افريقيا'),
(2, 'فرنسا'),
(2, 'كندا'),
(2, 'مالطا'),
(2, 'ماليزيا'),
(2, 'نيوزلندا'),
(3, 'استراليا'),
(3, 'امريكا'),
(3, 'بريطانيا'),
(3, 'سويسرا'),
(3, 'فنلندا'),
(3, 'نيوزلندا'),
(4, 'الاردن'),
(4, 'الامارات'),
(4, 'الفلبين'),
(4, 'الكويت'),
(4, 'اندونيسيا'),
(4, 'تايلاند'),
(4, 'روسيا'),
(4, 'سنغافورة'),
(4, 'فيتنام'),
(4, 'قطر'),
(4, 'كوريا الجنوبية'),
(4, 'مصر');

-- --------------------------------------------------------

--
-- بنية الجدول `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `payment_amount` int(11) NOT NULL,
  `payment_status` enum('تم الدفع','بانتظار الدفع','','') NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `rating_date` datetime DEFAULT current_timestamp(),
  `comment_text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_opps`
--

CREATE TABLE `scholarship_opps` (
  `scholarship_id` int(11) NOT NULL,
  `sch_field` enum('تقني وحوسبي','علوم طبيعية','صناعي وتشغيلي','ادراي','قانوني','اجتماعي وانساني','تصميمي','اقتصادي','إعلامي','بيئي','لوجيستي','صحي') NOT NULL,
  `inv_id` int(11) NOT NULL,
  `sch_name` varchar(100) NOT NULL,
  `requirements` text NOT NULL,
  `study_level` enum('بكالوريوس','ماجستير','دكتوراه') NOT NULL,
  `Specializations` text NOT NULL,
  `app_deadline` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `scholarship_opps`
--

INSERT INTO `scholarship_opps` (`scholarship_id`, `sch_field`, `inv_id`, `sch_name`, `requirements`, `study_level`, `Specializations`, `app_deadline`) VALUES
(1, 'صناعي وتشغيلي', 1, 'برنامج سابك - تطوير الأنظمة الصناعية المتقدمة', 'أن يكون المتقدم سعودي الجنسية.\r\nحاصل على بكالوريوس في الهندسة الصناعية بمعدل لا يقل عن 3.5 من 4.\r\nخبرة عملية لا تقل عن سنة في مجال ذات صلة (للماجستير).\r\nالالتزام بالعمل في سابك بعد التخرج لمدة محددة.', 'بكالوريوس', 'تحسين العمليات الصناعية، سلاسل الامداد، نظم الدعم الشاملة', '2026-04-15 00:00:00');

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_requests`
--

CREATE TABLE `scholarship_requests` (
  `request_id` int(11) NOT NULL,
  `scholarship_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `Submit_date` date NOT NULL,
  `request_status` enum('مقبول','مرفوض','في انتظار المراجعة','') NOT NULL DEFAULT 'في انتظار المراجعة',
  `major_name` varchar(100) NOT NULL,
  `univ_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `scholarship_requests`
--

INSERT INTO `scholarship_requests` (`request_id`, `scholarship_id`, `bnf_id`, `Submit_date`, `request_status`, `major_name`, `univ_name`) VALUES
(2, 1, 1, '2026-03-25', 'مقبول', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة');

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_request_documents`
--

CREATE TABLE `scholarship_request_documents` (
  `doc_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `doc_type` varchar(150) NOT NULL,
  `file_name` varchar(150) NOT NULL,
  `file` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `scholarship_request_documents`
--

INSERT INTO `scholarship_request_documents` (`doc_id`, `request_id`, `doc_type`, `file_name`, `file`) VALUES
(1, 2, 'CV', '1774446178_Fatimah_Alhammadi_CV.pdf', '1774446178_Fatimah_Alhammadi_CV.pdf'),
(2, 2, 'Certificate', '1774446178_DegreeCertificate.pdf', '1774446178_DegreeCertificate.pdf'),
(3, 2, 'Recommendation', '1774446178_Fatimah_Alhammadi_Recommendation.pdf', '1774446178_Fatimah_Alhammadi_Recommendation.pdf'),
(4, 2, 'Acceptance', '1774446178_AcceptanceLetter.pdf', '1774446178_AcceptanceLetter.pdf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_report`
--
ALTER TABLE `academic_report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `bnf_id` (`bnf_id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_name` (`admin_name`);

--
-- Indexes for table `admission_request`
--
ALTER TABLE `admission_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `bnf_id` (`bnf_id`);

--
-- Indexes for table `admission_request_documents`
--
ALTER TABLE `admission_request_documents`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `beneficiary`
--
ALTER TABLE `beneficiary`
  ADD PRIMARY KEY (`bnf_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bnf_inv_msg`
--
ALTER TABLE `bnf_inv_msg`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `bnf_id` (`bnf_id`),
  ADD KEY `inv_id` (`inv_id`);

--
-- Indexes for table `bnf_off_msg`
--
ALTER TABLE `bnf_off_msg`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `bnf_id` (`bnf_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `complaints_inquiries`
--
ALTER TABLE `complaints_inquiries`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `bnf_id` (`bnf_id`),
  ADD KEY `inv_id` (`inv_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `consulting_office`
--
ALTER TABLE `consulting_office`
  ADD PRIMARY KEY (`office_id`),
  ADD UNIQUE KEY `ccr_number` (`ccr_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `e_contract`
--
ALTER TABLE `e_contract`
  ADD PRIMARY KEY (`contract_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `investor`
--
ALTER TABLE `investor`
  ADD PRIMARY KEY (`inv_id`),
  ADD UNIQUE KEY `ccr_number` (`ccr_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `office_country`
--
ALTER TABLE `office_country`
  ADD PRIMARY KEY (`office_id`,`con_name`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `bnf_id` (`bnf_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `scholarship_opps`
--
ALTER TABLE `scholarship_opps`
  ADD PRIMARY KEY (`scholarship_id`),
  ADD UNIQUE KEY `scholarship_id` (`scholarship_id`),
  ADD KEY `field_filter` (`sch_field`),
  ADD KEY `inv_id` (`inv_id`);

--
-- Indexes for table `scholarship_requests`
--
ALTER TABLE `scholarship_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `scholarship_id` (`scholarship_id`),
  ADD KEY `bnf_id` (`bnf_id`);

--
-- Indexes for table `scholarship_request_documents`
--
ALTER TABLE `scholarship_request_documents`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `request_id` (`request_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_report`
--
ALTER TABLE `academic_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admission_request`
--
ALTER TABLE `admission_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admission_request_documents`
--
ALTER TABLE `admission_request_documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `beneficiary`
--
ALTER TABLE `beneficiary`
  MODIFY `bnf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bnf_inv_msg`
--
ALTER TABLE `bnf_inv_msg`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bnf_off_msg`
--
ALTER TABLE `bnf_off_msg`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints_inquiries`
--
ALTER TABLE `complaints_inquiries`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `consulting_office`
--
ALTER TABLE `consulting_office`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `e_contract`
--
ALTER TABLE `e_contract`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `investor`
--
ALTER TABLE `investor`
  MODIFY `inv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scholarship_opps`
--
ALTER TABLE `scholarship_opps`
  MODIFY `scholarship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scholarship_requests`
--
ALTER TABLE `scholarship_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scholarship_request_documents`
--
ALTER TABLE `scholarship_request_documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `academic_report`
--
ALTER TABLE `academic_report`
  ADD CONSTRAINT `academic_report_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `academic_report_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `e_contract` (`contract_id`),
  ADD CONSTRAINT `academic_report_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`);

--
-- قيود الجداول `admission_request`
--
ALTER TABLE `admission_request`
  ADD CONSTRAINT `admission_request_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `admission_request_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`);

--
-- قيود الجداول `admission_request_documents`
--
ALTER TABLE `admission_request_documents`
  ADD CONSTRAINT `admission_request_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `admission_request` (`request_id`);

--
-- قيود الجداول `bnf_inv_msg`
--
ALTER TABLE `bnf_inv_msg`
  ADD CONSTRAINT `bnf_inv_msg_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `bnf_inv_msg_ibfk_2` FOREIGN KEY (`inv_id`) REFERENCES `investor` (`inv_id`);

--
-- قيود الجداول `bnf_off_msg`
--
ALTER TABLE `bnf_off_msg`
  ADD CONSTRAINT `bnf_off_msg_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `bnf_off_msg_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`);

--
-- قيود الجداول `complaints_inquiries`
--
ALTER TABLE `complaints_inquiries`
  ADD CONSTRAINT `complaints_inquiries_ibfk_2` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `complaints_inquiries_ibfk_3` FOREIGN KEY (`inv_id`) REFERENCES `investor` (`inv_id`),
  ADD CONSTRAINT `complaints_inquiries_ibfk_4` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`);

--
-- قيود الجداول `e_contract`
--
ALTER TABLE `e_contract`
  ADD CONSTRAINT `e_contract_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `scholarship_requests` (`request_id`);

--
-- قيود الجداول `office_country`
--
ALTER TABLE `office_country`
  ADD CONSTRAINT `office_country_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`);

--
-- قيود الجداول `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `e_contract` (`contract_id`);

--
-- قيود الجداول `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`);

--
-- قيود الجداول `scholarship_opps`
--
ALTER TABLE `scholarship_opps`
  ADD CONSTRAINT `scholarship_opps_ibfk_1` FOREIGN KEY (`inv_id`) REFERENCES `investor` (`inv_id`);

--
-- قيود الجداول `scholarship_requests`
--
ALTER TABLE `scholarship_requests`
  ADD CONSTRAINT `scholarship_requests_ibfk_1` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarship_opps` (`scholarship_id`),
  ADD CONSTRAINT `scholarship_requests_ibfk_2` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`);

--
-- قيود الجداول `scholarship_request_documents`
--
ALTER TABLE `scholarship_request_documents`
  ADD CONSTRAINT `scholarship_request_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `scholarship_requests` (`request_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
