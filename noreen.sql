-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: 09 مارس 2026 الساعة 21:57
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
  `report_file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `report_upload` enum('مرفوع','غير مرفوع','','') COLLATE utf8mb4_general_ci NOT NULL,
  `installment_num` int NOT NULL,
  `submit_date` date NOT NULL,
  `report_appoval` enum('معتمد','غير معتمد','','') COLLATE utf8mb4_general_ci NOT NULL,
  `bnf_id` int NOT NULL,
  `contract_id` int NOT NULL,
  PRIMARY KEY (`report_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `contract_id` (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL,
  `admin_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `admin_name` (`admin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `admission_request`
--

DROP TABLE IF EXISTS `admission_request`;
CREATE TABLE IF NOT EXISTS `admission_request` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `office_id` int NOT NULL,
  `major_name` varchar(100) NOT NULL,
  `univ_name` varchar(100) NOT NULL,
  `Submit_date` date NOT NULL,
  `result_notes` text NOT NULL,
  `Result_status` enum('في انتظار الاصدار','أصدرت') NOT NULL,
  PRIMARY KEY (`request_id`),
  KEY `office_id` (`office_id`),
  KEY `bnf_id` (`bnf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `beneficiary`
--

DROP TABLE IF EXISTS `beneficiary`;
CREATE TABLE IF NOT EXISTS `beneficiary` (
  `bnf_id` int NOT NULL AUTO_INCREMENT,
  `f_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `l_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `phone_num` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field` enum('صناعي','صحي','اداري','') COLLATE utf8mb4_general_ci NOT NULL,
  `degree_level` enum('ثانوي','بكالريوس','ماجستير','') COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`bnf_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `bnf_inv_msg`
--

DROP TABLE IF EXISTS `bnf_inv_msg`;
CREATE TABLE IF NOT EXISTS `bnf_inv_msg` (
  `msg_id` int NOT NULL,
  `msg_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `msg_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bnf_id` int NOT NULL,
  `inv_id` int NOT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `inv_id` (`inv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `bnf_off_msg`
--

DROP TABLE IF EXISTS `bnf_off_msg`;
CREATE TABLE IF NOT EXISTS `bnf_off_msg` (
  `msg_id` int NOT NULL AUTO_INCREMENT,
  `msg_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `msg_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bnf_id` int NOT NULL,
  `office_id` int NOT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `complaints_inquiries`
--

DROP TABLE IF EXISTS `complaints_inquiries`;
CREATE TABLE IF NOT EXISTS `complaints_inquiries` (
  `ticket_id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `sender_role` enum('Beneficiary','Investor','Office') COLLATE utf8mb4_general_ci NOT NULL,
  `submission_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `subject` text COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('بانتظار الرد','تم الرد عليها') COLLATE utf8mb4_general_ci DEFAULT 'بانتظار الرد',
  `admin_reply` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `consulting_office`
--

DROP TABLE IF EXISTS `consulting_office`;
CREATE TABLE IF NOT EXISTS `consulting_office` (
  `office_id` int NOT NULL AUTO_INCREMENT,
  `office_name` varchar(255) NOT NULL,
  `office_description` text NOT NULL,
  `Bachelor_fee` int NOT NULL,
  `Masters_fee` int NOT NULL,
  `Phd_fee` int NOT NULL,
  `ccr_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  PRIMARY KEY (`office_id`),
  UNIQUE KEY `ccr_number` (`ccr_number`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `e_contract`
--

DROP TABLE IF EXISTS `e_contract`;
CREATE TABLE IF NOT EXISTS `e_contract` (
  `contract_id` int NOT NULL AUTO_INCREMENT,
  `payments_count` int NOT NULL,
  `funding_duration` int NOT NULL,
  `ctr_status` enum('نشط','ملغي') COLLATE utf8mb4_general_ci NOT NULL,
  `terms` text COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `request_id` int NOT NULL,
  PRIMARY KEY (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `investor`
--

DROP TABLE IF EXISTS `investor`;
CREATE TABLE IF NOT EXISTS `investor` (
  `inv_id` int NOT NULL,
  `inv_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ccr_number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`inv_id`),
  UNIQUE KEY `ccr_number` (`ccr_number`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `office_country`
--

DROP TABLE IF EXISTS `office_country`;
CREATE TABLE IF NOT EXISTS `office_country` (
  `con_id` int NOT NULL AUTO_INCREMENT,
  `office_id` int NOT NULL,
  `con_name` varchar(100) NOT NULL,
  PRIMARY KEY (`con_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL,
  `installment_number` int NOT NULL,
  `payment_amount` int NOT NULL,
  `payment_status` enum('تم الدفع','بانتظار الدفع','','') COLLATE utf8mb4_general_ci NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `contract_id` int NOT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `contract_id` (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `rating`
--

DROP TABLE IF EXISTS `rating`;
CREATE TABLE IF NOT EXISTS `rating` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `office_id` int NOT NULL,
  `rating_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `comment_text` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`rating_id`),
  KEY `bnf_id` (`bnf_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_opps`
--

DROP TABLE IF EXISTS `scholarship_opps`;
CREATE TABLE IF NOT EXISTS `scholarship_opps` (
  `scholarship_id` int NOT NULL AUTO_INCREMENT,
  `sch_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `sch_field` enum('تقني وحوسبي','علوم طبيعية','صناعي وتشغيلي','ادراي','قانوني','اجتماعي وانساني','تصميمي','اقتصادي','إعلامي','بيئي','لوجيستي','صحي') COLLATE utf8mb4_general_ci NOT NULL,
  `requirements` text COLLATE utf8mb4_general_ci NOT NULL,
  `study_level` enum('بكالوريوس','ماجستير','دكاوراه') COLLATE utf8mb4_general_ci NOT NULL,
  `Specializations` text COLLATE utf8mb4_general_ci NOT NULL,
  `app_deadline` datetime NOT NULL,
  `inv_id` int NOT NULL,
  PRIMARY KEY (`scholarship_id`),
  UNIQUE KEY `scholarship_id` (`scholarship_id`),
  KEY `field_filter` (`sch_field`),
  KEY `inv_id` (`inv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_requests`
--

DROP TABLE IF EXISTS `scholarship_requests`;
CREATE TABLE IF NOT EXISTS `scholarship_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `bnf_id` int NOT NULL,
  `scholarship_id` int NOT NULL,
  `Submit_date` date NOT NULL,
  `request_status` enum('مفبول','مرفوض','','') NOT NULL,
  `major_name` varchar(100) NOT NULL,
  `univ_name` varchar(100) NOT NULL,
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `bnf_id` (`bnf_id`,`scholarship_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_request_documents`
--

DROP TABLE IF EXISTS `scholarship_request_documents`;
CREATE TABLE IF NOT EXISTS `scholarship_request_documents` (
  `doc_id` int NOT NULL,
  `doc_type` enum('CV','LastDegreeCertificate','RecommendationLetter','AcceptanceLetter') COLLATE utf8mb4_general_ci NOT NULL,
  `file _name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `request_id` int NOT NULL,
  PRIMARY KEY (`doc_id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `academic_report`
--
ALTER TABLE `academic_report`
  ADD CONSTRAINT `academic_report_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `academic_report_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `e_contract` (`contract_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `admission_request`
--
ALTER TABLE `admission_request`
  ADD CONSTRAINT `admission_request_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `admission_request_documents`
--
ALTER TABLE `admission_request_documents`
  ADD CONSTRAINT `admission_request_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `admission_request` (`request_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `bnf_inv_msg`
--
ALTER TABLE `bnf_inv_msg`
  ADD CONSTRAINT `bnf_inv_msg_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `bnf_inv_msg_ibfk_2` FOREIGN KEY (`inv_id`) REFERENCES `investor` (`inv_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `bnf_off_msg`
--
ALTER TABLE `bnf_off_msg`
  ADD CONSTRAINT `bnf_off_msg_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `bnf_off_msg_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `office_country`
--
ALTER TABLE `office_country`
  ADD CONSTRAINT `office_country_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `e_contract` (`contract_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `scholarship_opps`
--
ALTER TABLE `scholarship_opps`
  ADD CONSTRAINT `scholarship_opps_ibfk_1` FOREIGN KEY (`inv_id`) REFERENCES `investor` (`inv_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `scholarship_requests`
--
ALTER TABLE `scholarship_requests`
  ADD CONSTRAINT `scholarship_requests_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- قيود الجداول `scholarship_request_documents`
--
ALTER TABLE `scholarship_request_documents`
  ADD CONSTRAINT `scholarship_request_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `scholarship_requests` (`request_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
