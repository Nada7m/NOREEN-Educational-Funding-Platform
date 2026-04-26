-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: 26 أبريل 2026 الساعة 17:14
-- إصدار الخادم: 9.1.0
-- PHP Version: 8.3.14

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

DROP TABLE IF EXISTS `academic_report`;
CREATE TABLE IF NOT EXISTS `academic_report` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `contract_id` int NOT NULL,
  `payment_id` int NOT NULL,
  `report_file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `report_upload` enum('مرفوع','غير مرفوع','','') COLLATE utf8mb4_general_ci NOT NULL,
  `submit_date` date NOT NULL,
  `report_appoval` enum('معتمد','غير معتمد','','') COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`report_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `contract_id` (`contract_id`),
  KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `academic_report`
--

INSERT INTO `academic_report` (`report_id`, `bnf_id`, `contract_id`, `payment_id`, `report_file`, `report_upload`, `submit_date`, `report_appoval`) VALUES
(1, 1, 1, 1, 'uploads/1775414890_Admission_Result.pdf.pdf', 'مرفوع', '2026-04-05', 'غير معتمد'),
(2, 3, 3, 5, 'uploads/1775474531_Fatima_Alghamdi_CV.pdf', 'مرفوع', '2026-04-06', 'معتمد');

-- --------------------------------------------------------

--
-- بنية الجدول `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `admin_name` (`admin_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `password`) VALUES
(1, 'noreenAdmin', '$2y$12$QiwXOpLzO08yFHv9kyLsEOcrUCgpUJ0yOVfjVshq706TO9M3p.NtG');

-- --------------------------------------------------------

--
-- بنية الجدول `admission_request`
--

DROP TABLE IF EXISTS `admission_request`;
CREATE TABLE IF NOT EXISTS `admission_request` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `office_id` int NOT NULL,
  `program_type` enum('bachelor','master','phd') COLLATE utf8mb4_general_ci NOT NULL,
  `major_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `univ_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Submit_date` date NOT NULL,
  `result_notes` text COLLATE utf8mb4_general_ci,
  `Result_status` enum('قيد المعالجة','أُصدرت','لم تُصدر') COLLATE utf8mb4_general_ci DEFAULT 'قيد المعالجة',
  `request_status` enum('في الانتظار','مقبول','مرفوض') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'في الانتظار',
  `payment_status` enum('غير مدفوع','مدفوع','بانتظار الدفع') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'غير مدفوع',
  `result` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admission_request`
--

INSERT INTO `admission_request` (`request_id`, `bnf_id`, `office_id`, `program_type`, `major_name`, `univ_name`, `Submit_date`, `result_notes`, `Result_status`, `request_status`, `payment_status`, `result`) VALUES
(1, 1, 2, 'master', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة', '2026-04-02', 'تم رفع خطاب القبول الرسمي أدناه يرجى مراجعة الملف المرفق والاطلاع على الشروط والأحكام الأكاديمية والمالية قبل اتخاذ أي إجراء لاحق.', '', 'مقبول', 'مدفوع', 'uploads/admission_results/1_result.pdf'),
(3, 3, 3, 'bachelor', 'هندسة الطيران', 'جامعة إمبري ريدل للطيران', '2026-04-06', 'مبارك', '', 'مقبول', 'مدفوع', 'uploads/admission_results/3_result.pdf'),
(4, 3, 3, 'bachelor', 'هندسة الطيران', 'جامعة إمبري ريدل للطيران', '2026-04-14', '', 'قيد المعالجة', 'في الانتظار', 'بانتظار الدفع', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `admission_request_documents`
--

DROP TABLE IF EXISTS `admission_request_documents`;
CREATE TABLE IF NOT EXISTS `admission_request_documents` (
  `doc_id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `doc_type` enum('CV','High School Certificate','University Degree Certificate','Academic Certificates','Academic Transcript','Language Certificate','Passport','Recommendation Letters','Statement of Purpose','Letter of Intent','Research Proposal','Acceptance Letter','Other Certificates') COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`doc_id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(21, 4, 'CV', 'Tasneem_Alharbi_CV.pdf.pdf', 'uploads/admission_requests/4_cv_file.pdf'),
(22, 4, 'Passport', 'Passport_Tasneem.pdf', 'uploads/admission_requests/4_passport_file.pdf'),
(23, 4, 'Language Certificate', 'IELTS_Tasneem.pdf.pdf', 'uploads/admission_requests/4_language_file.pdf'),
(24, 4, 'Recommendation Letters', 'Recommendation_Tasneem.pdf', 'uploads/admission_requests/4_recommendation_file.pdf'),
(25, 4, 'High School Certificate', 'HS_Certificate_Tasneem.pdf.pdf', 'uploads/admission_requests/4_highschool_file.pdf'),
(26, 4, 'Letter of Intent', 'SOP_Tasneem.pdf', 'uploads/admission_requests/4_intent_file.pdf');

-- --------------------------------------------------------

--
-- بنية الجدول `beneficiary`
--

DROP TABLE IF EXISTS `beneficiary`;
CREATE TABLE IF NOT EXISTS `beneficiary` (
  `bnf_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `f_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `l_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `phone_num` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `degree_level` enum('ثانوي','بكالوريوس','ماجستير','') COLLATE utf8mb4_general_ci NOT NULL,
  `account_status` enum('نشط','محظور') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'نشط',
  `sch_field` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`bnf_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `beneficiary`
--

INSERT INTO `beneficiary` (`bnf_id`, `email`, `f_name`, `l_name`, `phone_num`, `password`, `degree_level`, `account_status`, `sch_field`) VALUES
(1, 'FatmaAlghamdi@gmail.com', 'فاطمة', 'الغامدي', '0502508284', '$2y$10$vS2J608Dv/DImLLpfl9Vu.PcHg0YtAsR2HX8IgX87Lj9Bh1AMhQlS', 'بكالوريوس', 'نشط', ''),
(3, 'Tasneem@gmail.com', 'تسنيم', 'الحربي', '0535246000', '$2y$10$6AxmbjZBFFpd8KP8K.9jWuFUkZ0ifxmunNsiRxq3pzNng6mvo2RhW', 'ثانوي', 'نشط', ''),
(4, 'noorAlfif99@gmail.com', 'نور', 'الفيفي', '0559800107', '$2y$10$7Ym4vCgxDdZbi7ELxvoDReX0CbOud1XYHol8HNeJ0/UdogqE3Vv/W', 'ثانوي', 'نشط', ''),
(5, 'roro.b1908@gmail.com', 'REMAS', 'ALHARBI', '0502508481', '$2y$10$0crcpoVtPfbZ6iuSFDMlPu03FwtxGPRW8AkWKbqKBfSUtkheirrCW', 'بكالوريوس', 'نشط', ''),
(6, 'amal.b1908@gmail.com', 'amal', 'AHARBI', '0502500481', '$2y$10$6ChXA/5O84Evqns2.xQPteBjckpILxBqocR.P8V.BmNiCxqbTdcUS', 'ثانوي', 'نشط', ''),
(7, 'omar.b1908@gmail.com', 'omar', 'ALHARBI', '0533508481', '$2y$10$.zaLlgn6K4VYFL0pzqbRKuvfdCOLCIqHeRnuqfADuvz4TtPYI008u', 'ثانوي', 'نشط', 'إداري');

-- --------------------------------------------------------

--
-- بنية الجدول `bnf_inv_msg`
--

DROP TABLE IF EXISTS `bnf_inv_msg`;
CREATE TABLE IF NOT EXISTS `bnf_inv_msg` (
  `msg_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `inv_id` int NOT NULL,
  `msg_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `msg_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `sender_type` enum('beneficiary','investor') COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `inv_id` (`inv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `bnf_off_msg`;
CREATE TABLE IF NOT EXISTS `bnf_off_msg` (
  `msg_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `office_id` int NOT NULL,
  `msg_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `msg_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `sender_type` enum('beneficiary','office') COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bnf_off_msg`
--

INSERT INTO `bnf_off_msg` (`msg_id`, `bnf_id`, `office_id`, `msg_time`, `msg_text`, `sender_type`) VALUES
(1, 1, 2, '2026-04-04 15:42:12', 'السلام عليكم ورحمة الله وبركاته.  أنا فاطمة الغامدي، أرغب في دراسة الماجستير في الولايات المتحدة، ولكنني لست متأكدة من الجامعة أو البرنامج الأنسب لي أحمل درجة البكالوريوس في الهندسة الصناعية، وارغب ببرنامج يركز على تطوير الانظمة او ما يشابهها', 'beneficiary'),
(2, 1, 2, '2026-04-04 15:43:08', 'وعليكم السلام ورحمة الله وبركاته،  أهلًا بكِ فاطمة. بناءً على خلفيتك الأكاديمية في الهندسة الصناعية واهتمامك بمجال تطوير الانظمة أقدم لك “نمذجة وتحسين النظم” من الجامعات الرائدة عالميًا جامعة إلينوي في أوربانا-شامبين وتُصنّف من أفضل الجامعات في تخصصات الهندسة الصناعية بشكل عام', 'office'),
(3, 1, 2, '2026-04-04 15:43:38', 'كما أن الجامعة ضمن نطاق خدماتنا ويمكننا إصدار خطاب القبول إذا رغبتي في تقديم طلب! البيانات المطلوبة للتقديم مذكورة في قسم الاسئلة الشائعة', 'office'),
(4, 1, 2, '2026-04-04 16:59:46', 'ممتاز اذا ساقوم برفع طلب  بالمستندات المطلوبة', 'beneficiary'),
(5, 3, 3, '2026-04-06 14:18:14', 'السلام عليكم', 'beneficiary'),
(6, 3, 3, '2026-04-06 14:18:26', 'وعليكم السلام', 'office');

-- --------------------------------------------------------

--
-- بنية الجدول `complaints_inquiries`
--

DROP TABLE IF EXISTS `complaints_inquiries`;
CREATE TABLE IF NOT EXISTS `complaints_inquiries` (
  `ticket_id` int NOT NULL AUTO_INCREMENT,
  `office_id` int DEFAULT NULL,
  `bnf_id` int DEFAULT NULL,
  `inv_id` int DEFAULT NULL,
  `admin_reply` text COLLATE utf8mb4_general_ci,
  `submission_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `subject` text COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('بانتظار الرد','تم الرد') COLLATE utf8mb4_general_ci DEFAULT 'بانتظار الرد',
  PRIMARY KEY (`ticket_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `inv_id` (`inv_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `consulting_office`;
CREATE TABLE IF NOT EXISTS `consulting_office` (
  `office_id` int NOT NULL AUTO_INCREMENT,
  `ccr_number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `office_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `office_description` text COLLATE utf8mb4_general_ci NOT NULL,
  `Bachelor_fee` int NOT NULL,
  `Masters_fee` int NOT NULL,
  `Phd_fee` int NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `approval_status` enum('بانتظار المراجعة','معتمد','مرفوض') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'بانتظار المراجعة',
  `account_status` enum('نشط','محظور') COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`office_id`),
  UNIQUE KEY `ccr_number` (`ccr_number`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `consulting_office`
--

INSERT INTO `consulting_office` (`office_id`, `ccr_number`, `email`, `office_name`, `office_description`, `Bachelor_fee`, `Masters_fee`, `Phd_fee`, `password`, `phone`, `approval_status`, `account_status`) VALUES
(2, ' 101989710', 'info@asasstudyabroad.com', 'أساس للدراسة بالخارج', 'مكتب رائد في مجال الاستشارات التعليمية والقبول الجامعي، نخدم الطلاب في أكثر من 15 دولة حول العالم، مستشارينا معتمدين من التعليم البريطاني، والتعليم الكندي والتعليم الامريكي ومنظمة ICEF ومؤهلين لتقديم النصيحة والاستشارة المناسبة لكل طالب ونسعى لتقديم النصيحة الأمينة حسب متطلبات الجامعات  ومؤهلات الطلب لضمان انسب خيارات الدراسة لكل طالب.\r\nوكلاء افضل الجامعات والمعاهد في أمريكا، بريطانيا، استراليا، نيوزيلندا، أيرلندا، كندا، ماليزيا، اسبانيا، ايطاليا، فرنسا، الهند، مالطا، جنوب افريقيا، تركيا، وغيرها من الدول، تقديم سهل وسريع على المعاهد والجامعات لضمان توفير القبول المناسب بأسرع وقت ممكن.', 300, 200, 600, '$2y$10$Nb3JPrsRVArQ8nZ1saNTYux42WPRTBnIJPg5kMEEDzR706mx98pVu', '0541722808', 'معتمد', 'نشط'),
(3, '1029836153', 'gecs.edu.@outlook.com', 'مكتب الخليج', 'مكتب متخصص في الاستشارات الأكاديمية ومساعدة الطلاب في إجراءات القبول الجامعي نقدّم خدمات تقييم المؤهلات الأكاديمية، ترشيح الجامعات والتخصصات المناسبة، وإرسال طلبات القبول إلى الجامعات\r\nكما نتولى متابعة حالة الطلب حتى صدور قرار القبول، مع تقديم إرشادات حول اختيار الدولة والبرنامج الدراسي الأنسب للطالب', 150, 200, 300, '$2y$10$ha7iXPVAeiRjM0bpPDo5GufT.cvp2awdRfogP0z/bIojRWUy0d4nu', '0549778902', 'معتمد', 'نشط'),
(4, '4030257841', ' info@almasarconsult.com', 'المسار الدولي', 'مكتبنا يقدم خدمات الإرشاد الأكاديمي للطلاب الراغبين في الدراسة في الجامعات الآسيوية والعربية ويتضمن في مساعدة الطالب لاختيار الدولة والبرنامج الدراسي المناسب، توضيح متطلبات القبول في الجامعات، وترتيب خطوات التقديم بشكل منظم، إضافة إلى متابعة الطلبات مع الجهات التعليمية حتى استكمال إجراءات القبول.', 200, 300, 500, '$2y$10$cLcV5hj8mNTQ9QFyMZ1ZU.g4kLS02k5VXBwn9LV4vKcbhrJxQ1KS2', '0551234567', 'معتمد', 'نشط');

-- --------------------------------------------------------

--
-- بنية الجدول `e_contract`
--

DROP TABLE IF EXISTS `e_contract`;
CREATE TABLE IF NOT EXISTS `e_contract` (
  `contract_id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `payments_count` int NOT NULL,
  `funding_duration` int NOT NULL,
  `ctr_status` enum('نشط','ملغي','منتهي') COLLATE utf8mb4_general_ci NOT NULL,
  `terms` text COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `approval_status` enum('انتظار الموافقة','تمت الموافقة') COLLATE utf8mb4_general_ci DEFAULT 'انتظار الموافقة',
  `inv_id` int NOT NULL,
  PRIMARY KEY (`contract_id`),
  KEY `request_id` (`request_id`),
  KEY `e_contract_ibfk_2` (`inv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `e_contract`
--

INSERT INTO `e_contract` (`contract_id`, `request_id`, `payments_count`, `funding_duration`, `ctr_status`, `terms`, `amount`, `approval_status`, `inv_id`) VALUES
(1, 2, 2, 1, 'نشط', 'أولاً: التوظيف بعد التخرج\r\nيلتزم المستفيد بعد إتمام البرنامج الأكاديمي بنجاح بالعمل لدى سابك او إحدى الشركات التابعة لها لمدة لا تقل عن سنتين وذلك وفق عقد عمل مستقل يُبرم بعد التخرج، و في حال امتناع المستفيد عن الالتحاق بالوظيفة دون سبب مشروع تقبله الجهة الممولة، يحق للمستثمر المطالبة باسترداد كامل مبالغ التمويل المصروفة.\r\n\r\nثانياً: باستمرارية الدراسة  2) يلتزم المستفيد بالاستمرار في الدراسة حتى إتمام البرنامج المحدد في هذا العقد، وعدم الانسحاب أو تأجيل الدراسة إلا بموافقة مسبقة.  3) في حال الانسحاب أو الفصل الأكاديمي بسبب تقصير من المستفيد، يحق للمستثمر إيقاف التمويل فورًا والمطالبة باسترداد المبالغ المصروفة.\r\n\r\nثالثاً: استخدام أموال المنحة  4) يقر المستفيد بأن جميع المبالغ المصروفة بموجب هذا العقد مخصصة فقط لتغطية الرسوم الدراسية والمصاريف التعليمية المرتبطة بالبرنامج المحدد، وفي حال ثبوت استخدام أي جزء من المبالغ لأغراض غير تعليمية أو تقديم مستندات غير صحيحة، يعد ذلك إخلالًا فوريًا بالعقد ويعرض المستفيد للمساءلة القانونية.', 147540.00, 'تمت الموافقة', 1),
(3, 5, 5, 3, 'ملغي', '1\r\n2\r\n3', 5000000.00, 'تمت الموافقة', 2);

-- --------------------------------------------------------

--
-- بنية الجدول `investor`
--

DROP TABLE IF EXISTS `investor`;
CREATE TABLE IF NOT EXISTS `investor` (
  `inv_id` int NOT NULL AUTO_INCREMENT,
  `ccr_number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `inv_number` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `inv_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `approval_status` enum('بانتظار المراجعة','معتمد','مرفوض') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'بانتظار المراجعة',
  `account_status` enum('نشط','محظور') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'نشط',
  PRIMARY KEY (`inv_id`),
  UNIQUE KEY `ccr_number` (`ccr_number`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `investor`
--

INSERT INTO `investor` (`inv_id`, `ccr_number`, `email`, `inv_number`, `inv_name`, `password`, `approval_status`, `account_status`) VALUES
(1, '1010010814', 'InvestorRelations@safco.sabic.com', '0530014051', 'سابك - الشركة السعودية للصناعات', '$2y$10$/8anVADcwa6r5K6nJk/RCO9Tb6fS7G05m9eUS4FIXm90T2iaJ7c4a', 'معتمد', 'نشط'),
(2, '4030175741', 'Baggage.Inquiries@Saudia.com', '0557159469', 'الخطوط الجوية السعودية', '$2y$10$46r8y.xFY5El.dVX2RJuN.2ijifWQAc8tZgH6GxcpN.vi5D5hJPby', 'معتمد', 'نشط'),
(3, '2052101150', 'investor@aramco.com', '0521075839', 'أرامكو', '$2y$10$IGfO7u71zWhhE/n3JrK72.0Wh2ZOM9PyPNd.3vGYwGuYarq8SCIT.', 'مرفوض', 'نشط');

-- --------------------------------------------------------

--
-- بنية الجدول `office_country`
--

DROP TABLE IF EXISTS `office_country`;
CREATE TABLE IF NOT EXISTS `office_country` (
  `office_id` int NOT NULL,
  `con_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`office_id`,`con_name`),
  KEY `office_id` (`office_id`)
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

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `installment_number` int NOT NULL,
  `payment_amount` int NOT NULL,
  `payment_status` enum('تم الدفع','بانتظار الدفع','','') COLLATE utf8mb4_general_ci NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `contract_id` (`contract_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `payments`
--

INSERT INTO `payments` (`payment_id`, `contract_id`, `installment_number`, `payment_amount`, `payment_status`, `payment_date`) VALUES
(1, 1, 1, 73770, 'بانتظار الدفع', '2026-04-05 18:43:24'),
(2, 1, 2, 73770, 'بانتظار الدفع', '2026-04-05 18:43:24'),
(5, 3, 1, 1000000, '', '2026-04-06 11:23:42'),
(6, 3, 2, 1000000, 'بانتظار الدفع', '2026-04-06 11:21:56'),
(7, 3, 3, 1000000, 'بانتظار الدفع', '2026-04-06 11:21:56'),
(8, 3, 4, 1000000, 'بانتظار الدفع', '2026-04-06 11:21:56'),
(9, 3, 5, 1000000, 'بانتظار الدفع', '2026-04-06 11:21:56');

-- --------------------------------------------------------

--
-- بنية الجدول `rating`
--

DROP TABLE IF EXISTS `rating`;
CREATE TABLE IF NOT EXISTS `rating` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `office_id` int NOT NULL,
  `request_id` int NOT NULL,
  `rating_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `comment_text` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`rating_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `office_id` (`office_id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `rating`
--

INSERT INTO `rating` (`rating_id`, `bnf_id`, `office_id`, `request_id`, `rating_date`, `comment_text`) VALUES
(4, 1, 2, 1, '2026-04-04 20:40:56', 'تم التعامل مع طلبي باحترافية من البداية وحتى إصدار القبول، أقدّر الجهود المبذولة واشكر المكتب الشكر الجزيل'),
(6, 3, 3, 3, '2026-04-06 14:18:45', 'شكرا على خدمتكم');

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_opps`
--

DROP TABLE IF EXISTS `scholarship_opps`;
CREATE TABLE IF NOT EXISTS `scholarship_opps` (
  `scholarship_id` int NOT NULL AUTO_INCREMENT,
  `sch_field` enum('تقني وحوسبي','علوم طبيعية','صناعي وتشغيلي','ادراي','قانوني','اجتماعي وانساني','تصميمي','اقتصادي','إعلامي','بيئي','لوجيستي','صحي') COLLATE utf8mb4_general_ci NOT NULL,
  `inv_id` int NOT NULL,
  `sch_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `requirements` text COLLATE utf8mb4_general_ci NOT NULL,
  `study_level` enum('بكالوريوس','ماجستير','دكتوراه') COLLATE utf8mb4_general_ci NOT NULL,
  `Specializations` text COLLATE utf8mb4_general_ci NOT NULL,
  `app_deadline` datetime NOT NULL,
  PRIMARY KEY (`scholarship_id`),
  UNIQUE KEY `scholarship_id` (`scholarship_id`),
  KEY `field_filter` (`sch_field`),
  KEY `inv_id` (`inv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `scholarship_opps`
--

INSERT INTO `scholarship_opps` (`scholarship_id`, `sch_field`, `inv_id`, `sch_name`, `requirements`, `study_level`, `Specializations`, `app_deadline`) VALUES
(1, 'صناعي وتشغيلي', 1, 'تطوير الأنظمة الصناعية المتقدمة', 'أن يكون المتقدم سعودي الجنسية.\r\nحاصل على بكالوريوس في الهندسة الصناعية بمعدل لا يقل عن 3.5 من 4.\r\nخبرة عملية لا تقل عن سنة في مجال ذات صلة (للماجستير).\r\nالالتزام بالعمل في سابك بعد التخرج لمدة محددة.', 'بكالوريوس', 'تحسين العمليات الصناعية، سلاسل الامداد، نظم الدعم الشاملة', '2026-04-15 00:00:00'),
(4, 'صناعي وتشغيلي', 2, 'منح هندسة وصيانة الطائرات', '•	موزونة ثلاثية لا تقل عن 98%\r\n•	شهادة لغة إنجليزية ستيب لا تقل عن 75% أو ما يعادلها (ايلتس ، توفل)\r\n•	لياقة طبية معتمدة', 'بكالوريوس', 'هندسة الطيران •	هندسة الصيانة الجوية •	هندسة الأنظمة الصناعية', '2026-08-10 00:00:00'),
(5, 'ادراي', 2, 'برنامج SOAR ', '- أن يكون المتقدم سعودي الجنسية.\r\n- حاصل على درجة البكالوريوس بمعدل (2.75 من 4 أو 3.75 من 5) ، أو درجة الماجستير بمعدل (2.75 من 4 أو 3.75 من 5).\r\n- حديثي التخرج (خبرة سنتين على الأقل).\r\n- درجة اختبار اللغة الإنجليزية (STEP 75) أو  (ايتلس 5.5) أو (توفل 46 iBT أو 475 PBT).', 'ماجستير', 'ادارة الاعمال - ادارة الطيران - ادارة الجودة - ادارة المخاطر - التسويق - العلاقات العامة - المالية - المحاسبة - الموارد البشرية', '2026-06-24 00:00:00');

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_requests`
--

DROP TABLE IF EXISTS `scholarship_requests`;
CREATE TABLE IF NOT EXISTS `scholarship_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `scholarship_id` int NOT NULL,
  `bnf_id` int NOT NULL,
  `Submit_date` date NOT NULL,
  `request_status` enum('مقبول','مرفوض','تحت المراجعة','منتهي','ملغي') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'تحت المراجعة',
  `major_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `univ_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`request_id`),
  KEY `scholarship_id` (`scholarship_id`),
  KEY `bnf_id` (`bnf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `scholarship_requests`
--

INSERT INTO `scholarship_requests` (`request_id`, `scholarship_id`, `bnf_id`, `Submit_date`, `request_status`, `major_name`, `univ_name`) VALUES
(2, 1, 1, '2026-03-25', 'مقبول', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة'),
(5, 4, 3, '2026-04-06', 'منتهي', 'هندسة الطيران', 'انديانا'),
(6, 4, 1, '2026-04-11', 'مرفوض', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة');

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_request_documents`
--

DROP TABLE IF EXISTS `scholarship_request_documents`;
CREATE TABLE IF NOT EXISTS `scholarship_request_documents` (
  `doc_id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `doc_type` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`doc_id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `scholarship_request_documents`
--

INSERT INTO `scholarship_request_documents` (`doc_id`, `request_id`, `doc_type`, `file_name`, `file`) VALUES
(1, 2, 'CV', '1774446178_Fatimah_Alhammadi_CV.pdf', 'uploads/admission_requests/1_cv_file.pdf'),
(2, 2, 'Certificate', '1774446178_DegreeCertificate.pdf', 'uploads/admission_requests/1_degree_file.pdf'),
(3, 2, 'Recommendation', '1774446178_Fatimah_Alhammadi_Recommendation.pdf', 'uploads/admission_requests/1_recommendation_file.pdf'),
(4, 2, 'Acceptance', '1774446178_AcceptanceLetter.pdf', 'uploads/admission_requests/3_recommendation_file.pdf'),
(9, 5, 'CV', 'Tasneem_Alharbi_CV.pdf.pdf', 'uploads/scholarship_requests/5_cv_file.pdf'),
(10, 5, 'Certificate', 'HS_Certificate_Tasneem.pdf.pdf', 'uploads/scholarship_requests/5_cert_file.pdf'),
(11, 5, 'Recommendation', 'Recommendation_Tasneem.pdf', 'uploads/scholarship_requests/5_rec_file.pdf'),
(12, 5, 'Acceptance', 'Admission_Letter_Tasneem.pdf', 'uploads/scholarship_requests/5_accept_file.pdf'),
(13, 6, 'CV', 'Fatima_Alghamdi_CV.pdf', 'uploads/scholarship_requests/6_cv_file.pdf'),
(14, 6, 'Certificate', 'Fatima_Alghamdi_University_Degree.pdf', 'uploads/scholarship_requests/6_cert_file.pdf'),
(15, 6, 'Recommendation', 'Fatima_Alghamdi_Recommendation_Letters.pdf', 'uploads/scholarship_requests/6_rec_file.pdf'),
(16, 6, 'Acceptance', 'Admission_Result.pdf.pdf', 'uploads/scholarship_requests/6_accept_file.pdf');

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
  ADD CONSTRAINT `e_contract_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `scholarship_requests` (`request_id`),
  ADD CONSTRAINT `e_contract_ibfk_2` FOREIGN KEY (`inv_id`) REFERENCES `investor` (`inv_id`);

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
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`),
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
