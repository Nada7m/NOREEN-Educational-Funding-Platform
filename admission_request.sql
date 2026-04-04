-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 04 أبريل 2026 الساعة 14:56
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
  `Result_status` enum('قيد المعالجة','أصدرت') NOT NULL DEFAULT 'قيد المعالجة',
  `request_status` enum('مرفوض','مقبول','في الانتظار','') NOT NULL DEFAULT 'في الانتظار',
  `result` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admission_request`
--

INSERT INTO `admission_request` (`request_id`, `bnf_id`, `office_id`, `program_type`, `major_name`, `univ_name`, `Submit_date`, `result_notes`, `Result_status`, `request_status`, `result`) VALUES
(1, 1, 2, '', 'نمذجة وتحسين النظم الصناعية', 'إلينوي أوربانا شامبين - الولايات المتحدة', '2026-04-02', '', 'قيد المعالجة', 'مقبول', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admission_request`
--
ALTER TABLE `admission_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `bnf_id` (`bnf_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admission_request`
--
ALTER TABLE `admission_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `admission_request`
--
ALTER TABLE `admission_request`
  ADD CONSTRAINT `admission_request_ibfk_1` FOREIGN KEY (`bnf_id`) REFERENCES `beneficiary` (`bnf_id`),
  ADD CONSTRAINT `admission_request_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `consulting_office` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
