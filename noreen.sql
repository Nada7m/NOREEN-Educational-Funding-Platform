-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 18 مارس 2026 الساعة 04:42
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints_inquiries`
--
ALTER TABLE `complaints_inquiries`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `bnf_id` (`bnf_id`),
  ADD KEY `inv_id` (`inv_id`),
  ADD KEY `office_id` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaints_inquiries`
--
ALTER TABLE `complaints_inquiries`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `complaints_inquiries`
--
ALTER TABLE `complaints_inquiries`
  ADD CONSTRAINT `complaints_inquiries_ibfk_2` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `complaints_inquiries_ibfk_3` FOREIGN KEY (`inv_id`) REFERENCES `investor` (`inv_id`),
  ADD CONSTRAINT `complaints_inquiries_ibfk_4` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
