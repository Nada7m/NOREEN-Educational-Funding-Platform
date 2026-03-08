-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 09 مارس 2026 الساعة 00:13
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
-- Database: `noreen_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `bnf_off_msg`
--

CREATE TABLE `bnf_off_msg` (
  `msg_id` int(11) NOT NULL,
  `sent_date` datetime NOT NULL DEFAULT current_timestamp(),
  `msg_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `e_contract`
--

CREATE TABLE `e_contract` (
  `contract_id` int(11) NOT NULL,
  `installments_count` int(11) NOT NULL,
  `funding_duration` int(11) NOT NULL,
  `ctr_status` enum('نشط','ملغي') NOT NULL,
  `terms` text NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `scholarship_opps`
--

CREATE TABLE `scholarship_opps` (
  `scholarship_id` int(50) NOT NULL,
  `sch_name` varchar(100) NOT NULL,
  `sch_field` enum('تقني وحوسبي','علوم طبيعية','صناعي وتشغيلي','ادراي','قانوني','اجتماعي وانساني','تصميمي','اقتصادي','إعلامي','بيئي','لوجيستي','صحي') NOT NULL,
  `requirements` text NOT NULL,
  `study_level` enum('بكالوريوس','ماجستير','دكاوراه') NOT NULL,
  `Specializations` text NOT NULL,
  `app_deadline` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bnf_off_msg`
--
ALTER TABLE `bnf_off_msg`
  ADD PRIMARY KEY (`msg_id`);

--
-- Indexes for table `e_contract`
--
ALTER TABLE `e_contract`
  ADD PRIMARY KEY (`contract_id`);

--
-- Indexes for table `scholarship_opps`
--
ALTER TABLE `scholarship_opps`
  ADD PRIMARY KEY (`scholarship_id`),
  ADD UNIQUE KEY `scholarship_id` (`scholarship_id`),
  ADD KEY `field_filter` (`sch_field`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bnf_off_msg`
--
ALTER TABLE `bnf_off_msg`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `e_contract`
--
ALTER TABLE `e_contract`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scholarship_opps`
--
ALTER TABLE `scholarship_opps`
  MODIFY `scholarship_id` int(50) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
