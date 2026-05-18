-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 18 مايو 2026 الساعة 20:19
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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

--
-- إرجاع أو استيراد بيانات الجدول `academic_report`
--

INSERT INTO `academic_report` (`report_id`, `bnf_id`, `contract_id`, `payment_id`, `report_file`, `report_upload`, `submit_date`, `report_appoval`) VALUES
(1, 1, 1, 1, 'uploads/Fatimah_Report.pdf', 'مرفوع', '2026-04-05', 'معتمد');

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
(1, 'noreenAdmin', '$2y$12$QiwXOpLzO08yFHv9kyLsEOcrUCgpUJ0yOVfjVshq706TO9M3p.NtG');

-- --------------------------------------------------------

--
-- بنية الجدول `admission_request`
--

CREATE TABLE `admission_request` (
  `request_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `program_type` enum('bachelor','master','phd') NOT NULL,
  `major_name` varchar(100) NOT NULL,
  `univ_name` varchar(100) NOT NULL,
  `Submit_date` date NOT NULL,
  `result_notes` text DEFAULT NULL,
  `Result_status` enum('قيد المعالجة','أُصدرت','لم تُصدر') DEFAULT 'قيد المعالجة',
  `request_status` enum('في الانتظار','مقبول','مرفوض') NOT NULL DEFAULT 'في الانتظار',
  `payment_status` enum('غير مدفوع','مدفوع','بانتظار الدفع') NOT NULL DEFAULT 'غير مدفوع',
  `result` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admission_request`
--

INSERT INTO `admission_request` (`request_id`, `bnf_id`, `office_id`, `program_type`, `major_name`, `univ_name`, `Submit_date`, `result_notes`, `Result_status`, `request_status`, `payment_status`, `result`) VALUES
(1, 1, 2, 'master', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة', '2026-04-02', 'تم رفع خطاب القبول الرسمي أدناه يرجى مراجعة الملف المرفق والاطلاع على الشروط والأحكام الأكاديمية والمالية قبل اتخاذ أي إجراء لاحق.', '', 'مقبول', 'مدفوع', 'uploads/admission_results/1_result.pdf'),
(3, 3, 3, 'bachelor', 'هندسة الطيران', 'جامعة إمبري ريدل للطيران', '2026-04-06', 'مبارك', '', 'مقبول', 'مدفوع', 'uploads/admission_results/3_result.pdf'),
(7, 8, 2, 'phd', 'دكتوراه في العلوم السريرية', 'جامعة كينجز كوليدج لندن', '2026-05-03', '', 'لم تُصدر', 'مرفوض', 'غير مدفوع', NULL),
(8, 8, 5, 'phd', 'دكتوراه في العلوم السريرية', 'جامعة كينجز كوليدج لندن', '2026-05-03', 'تم إصدار خطاب القبول المبدئي للطالبة بعد مراجعة جميع مستنداتها واستيفائها لشروط البرنامج. القبول صادر من جامعة معتمدة ضمن التخصص الطبي المطلوب، ويمكن للطالبة استكمال الإجراءات النهائية والتواصل مع الجهة التعليمية لإتمام التسجيل', 'أُصدرت', 'مقبول', 'مدفوع', 'uploads/admission_results/8_result.pdf');

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

--
-- إرجاع أو استيراد بيانات الجدول `admission_request_documents`
--

INSERT INTO `admission_request_documents` (`doc_id`, `request_id`, `doc_type`, `file_name`, `file`) VALUES
(1, 1, 'CV', 'Fatima_Alghamdi_CV.pdf', 'uploads/admission_requests/1_cv_file.pdf'),
(2, 1, 'Passport', 'Fatima_Alghamdi_Passport.pdf', 'uploads/admission_requests/1_passport_file.pdf'),
(3, 1, 'Language Certificate', 'Fatima_Alghamdi_Language_Certificate.pdf', 'uploads/admission_requests/1_language_file.pdf'),
(4, 1, 'Recommendation Letters', 'Fatima_Alghamdi_Recommendation_Letters.pdf', 'uploads/admission_requests/1_recommendation_file.pdf'),
(5, 1, 'Other Certificates', 'Fatima_Alghamdi_Other_Certificates.pdf.pdf', 'uploads/admission_requests/1_other_file.pdf'),
(6, 1, 'University Degree Certificate', 'Fatima_Alghamdi_University_Degree.pdf', 'uploads/admission_requests/1_degree_file.pdf'),
(7, 1, 'Academic Transcript', 'Fatima_Alghamdi_Transcript.pdf', 'uploads/admission_requests/1_transcript_file.pdf'),
(8, 1, 'Statement of Purpose', 'Fatima_Alghamdi_SOP.pdf', 'uploads/admission_requests/1_sop_file.pdf'),
(15, 3, 'CV', 'Tasneem_Alharbi_CV.pdf.pdf', 'uploads/admission_requests/3_cv_file.pdf'),
(16, 3, 'Passport', 'Passport_Tasneem.pdf', 'uploads/admission_requests/3_passport_file.pdf'),
(17, 3, 'Language Certificate', 'IELTS_Tasneem.pdf.pdf', 'uploads/admission_requests/3_language_file.pdf'),
(18, 3, 'Recommendation Letters', 'SOP_Tasneem.pdf', 'uploads/admission_requests/3_recommendation_file.pdf'),
(19, 3, 'High School Certificate', 'HS_Certificate_Tasneem.pdf.pdf', 'uploads/admission_requests/3_highschool_file.pdf'),
(20, 3, 'Letter of Intent', 'SOP_Tasneem.pdf', 'uploads/admission_requests/3_intent_file.pdf'),
(41, 7, 'CV', 'reemCV.pdf', 'uploads/admission_requests/7_cv_file.pdf'),
(42, 7, 'Passport', 'ReemAlshammari_Passport..pdf', 'uploads/admission_requests/7_passport_file.pdf'),
(43, 7, 'Language Certificate', 'Reem_IELTS.pdf', 'uploads/admission_requests/7_language_file.pdf'),
(44, 7, 'Recommendation Letters', 'Reem_Recond.pdf', 'uploads/admission_requests/7_recommendation_file.pdf'),
(45, 7, 'Academic Certificates', 'Reem_Academic Transcript.pdf', 'uploads/admission_requests/7_academic_file.pdf'),
(46, 7, 'Research Proposal', 'Research Proposal.pdf', 'uploads/admission_requests/7_research_file.pdf'),
(47, 7, 'Statement of Purpose', 'PersonalStatement.pdf', 'uploads/admission_requests/7_sop_file.pdf'),
(48, 8, 'CV', 'reemCV.pdf', 'uploads/admission_requests/8_cv_file.pdf'),
(49, 8, 'Passport', 'ReemAlshammari_Passport..pdf', 'uploads/admission_requests/8_passport_file.pdf'),
(50, 8, 'Language Certificate', 'Reem_IELTS.pdf', 'uploads/admission_requests/8_language_file.pdf'),
(51, 8, 'Recommendation Letters', 'Reem_Recond.pdf', 'uploads/admission_requests/8_recommendation_file.pdf'),
(52, 8, 'Academic Certificates', 'Reem_Academic Transcript.pdf', 'uploads/admission_requests/8_academic_file.pdf'),
(53, 8, 'Research Proposal', 'Research Proposal.pdf', 'uploads/admission_requests/8_research_file.pdf'),
(54, 8, 'Statement of Purpose', 'PersonalStatement.pdf', 'uploads/admission_requests/8_sop_file.pdf');

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
  `degree_level` enum('ثانوي','بكالوريوس','ماجستير','') NOT NULL,
  `account_status` enum('نشط','محظور') NOT NULL DEFAULT 'نشط',
  `sch_field` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `beneficiary`
--

INSERT INTO `beneficiary` (`bnf_id`, `email`, `f_name`, `l_name`, `phone_num`, `password`, `degree_level`, `account_status`, `sch_field`) VALUES
(1, 'FatmaAlghamdi@gmail.com', 'فاطمة', 'الغامدي', '0502508284', '$2y$10$vS2J608Dv/DImLLpfl9Vu.PcHg0YtAsR2HX8IgX87Lj9Bh1AMhQlS', 'بكالوريوس', 'نشط', ''),
(3, 'Tasneem@gmail.com', 'تسنيم', 'الحربي', '0535246000', '$2y$10$6AxmbjZBFFpd8KP8K.9jWuFUkZ0ifxmunNsiRxq3pzNng6mvo2RhW', 'ماجستير', 'نشط', 'تصميمي'),
(4, 'noorAlfif99@gmail.com', 'نور', 'الفيفي', '0559800107', '$2y$10$7Ym4vCgxDdZbi7ELxvoDReX0CbOud1XYHol8HNeJ0/UdogqE3Vv/W', 'ثانوي', 'نشط', ''),
(8, 'reem.alshammari@gmail.com', 'ريم', 'الشمري', '0557362198', '$2y$10$lK6fu9/DfA1VxDahrdRpOOe4x5QLn68YwgvCOILUixcKdVt2l.Ms.', 'ماجستير', 'نشط', 'صحي');

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
(2, 1, 1, '2026-04-01 18:13:26', 'وعليكم السلام ورحمة الله وبركاته، أسعد الله مساءكم بكل خير، وشكرًا لكم على قبولكم وثقتكم. نعم، الموعد المقترح يوم الأحد الساعة 10:00 صباحًا مناسب لي، كما أتطلع لمعرفة تفاصيل المقابلة والآلية المتبعة وتفضلوا بقبول فائق الاحترام', 'beneficiary'),
(5, 8, 4, '2026-05-02 02:40:42', 'المستشفى: السلام عليكم، نشكر اهتمامك ببرنامج المنح الطبية لدينا ونبارك لك ترشيحك المبدئي بعد مراجعة طلبك ومستنداتك، نود دعوتك لإجراء مقابلة شخصية كجزء من إجراءات التقييم نود التنويه بأن تفاصيل العقد، وكذلك أي أمور تتعلق بالجامعة والبرنامج، سيتم مناقشتها خلال المقابلة لذا يرجى الاستعداد لذلك، يرجى تزويدنا بموعد مناسب لك خلال الأيام القادمة..', 'investor'),
(6, 8, 4, '2026-05-02 02:40:53', 'وعليكم السلام، شكرًا لكم على إتاحة هذه الفرصة و أؤكد استعدادي لإجراء المقابلة، وأفضل أن تكون خلال هذا الأسبوع في الفترة المسائية إن أمكن', 'beneficiary'),
(7, 8, 4, '2026-05-02 02:41:07', 'شكرًا لتأكيدك. تم تحديد موعد المقابلة يوم الثلاثاء الساعة 7:00 مساءً عبر منصة  Zoom، وسيتم إرسال رابط الاجتماع قبل الموعد نأمل منك التواجد قبل الموعد بخمس دقائق', 'investor'),
(8, 8, 4, '2026-05-02 02:41:17', 'تم، شكرًا لكم سأكون متواجدة في الوقت المحدد بإذن الله.', 'beneficiary');

-- --------------------------------------------------------

--
-- بنية الجدول `bnf_off_msg`
--

CREATE TABLE `bnf_off_msg` (
  `msg_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `msg_time` datetime NOT NULL DEFAULT current_timestamp(),
  `msg_text` text NOT NULL,
  `sender_type` enum('beneficiary','office') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bnf_off_msg`
--

INSERT INTO `bnf_off_msg` (`msg_id`, `bnf_id`, `office_id`, `msg_time`, `msg_text`, `sender_type`) VALUES
(1, 1, 2, '2026-04-04 15:42:12', 'السلام عليكم ورحمة الله وبركاته.  أنا فاطمة الغامدي، أرغب في دراسة الماجستير في الولايات المتحدة، ولكنني لست متأكدة من الجامعة أو البرنامج الأنسب لي أحمل درجة البكالوريوس في الهندسة الصناعية، وارغب ببرنامج يركز على تطوير الانظمة او ما يشابهها', 'beneficiary'),
(2, 1, 2, '2026-04-04 15:43:08', 'وعليكم السلام ورحمة الله وبركاته،  أهلًا بكِ فاطمة. بناءً على خلفيتك الأكاديمية في الهندسة الصناعية واهتمامك بمجال تطوير الانظمة أقدم لك “نمذجة وتحسين النظم” من الجامعات الرائدة عالميًا جامعة إلينوي في أوربانا-شامبين وتُصنّف من أفضل الجامعات في تخصصات الهندسة الصناعية بشكل عام', 'office'),
(3, 1, 2, '2026-04-04 15:43:38', 'كما أن الجامعة ضمن نطاق خدماتنا ويمكننا إصدار خطاب القبول إذا رغبتي في تقديم طلب! البيانات المطلوبة للتقديم مذكورة في قسم الاسئلة الشائعة', 'office'),
(4, 1, 2, '2026-04-04 16:59:46', 'ممتاز اذا ساقوم برفع طلب  بالمستندات المطلوبة', 'beneficiary');

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
  `status` enum('بانتظار الرد','تم الرد') DEFAULT 'بانتظار الرد'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `complaints_inquiries`
--

INSERT INTO `complaints_inquiries` (`ticket_id`, `office_id`, `bnf_id`, `inv_id`, `admin_reply`, `submission_date`, `subject`, `message`, `status`) VALUES
(33, NULL, 3, NULL, 'تم معالجة الخلل يرجى اعادة الرفغ', '2026-03-18 06:04:25', 'اواجه مشكلة في رفع الملفات', 'لا استطيع رفع الملفات عبر خانة التقديم على فرص المنح المعروضة', 'تم الرد'),
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
  `phone` varchar(255) NOT NULL,
  `approval_status` enum('بانتظار المراجعة','معتمد','مرفوض') NOT NULL DEFAULT 'بانتظار المراجعة',
  `account_status` enum('نشط','محظور') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `consulting_office`
--

INSERT INTO `consulting_office` (`office_id`, `ccr_number`, `email`, `office_name`, `office_description`, `Bachelor_fee`, `Masters_fee`, `Phd_fee`, `password`, `phone`, `approval_status`, `account_status`) VALUES
(2, ' 101989710', 'info@asasstudyabroad.com', 'أساس للدراسة بالخارج', 'مكتب رائد في مجال الاستشارات التعليمية والقبول الجامعي، نخدم الطلاب في أكثر من 15 دولة حول العالم، مستشارينا معتمدين من التعليم البريطاني، والتعليم الكندي والتعليم الامريكي ومنظمة ICEF ومؤهلين لتقديم النصيحة والاستشارة المناسبة لكل طالب ونسعى لتقديم النصيحة الأمينة حسب متطلبات الجامعات  ومؤهلات الطلب لضمان انسب خيارات الدراسة لكل طالب.\r\nوكلاء افضل الجامعات والمعاهد في أمريكا، بريطانيا، استراليا، نيوزيلندا، أيرلندا، كندا، ماليزيا، اسبانيا، ايطاليا، فرنسا، الهند، مالطا، جنوب افريقيا، تركيا، وغيرها من الدول، تقديم سهل وسريع على المعاهد والجامعات لضمان توفير القبول المناسب بأسرع وقت ممكن.', 300, 200, 600, '$2y$10$Nb3JPrsRVArQ8nZ1saNTYux42WPRTBnIJPg5kMEEDzR706mx98pVu', '0541722808', 'معتمد', 'نشط'),
(3, '1029836153', 'gecs.edu.@outlook.com', 'مكتب الخليج', 'مكتب متخصص في الاستشارات الأكاديمية ومساعدة الطلاب في إجراءات القبول الجامعي نقدّم خدمات تقييم المؤهلات الأكاديمية، ترشيح الجامعات والتخصصات المناسبة، وإرسال طلبات القبول إلى الجامعات\r\nكما نتولى متابعة حالة الطلب حتى صدور قرار القبول، مع تقديم إرشادات حول اختيار الدولة والبرنامج الدراسي الأنسب للطالب', 150, 200, 300, '$2y$10$gYjC1hY4IAfyv0Qv7vu7SOpllzfdvYdCArKRPLWwLWQrD3mXHXw1a', '0549778902', 'معتمد', 'نشط'),
(4, '4030257841', 'info@almasarconsult.com', 'المسار الدولي', 'مكتبنا يقدم خدمات الإرشاد الأكاديمي للطلاب الراغبين في الدراسة في الجامعات الآسيوية والعربية ويتضمن في مساعدة المستفيد لاختيار الدولة والبرنامج الدراسي المناسب، توضيح متطلبات القبول في الجامعات، وترتيب خطوات التقديم بشكل منظم، إضافة إلى متابعة الطلبات مع الجهات التعليمية حتى استكمال إجراءات القبول.', 200, 300, 500, '$2y$10$1huXuK7B5YrMHRkOmhuPjupg/RQGXqerjZJMVF05NBReN4475hfPK', '0551234567', 'معتمد', 'نشط'),
(5, '1010967823', 'info@madar-edu.sa', 'مدار التعليمية', 'مكتب استشاري متخصص في خدمات القبول الأكاديمي في الجامعات والمعاهد الدولية، يركّز على توجيه الطلاب لاختيار التخصصات المناسبة وفق ميولهم الأكاديمية واحتياجات سوق العمل، كما يقدم مكتبنا استشارات دقيقة حول خيارات الدراسة في الدول الأوروبية ويتابع حالة الطلب مع الجهات التعليمية ويقوم بالتنسيق المستمر لضمان سير الإجراءات حتى صدور القبول', 450, 300, 400, '$2y$10$b8WQCi9ZGiIYzyLuEimum.8exMMZKdefidMo45BOME6o8bj.98YAG', '0556182394', 'معتمد', 'نشط');

-- --------------------------------------------------------

--
-- بنية الجدول `e_contract`
--

CREATE TABLE `e_contract` (
  `contract_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `payments_count` int(11) NOT NULL,
  `funding_duration` int(11) NOT NULL,
  `ctr_status` enum('نشط','ملغي','منتهي') NOT NULL,
  `terms` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `approval_status` enum('انتظار الموافقة','تمت الموافقة') DEFAULT 'انتظار الموافقة'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `e_contract`
--

INSERT INTO `e_contract` (`contract_id`, `request_id`, `payments_count`, `funding_duration`, `ctr_status`, `terms`, `amount`, `approval_status`) VALUES
(1, 2, 2, 1, 'نشط', 'أولاً: التوظيف بعد التخرج\r\nيلتزم المستفيد بعد إتمام البرنامج الأكاديمي بنجاح بالعمل لدى سابك او إحدى الشركات التابعة لها لمدة لا تقل عن سنتين وذلك وفق عقد عمل مستقل يُبرم بعد التخرج، و في حال امتناع المستفيد عن الالتحاق بالوظيفة دون سبب مشروع تقبله الجهة الممولة، يحق للمستثمر المطالبة باسترداد كامل مبالغ التمويل المصروفة.\r\n\r\nثانياً: باستمرارية الدراسة  2) يلتزم المستفيد بالاستمرار في الدراسة حتى إتمام البرنامج المحدد في هذا العقد، وعدم الانسحاب أو تأجيل الدراسة إلا بموافقة مسبقة.  3) في حال الانسحاب أو الفصل الأكاديمي بسبب تقصير من المستفيد، يحق للمستثمر إيقاف التمويل فورًا والمطالبة باسترداد المبالغ المصروفة.\r\n\r\nثالثاً: استخدام أموال المنحة  4) يقر المستفيد بأن جميع المبالغ المصروفة بموجب هذا العقد مخصصة فقط لتغطية الرسوم الدراسية والمصاريف التعليمية المرتبطة بالبرنامج المحدد، وفي حال ثبوت استخدام أي جزء من المبالغ لأغراض غير تعليمية أو تقديم مستندات غير صحيحة، يعد ذلك إخلالًا فوريًا بالعقد ويعرض المستفيد للمساءلة القانونية.', 147540.00, 'تمت الموافقة'),
(4, 10, 6, 3, 'نشط', '1.	يلتزم المستثمر بتمويل تكاليف الدراسة كاملة وفق البرنامج المتفق عليه، بما يشمل الرسوم الدراسية والمصروفات الأساسية المرتبطة بالدراسة.\r\n2.	يلتزم المستفيد بالتفرغ الكامل للدراسة وعدم الالتحاق بأي عمل آخر خلال فترة البرنامج إلا بموافقة خطية من المستثمر.\r\n3.	يلتزم المستفيد بالحفاظ على مستوى أكاديمي لا يقل عن تقدير جيد جدًا، وفي حال انخفاض المعدل يتم إنذاره رسميًا، وقد يؤدي تكرار ذلك إلى إيقاف التمويل.\r\n4.	يلتزم المستفيد بتقديم تقارير أكاديمية دورية بعد كل فصل دراسي توضح مستوى التقدم والنتائج.\r\n5.	يلتزم المستفيد بالالتزام بجميع أنظمة ولوائح الجامعة والجهة التعليمية.\r\n6.	يلتزم المستفيد بإشعار المستثمر فور حدوث أي تغيير في حالته الأكاديمية أو تأجيل أو انسحاب من البرنامج.\r\n7.	في حال الانسحاب أو الفصل الأكاديمي دون عذر مقبول، يلتزم المستفيد بإعادة جزء أو كامل المبالغ المصروفة حسب ما يحدده المستثمر.\r\n8.	بعد التخرج، يلتزم المستفيد بالعمل لدى جهة المستثمر أو إحدى الجهات التابعة لها لمدة لا تقل عن 3 سنوات.\r\n9.	في حال عدم التزام المستفيد بالعمل بعد التخرج، يلتزم بسداد تكاليف التمويل أو جزء منها وفق ما يتم الاتفاق عليه.\r\n10.	يحق للمستثمر متابعة أداء المستفيد أكاديميًا ومهنيًا طوال فترة العقد.\r\n11.	يلتزم المستفيد بالمشاركة في التدريب العملي أو البرامج التطويرية التي يحددها المستثمر خلال أو بعد فترة الدراسة.\r\n12.	يتم صرف الدفعات المالية على مراحل مرتبطة بالتقدم الأكاديمي، ويحق للمستثمر إيقاف أي دفعة في حال عدم استيفاء الشروط.\r\n13.	يلتزم الطرفان بالحفاظ على سرية المعلومات المتبادلة وعدم استخدامها خارج نطاق هذا العقد.\r\n14.	في حال حدوث أي نزاع، يتم حله وديًا، وفي حال تعذر ذلك يتم الرجوع إلى الجهات القانونية المختصة في المملكة العربية السعودية.', 180000.00, '');

-- --------------------------------------------------------

--
-- بنية الجدول `investor`
--

CREATE TABLE `investor` (
  `inv_id` int(11) NOT NULL,
  `ccr_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `inv_number` varchar(11) NOT NULL,
  `inv_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `approval_status` enum('بانتظار المراجعة','معتمد','مرفوض') NOT NULL DEFAULT 'بانتظار المراجعة',
  `account_status` enum('نشط','محظور') NOT NULL DEFAULT 'نشط'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `investor`
--

INSERT INTO `investor` (`inv_id`, `ccr_number`, `email`, `inv_number`, `inv_name`, `password`, `approval_status`, `account_status`) VALUES
(1, '1010010814', 'InvestorRelations@safco.sabic.com', '0530014051', 'سابك - الشركة السعودية للصناعات', '$2y$10$/8anVADcwa6r5K6nJk/RCO9Tb6fS7G05m9eUS4FIXm90T2iaJ7c4a', 'معتمد', 'نشط'),
(2, '4030175741', 'Baggage.Inquiries@Saudia.com', '0557159469', 'الخطوط الجوية السعودية', '$2y$10$bPlG61BR9K5MrBZfhQUBJ.hZCdFLe.TuzRHVx2YgzRyuepvV5ZHA.', 'معتمد', 'نشط'),
(3, '2052101150', 'investor@aramco.com', '0521075839', 'أرامكو', '$2y$10$IGfO7u71zWhhE/n3JrK72.0Wh2ZOM9PyPNd.3vGYwGuYarq8SCIT.', 'مرفوض', 'نشط'),
(4, '1010209985', 'partnerships@hmg.com.sa', '0558743210', 'مجموعة الدكتور سليمان الحبيب الطبية', '$2y$10$gg2wPj4CX4GDa3fOElFRU.E5HqL9B9vsGNGR65sATwpliUYSKRF8G', 'معتمد', 'نشط');

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
(4, 'مصر'),
(5, 'السويد'),
(5, 'المانيا'),
(5, 'ايطاليا'),
(5, 'بريطانيا'),
(5, 'تركيا'),
(5, 'سويسرا'),
(5, 'نيوزلندا'),
(5, 'هولندا');

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

--
-- إرجاع أو استيراد بيانات الجدول `payments`
--

INSERT INTO `payments` (`payment_id`, `contract_id`, `installment_number`, `payment_amount`, `payment_status`, `payment_date`) VALUES
(1, 1, 1, 73770, 'بانتظار الدفع', '2026-04-05 18:43:24'),
(2, 1, 2, 73770, 'بانتظار الدفع', '2026-04-05 18:43:24');

-- --------------------------------------------------------

--
-- بنية الجدول `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `rating_date` datetime DEFAULT current_timestamp(),
  `comment_text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `rating`
--

INSERT INTO `rating` (`rating_id`, `request_id`, `rating_date`, `comment_text`) VALUES
(4, 1, '2026-04-04 20:40:56', 'تم التعامل مع طلبي باحترافية من البداية وحتى إصدار القبول، أقدّر الجهود المبذولة واشكر المكتب الشكر الجزيل'),
(8, 8, '2026-05-04 00:16:39', 'أشكر مكتب مدار التعليمية على دعمهم خلال رحلة التقديم، حيث كان التعامل احترافيًا وسريعًا، وتم توضيح جميع خطوات القبول بشكل دقيق. ساعدني الفريق في اختيار الجامعة المناسبة ومتابعة الطلب حتى صدور القبول دون تأخير.\r\n التجربة كانت مريحة وواضحة، وأوصي بالتعامل معه👍');

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
(1, 'صناعي وتشغيلي', 1, 'تطوير الأنظمة الصناعية المتقدمة', 'أن يكون المتقدم سعودي الجنسية.\r\nحاصل على بكالوريوس في الهندسة الصناعية بمعدل لا يقل عن 3.5 من 4.\r\nخبرة عملية لا تقل عن سنة في مجال ذات صلة (للماجستير).\r\nالالتزام بالعمل في سابك بعد التخرج لمدة محددة.', 'بكالوريوس', 'تحسين العمليات الصناعية، سلاسل الامداد، نظم الدعم الشاملة', '2026-04-15 00:00:00'),
(4, 'صناعي وتشغيلي', 2, 'منح هندسة وصيانة الطائرات', '•	موزونة ثلاثية لا تقل عن 98%\r\n•	شهادة لغة إنجليزية ستيب لا تقل عن 75% أو ما يعادلها (ايلتس ، توفل)\r\n•	لياقة طبية معتمدة', 'بكالوريوس', 'هندسة الطيران •	هندسة الصيانة الجوية •	هندسة الأنظمة الصناعية', '2026-08-10 00:00:00'),
(5, 'ادراي', 2, 'برنامج SOAR ', '- أن يكون المتقدم سعودي الجنسية.\r\n- حاصل على درجة البكالوريوس بمعدل (2.75 من 4 أو 3.75 من 5) ، أو درجة الماجستير بمعدل (2.75 من 4 أو 3.75 من 5).\r\n- حديثي التخرج (خبرة سنتين على الأقل).\r\n- درجة اختبار اللغة الإنجليزية (STEP 75) أو  (ايتلس 5.5) أو (توفل 46 iBT أو 475 PBT).', 'ماجستير', 'ادارة الاعمال - ادارة الطيران - ادارة الجودة - ادارة المخاطر - التسويق - العلاقات العامة - المالية - المحاسبة - الموارد البشرية', '2026-06-24 00:00:00'),
(6, 'صحي', 4, 'برنامج الحبيب لتأهيل الكوادر الطبية', '1.	الحصول على درجة البكالوريوس والماجستير في تخصص صحي معتمد بمعدل لا يقل عن جيد جدًا\r\n2.	شهادة إجادة اللغة الإنجليزية بدرجة لا تقل عن IELTS 7.0 أو ما يعادلها\r\n3.	إعداد وتقديم مقترح بحثي مفصل يوضح مشكلة بحثية واضحة وأهداف الدراسة والمنهجية المقترحة، ويكون مرتبطًا بأحد مجالات الرعاية الصحية أو تطوير الخدمات الطبية\r\n4.	توفير خطابي توصية أكاديمية على الأقل\r\n5.	اجتياز المقابلة الشخصية التي تقيم الجوانب العلمية والبحثية والمهارات الشخصية\r\n6.	الالتزام بالتفرغ الكامل للدراسة والبحث طوال مدة البرنامج\r\n7.	الاستعداد للمشاركة في التدريب السريري أو العمل البحثي داخل منشآت المستشفى بعد الانتهاء من المنحة', 'دكتوراه', 'الطب البشري، التمريض، إدارة الخدمات الصحية، المختبرات الطبية', '2027-02-11 00:00:00');

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_requests`
--

CREATE TABLE `scholarship_requests` (
  `request_id` int(11) NOT NULL,
  `scholarship_id` int(11) NOT NULL,
  `bnf_id` int(11) NOT NULL,
  `Submit_date` date NOT NULL,
  `request_status` enum('مقبول','مرفوض','تحت المراجعة','منتهي','ملغي') NOT NULL DEFAULT 'تحت المراجعة',
  `major_name` varchar(100) NOT NULL,
  `univ_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `scholarship_requests`
--

INSERT INTO `scholarship_requests` (`request_id`, `scholarship_id`, `bnf_id`, `Submit_date`, `request_status`, `major_name`, `univ_name`) VALUES
(2, 1, 1, '2026-03-25', 'مقبول', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة'),
(6, 4, 1, '2026-04-11', 'مرفوض', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة'),
(10, 6, 8, '2026-05-02', 'مقبول', 'دكتوراه في العلوم السريرية', 'جامعة كينجز كوليدج لندن');

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
(1, 2, 'CV', '1774446178_Fatimah_Alhammadi_CV.pdf', 'uploads/admission_requests/1_cv_file.pdf'),
(2, 2, 'Certificate', '1774446178_DegreeCertificate.pdf', 'uploads/admission_requests/1_degree_file.pdf'),
(3, 2, 'Recommendation', '1774446178_Fatimah_Alhammadi_Recommendation.pdf', 'uploads/admission_requests/1_recommendation_file.pdf'),
(4, 2, 'Acceptance', '1774446178_AcceptanceLetter.pdf', 'uploads/admission_requests/3_recommendation_file.pdf'),
(13, 6, 'CV', 'Fatima_Alghamdi_CV.pdf', 'uploads/scholarship_requests/6_cv_file.pdf'),
(14, 6, 'Certificate', 'Fatima_Alghamdi_University_Degree.pdf', 'uploads/scholarship_requests/6_cert_file.pdf'),
(15, 6, 'Recommendation', 'Fatima_Alghamdi_Recommendation_Letters.pdf', 'uploads/scholarship_requests/6_rec_file.pdf'),
(16, 6, 'Acceptance', 'Admission_Result.pdf.pdf', 'uploads/scholarship_requests/6_accept_file.pdf'),
(29, 10, 'CV', '1777678802_reemCV.pdf', '1777678802_reemCV.pdf'),
(30, 10, 'Certificate', '1777678802_Reem_Academic Transcript.pdf', '1777678802_Reem_Academic Transcript.pdf'),
(31, 10, 'Recommendation', '1777678802_Reem_Recond.pdf', '1777678802_Reem_Recond.pdf'),
(32, 10, 'Acceptance', '1777678802_Acceptance_Letter.pdf', '1777678802_Acceptance_Letter.pdf');

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
  ADD KEY `bnf_id` (`bnf_id`),
  ADD KEY `office_id` (`office_id`);

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
  ADD KEY `request_id` (`request_id`);

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
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admission_request`
--
ALTER TABLE `admission_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `admission_request_documents`
--
ALTER TABLE `admission_request_documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `beneficiary`
--
ALTER TABLE `beneficiary`
  MODIFY `bnf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bnf_inv_msg`
--
ALTER TABLE `bnf_inv_msg`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bnf_off_msg`
--
ALTER TABLE `bnf_off_msg`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `complaints_inquiries`
--
ALTER TABLE `complaints_inquiries`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `consulting_office`
--
ALTER TABLE `consulting_office`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `e_contract`
--
ALTER TABLE `e_contract`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `investor`
--
ALTER TABLE `investor`
  MODIFY `inv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `scholarship_opps`
--
ALTER TABLE `scholarship_opps`
  MODIFY `scholarship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `scholarship_requests`
--
ALTER TABLE `scholarship_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `scholarship_request_documents`
--
ALTER TABLE `scholarship_request_documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

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
  ADD CONSTRAINT `rating_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `admission_request` (`request_id`);

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
