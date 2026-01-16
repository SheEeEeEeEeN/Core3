-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2026 at 04:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `core_slate1`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `otp_hash` varchar(255) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `otp_attempts` int(11) DEFAULT 0,
  `last_otp_sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `username`, `email`, `phone_number`, `gender`, `password`, `profile_image`, `role`, `created_at`, `otp_hash`, `otp_expires_at`, `otp_attempts`, `last_otp_sent_at`) VALUES
(102, 'Admin', 'core3slate@gmail.com', '09499508447', 'Male', '$2y$10$YSW42GsrLP/QYn/e2/pPfuGa83qHXREPmp5/or7CBHgYjPOubjvGO', NULL, 'admin', '2025-10-20 13:27:26', '$2y$10$thfKbEtWotbMgrdSfxuUT.Kw6dgDA.7V9j594Ig7gFeuqbQoUbLjq', '2026-01-15 14:30:03', 0, '2025-10-20 21:27:26'),
(116, 'Valle Roy', 'royzxcasd@gmail.com', '09499508447', 'Male', '$2y$10$.3k99oF4Q6A7lDfj6ZfYG.UHdcbTv2ixz.5.p4L/wN9F4VXP7Xj/S', 'upload/1764165219_me.jpg', 'user', '2025-10-23 15:30:07', '$2y$10$Zj8cSeZ1ZxDBYrtJbU/9WexbuurbPZTZQEoRSQ6llPrw8wS5FEOf.', '2026-01-13 06:37:17', 0, '2025-10-23 23:30:07'),
(117, 'JustineL', 'justinelusung11@gmail.com', '09168207165', 'Male', '$2y$10$R.4NzLZPHzbYffh6LZN29eV8N4YuDBNfiwNooVc1IqjaAHvSK76LO', NULL, 'admin', '2025-10-24 11:10:04', '$2y$10$SruuWiIEcQERvuhs5o486.1fHDGuIsQl7/7S8FcXY2w2HTQWNcr6u', '2026-01-02 20:34:20', 0, '2025-10-24 19:10:04'),
(118, 'Admin1', 'admin1@gmail.com', '09232131233', 'Male', '$2y$10$T6/.IQVU.RkLE4.c/Dz2QuxJLKlfDzOPUryPD5yzouEWKK9oHzmFG', NULL, 'user', '2025-10-26 12:59:05', '$2y$10$AXwY2X2jqu.tMEoMsUx0yuHUJEhvEmeCbiyjknftPHlIg/TLb/zqi', '2025-10-26 21:36:30', 0, '2025-10-26 20:59:05'),
(119, 'Test1', 'koxemop140@dwakm.com', '0973564362', 'Male', '$2y$10$6KtD/nqCLm4U2GwgYXuE6.HxYrNQQydw.JZ2UEHT96QY.3Pa0Jnze', NULL, 'user', '2025-11-01 09:56:40', '$2y$10$bA.UTuNJH5HWbjkdn8ZNF./uwvxr7wRjAglP9SM6IIjYpK5P5bRe.', '2025-11-01 18:01:53', 0, '2025-11-01 17:56:40'),
(120, 'Testtest', 'xadido9169@fergetic.com', '534537545644', 'Male', '$2y$10$xYu8TrAmC5fdqFsdifoGueO9hrp5iC6mjqzI9faeEvb9Fo7oMgoAm', 'upload/1762844369_reverse.gif', 'user', '2025-11-11 06:32:32', '$2y$10$x6VUd1GmqQ0jXX/sAqWkJ.Vg16KdOYlpLS/8FKOaV7keAOpyL/R9i', '2025-11-11 14:37:56', 0, '2025-11-11 14:32:32'),
(121, 'Asd', 'asd@gmail.com', '09232312321', 'Male', '$2y$10$3LLFAyjvsDrhJ03aHlPVB.zDcK2zeqt.V399hEVbxP6nw3ursyNtG', NULL, 'user', '2025-12-19 08:27:04', '$2y$10$4y8c1Vbv0HcxYXn/OkLW9O0e7mBFrHFJOE5DAlvALRqjwzl2EaI0.', '2025-12-19 16:32:47', 0, '2025-12-19 16:27:04'),
(122, 'ASS', 'bathanjc23@gmail.com', '09232312321', 'Male', '$2y$10$xoCxTbs43APEpsqiSKUwI.ostxbfYW7jkYYsfuWQt2X6/q/ChQjv2', 'upload/1766132977_Capture.PNG', 'user', '2025-12-19 08:28:38', '$2y$10$EUmj/FWmOIrWuViJlLJNR.6Bl9hW6djcEwUYwLNemJHM1pDFDa9HO', '2025-12-19 16:33:50', 0, '2025-12-19 16:28:38'),
(123, 'Joshuagarcia', 'gerrychogonzales1234+slate@gmail.com', '09913456789', 'Male', '$2y$10$5KiASna0i.Hkto4i6EXkSuiufVv.i4ibYbgTy5pFL5AqXPNPNCFyu', NULL, 'user', '2026-01-02 03:42:03', '$2y$10$0qBL6PyMr2vKyay73IBXMupzEFD0dk7nX4HNZdbaIPpCyFr8JM58K', '2026-01-02 12:33:55', 0, '2026-01-02 11:42:03'),
(124, 'AGHIK', 'vacamil400@24faw.com', '09131322312', 'Male', '$2y$10$0oYRDKmX25PfAQZZUDG6TejcH7tg9NQjr2a.XQQJs18T7Z/DQC6OC', 'upload/1767593930_Capture.PNG', 'user', '2026-01-05 06:17:16', '$2y$10$9ZoL3lgrhstdGANO5wYdz./f4ugWH.micR4/fUcp0sGligJxuD2xS', '2026-01-05 14:22:45', 0, '2026-01-05 14:17:16'),
(125, 'Olgab', 'olgabercasio18@gmail.com', '09612536291', 'Female', '$2y$10$MpEncw3mq5O.gq/NPJFCv.3DDu5Z..Hcbx4lrh/GDpoItQyzFfz4C', NULL, 'user', '2026-01-06 09:07:42', '$2y$10$CROHJoLgJPQ1MZQFoVsV8.C2ZC7fD4qCWfXj3o9ffHDDyYkkFN1MK', '2026-01-13 07:08:15', 0, '2026-01-06 17:07:42'),
(126, 'master', 'tangol@gmail.com', '09499503447', 'Male', 'cbf06754df2f70dd1f853bdccaec98cc6d8ba861a2a91d357540b9d561b6ceb7', NULL, '', '2026-01-14 11:18:09', NULL, NULL, 0, '2026-01-14 19:18:09'),
(127, 'Valle', 'valleroy851@gmail.com', '09439508447', 'Male', '$2y$10$xutepoMUSSTy2qRYhq9oFec3pSg/3kRWdK0KvPtgYUGVlk/MH92OW', NULL, 'user', '2026-01-14 11:21:57', '$2y$10$ZQgA1JnrxBr7DAHIHzr3HeOKDTSWWLtWFGGcX1jIJcZc0MnD6mS2G', '2026-01-14 12:29:19', 0, '2026-01-14 19:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `login_time` datetime NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `username`, `login_time`, `ip_address`, `user_agent`) VALUES
(37, 19, 'admin', '2025-09-22 06:34:23', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(38, 20, 'user1', '2025-09-22 06:39:20', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(39, 19, 'admin', '2025-09-22 06:39:38', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(40, 19, 'admin', '2025-09-22 06:54:25', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(41, 28, '123', '2025-09-22 07:01:49', '136.158.8.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(42, 19, 'admin', '2025-09-22 07:03:31', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(43, 20, 'user1', '2025-09-22 07:03:50', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(44, 20, 'user1', '2025-09-22 07:26:03', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(45, 20, 'user1', '2025-09-22 07:44:20', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(46, 20, 'user1', '2025-09-22 07:44:36', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(47, 20, 'user1', '2025-09-22 07:44:39', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(48, 20, 'user1', '2025-09-22 07:45:09', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(49, 20, 'user1', '2025-09-22 07:45:14', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(50, 20, 'user1', '2025-09-22 07:45:45', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(51, 20, 'user1', '2025-09-22 07:46:36', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(52, 20, 'user1', '2025-09-22 07:47:36', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(53, 19, 'admin', '2025-09-22 07:48:25', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(54, 20, 'user1', '2025-09-22 07:49:06', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(55, 20, 'user1', '2025-09-22 07:49:58', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(56, 20, 'user1', '2025-09-22 07:51:26', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(57, 20, 'user1', '2025-09-22 07:52:22', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(58, 20, 'user1', '2025-09-22 07:54:33', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(59, 20, 'user1', '2025-09-22 07:58:03', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(60, 19, 'admin', '2025-09-22 08:11:49', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(61, 29, 'user2', '2025-09-22 08:30:21', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(62, 29, 'user2', '2025-09-22 08:32:53', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(63, 19, 'admin', '2025-09-22 08:39:46', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(64, 29, 'user2', '2025-09-22 08:41:07', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(65, 19, 'admin', '2025-09-22 09:27:00', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(66, 19, 'admin', '2025-09-22 10:03:06', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(67, 20, 'user1', '2025-09-22 10:04:41', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(68, 19, 'admin', '2025-09-22 10:05:58', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(69, 20, 'user1', '2025-09-22 10:08:26', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(70, 33, 'Last5', '2025-09-22 10:10:18', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(71, 33, 'Last5', '2025-09-22 10:15:31', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(72, 20, 'user1', '2025-09-22 10:19:45', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(73, 34, 'Last', '2025-09-22 10:27:49', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(74, 35, 'Last3', '2025-09-22 10:59:22', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(75, 35, 'Last3', '2025-09-22 10:59:58', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(76, 33, 'Last5', '2025-09-22 11:00:22', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(77, 35, 'Last3', '2025-09-22 11:00:40', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(78, 19, 'admin', '2025-09-22 11:17:57', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(79, 19, 'admin', '2025-09-22 11:18:12', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(80, 19, 'admin', '2025-09-22 11:22:45', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(81, 19, 'admin', '2025-09-22 11:27:04', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(82, 20, 'user1', '2025-09-22 11:28:14', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(83, 19, 'admin', '2025-09-22 11:28:29', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(84, 33, 'Last5', '2025-09-22 11:28:53', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(85, 20, 'user1', '2025-09-22 11:29:15', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(86, 19, 'admin', '2025-09-22 11:45:31', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(87, 19, 'admin', '2025-09-22 12:03:15', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(88, 20, 'user1', '2025-09-22 12:03:26', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(89, 19, 'admin', '2025-09-22 12:23:38', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(90, 20, 'user1', '2025-09-22 12:23:50', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(91, 19, 'admin', '2025-09-22 12:29:38', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(92, 20, 'user1', '2025-09-22 12:29:49', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(93, 19, 'admin', '2025-09-22 12:42:30', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0'),
(94, 19, 'admin', '2025-09-22 12:52:53', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(95, 36, 'Defense', '2025-09-22 12:56:25', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(96, 19, 'admin', '2025-09-22 12:57:55', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(97, 36, 'Defense', '2025-09-22 13:02:41', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(98, 19, 'admin', '2025-09-22 13:03:44', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(99, 19, 'admin', '2025-09-22 13:16:42', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(100, 19, 'admin', '2025-09-23 02:06:04', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(101, 20, 'user1', '2025-09-23 02:08:47', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(102, 19, 'admin', '2025-09-23 02:08:59', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(103, 19, 'admin', '2025-09-23 02:25:04', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(104, 19, 'admin', '2025-09-23 10:39:11', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(105, 19, 'admin', '2025-09-23 12:05:26', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(106, 20, 'user1', '2025-09-23 12:45:11', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(107, 19, 'admin', '2025-09-23 12:47:03', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(108, 19, 'admin', '2025-09-23 12:52:02', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(109, 19, 'admin', '2025-09-23 13:05:08', '175.176.27.88', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(110, 19, 'admin', '2025-09-23 15:29:24', '175.176.27.88', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(111, 19, 'admin', '2025-09-23 15:49:53', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(112, 20, 'user1', '2025-09-23 17:36:29', '175.176.29.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(113, 20, 'user1', '2025-09-23 17:36:29', '175.176.29.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(114, 19, 'admin', '2025-09-23 17:37:31', '175.176.29.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(115, 19, 'admin', '2025-09-23 17:37:31', '175.176.29.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(116, 19, 'admin', '2025-09-23 17:44:30', '175.176.29.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(117, 19, 'admin', '2025-09-23 20:03:14', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(118, 20, 'user1', '2025-09-23 21:20:18', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(119, 19, 'admin', '2025-09-23 21:39:07', '175.176.29.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(120, 29, 'user2', '2025-09-23 22:13:58', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(121, 20, 'user1', '2025-09-23 22:14:16', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(122, 19, 'admin', '2025-09-23 22:24:08', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(123, 29, 'user2', '2025-09-23 22:29:42', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(124, 19, 'admin', '2025-09-23 22:33:28', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(125, 19, 'admin', '2025-09-23 23:09:53', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(126, 19, 'admin', '2025-09-23 23:12:48', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(127, 20, 'user1', '2025-09-24 01:38:19', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(128, 29, 'user2', '2025-09-24 01:38:36', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(129, 19, 'admin', '2025-09-24 01:38:54', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(130, 19, 'admin', '2025-09-24 11:51:52', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(131, 20, 'user1', '2025-09-24 13:22:09', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(132, 19, 'admin', '2025-09-24 13:27:58', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(133, 20, 'user1', '2025-09-24 15:31:07', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(134, 29, 'user2', '2025-09-24 15:45:16', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(135, 20, 'user1', '2025-09-24 15:45:51', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(136, 29, 'user2', '2025-09-24 15:56:35', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(137, 19, 'admin', '2025-09-24 16:10:31', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(138, 20, 'user1', '2025-09-24 16:13:30', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(139, 19, 'admin', '2025-09-24 16:28:03', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(140, 19, 'admin', '2025-09-24 17:20:15', '175.176.27.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(141, 29, 'user2', '2025-09-24 18:34:03', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(142, 19, 'admin', '2025-09-24 18:36:03', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(143, 19, 'admin', '2025-09-24 20:23:33', '175.176.27.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(144, 19, 'admin', '2025-09-25 00:17:52', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(145, 19, 'admin', '2025-09-25 00:21:24', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(146, 19, 'admin', '2025-09-25 00:21:41', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(147, 19, 'admin', '2025-09-25 00:23:16', '175.176.27.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(148, 19, 'admin', '2025-09-25 00:23:34', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(149, 20, 'user1', '2025-09-25 00:25:46', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(150, 19, 'admin', '2025-09-25 00:28:05', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(151, 19, 'admin', '2025-09-25 00:28:48', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(152, 19, 'admin', '2025-09-25 00:36:17', '175.176.27.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(153, 20, 'user1', '2025-09-25 00:39:20', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(154, 19, 'admin', '2025-09-25 00:42:50', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(155, 20, 'user1', '2025-09-25 00:46:10', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(156, 19, 'admin', '2025-09-25 00:46:33', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(157, 20, 'user1', '2025-09-25 00:51:38', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(158, 29, 'user2', '2025-09-25 00:52:16', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(159, 19, 'admin', '2025-09-25 00:52:55', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(160, 20, 'user1', '2025-09-25 00:53:59', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(161, 29, 'user2', '2025-09-25 00:54:29', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(162, 19, 'admin', '2025-09-25 00:57:12', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(163, 20, 'user1', '2025-09-25 01:37:45', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(164, 19, 'admin', '2025-09-25 15:14:47', '175.176.27.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(165, 19, 'admin', '2025-09-26 01:04:46', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(166, 19, 'admin', '2025-09-26 01:54:29', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(167, 19, 'admin', '2025-09-26 02:06:44', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(168, 20, 'user1', '2025-09-26 02:09:07', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(169, 20, 'user1', '2025-09-26 03:18:22', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(170, 20, 'user1', '2025-09-26 03:24:44', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(171, 20, 'user1', '2025-09-26 03:31:53', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(172, 19, 'admin', '2025-09-26 03:43:15', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(173, 20, 'user1', '2025-09-26 03:43:31', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(174, 20, 'user1', '2025-09-26 03:45:33', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(175, 20, 'user1', '2025-09-26 04:01:05', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(176, 20, 'user1', '2025-09-26 04:04:12', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(177, 20, 'user1', '2025-09-26 08:40:56', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(178, 20, 'user1', '2025-09-26 08:42:50', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(179, 20, 'user1', '2025-09-26 08:45:20', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(180, 19, 'admin', '2025-09-26 08:47:12', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(181, 44, 'Try', '2025-09-26 08:49:01', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(182, 20, 'user1', '2025-09-26 08:56:54', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(183, 44, 'Try', '2025-09-26 08:59:05', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(184, 20, 'user1', '2025-09-26 09:15:20', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(185, 20, 'user1', '2025-09-26 09:31:37', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(186, 19, 'admin', '2025-09-26 09:40:17', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(187, 20, 'user1', '2025-09-26 10:05:11', '136.158.2.154', 'Mozilla/5.0 (Linux; Android 11; CPH1969 Build/RP1A.200720.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/140.0.7339.51 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/525.0.0.44.108;]'),
(188, 20, 'user1', '2025-09-26 11:26:01', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(189, 19, 'admin', '2025-09-26 11:29:55', '175.176.27.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(190, 25, 'olga', '2025-09-26 12:43:52', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(191, 20, 'user1', '2025-09-26 12:56:22', '175.176.27.53', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(192, 25, 'olga', '2025-09-26 13:00:38', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(193, 19, 'admin', '2025-09-26 13:13:46', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(194, 19, 'admin', '2025-09-26 14:00:54', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(195, 19, 'admin', '2025-09-26 14:01:52', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(196, 19, 'admin', '2025-09-26 14:06:27', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(197, 20, 'user1', '2025-09-26 15:58:21', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(198, 19, 'admin', '2025-09-26 16:06:05', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(199, 57, 'Captcha4', '2025-09-26 16:13:13', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(200, 58, 'Captcha5', '2025-09-26 16:15:02', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(201, 20, 'user1', '2025-09-26 16:43:14', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(202, 20, 'user1', '2025-09-26 16:50:42', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(203, 25, 'olga', '2025-09-26 17:25:44', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(204, 20, 'user1', '2025-09-26 17:29:52', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(205, 25, 'olga', '2025-09-26 17:35:44', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(206, 25, 'olga', '2025-09-26 18:04:31', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(207, 25, 'olga', '2025-09-26 18:09:22', '136.158.8.212', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36'),
(208, 25, 'olga', '2025-09-26 18:10:07', '136.158.8.212', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36'),
(209, 71, 'Captcha12123dsa', '2025-09-26 18:53:39', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(210, 20, 'user1', '2025-09-26 19:37:06', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(211, 21, 'roy', '2025-09-26 19:38:35', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(212, 21, 'roy', '2025-09-26 19:40:31', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(213, 21, 'roy', '2025-09-26 19:47:34', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(214, 25, 'olga', '2025-09-26 20:02:55', '136.158.8.212', 'Mozilla/5.0 (Linux; Android 12; RMX3690 Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/140.0.7339.51 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/525.0.0.44.108;]'),
(215, 21, 'roy', '2025-09-26 20:16:02', '175.176.26.133', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(216, 19, 'admin', '2025-09-26 20:56:37', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(217, 21, 'roy', '2025-09-26 21:29:17', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(218, 21, 'roy', '2025-09-26 21:44:54', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(219, 21, 'roy', '2025-09-26 21:48:32', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(220, 21, 'roy', '2025-09-26 21:49:10', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(221, 21, 'roy', '2025-09-26 21:53:13', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(222, 21, 'roy', '2025-09-26 21:55:28', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(223, 72, 'Elmo', '2025-09-26 21:57:19', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(224, 73, 'Teodora', '2025-09-26 22:02:41', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(225, 73, 'Teodora', '2025-09-26 22:06:56', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(226, 73, 'Teodora', '2025-09-26 22:08:25', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(227, 21, 'roy', '2025-09-26 22:12:35', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(228, 21, 'roy', '2025-09-26 22:14:54', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(229, 74, 'JustineL', '2025-09-26 22:15:43', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(230, 19, 'admin', '2025-09-26 22:21:19', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(231, 19, 'admin', '2025-09-26 22:41:45', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(232, 21, 'roy', '2025-09-26 22:44:16', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(233, 19, 'admin', '2025-09-26 23:03:17', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(234, 19, 'admin', '2025-09-26 23:12:34', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(235, 21, 'roy', '2025-09-26 23:13:04', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(236, 74, 'JustineL', '2025-09-26 23:30:40', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(237, 19, 'admin', '2025-09-26 23:36:35', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(238, 19, 'admin', '2025-09-26 23:39:46', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(239, 74, 'JustineL', '2025-09-26 23:44:30', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(240, 19, 'admin', '2025-09-26 23:45:33', '136.158.8.212', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36'),
(241, 19, 'admin', '2025-09-26 23:46:45', '136.158.8.212', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36'),
(242, 19, 'admin', '2025-09-26 23:47:25', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(243, 25, 'olga', '2025-09-26 23:48:40', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(244, 25, 'olga', '2025-09-26 23:49:15', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(245, 25, 'olga', '2025-09-26 23:59:17', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(246, 19, 'admin', '2025-09-27 00:06:54', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(247, 19, 'admin', '2025-09-27 00:14:42', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(248, 19, 'admin', '2025-09-27 00:18:58', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(249, 74, 'JustineL', '2025-09-27 00:22:45', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(250, 74, 'JustineL', '2025-09-27 00:28:47', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(251, 74, 'JustineL', '2025-09-27 00:58:23', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(252, 19, 'admin', '2025-09-27 01:19:26', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(253, 19, 'admin', '2025-09-27 01:37:38', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(254, 19, 'admin', '2025-09-27 01:37:39', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(255, 74, 'JustineL', '2025-09-27 01:39:04', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(256, 19, 'admin', '2025-09-27 01:49:09', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(257, 19, 'admin', '2025-09-27 02:18:29', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(258, 19, 'admin', '2025-09-27 02:33:10', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(259, 20, 'user1', '2025-09-27 02:33:24', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(260, 74, 'JustineL', '2025-09-27 02:33:44', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(261, 25, 'olga', '2025-09-27 02:37:03', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(262, 25, 'olga', '2025-09-27 02:40:32', '136.158.8.212', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(263, 80, 'Justine231', '2025-09-27 02:58:36', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(264, 74, 'JustineL', '2025-09-27 03:07:21', '136.158.2.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(265, 82, 'Game', '2025-09-27 05:52:32', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(266, 19, 'admin', '2025-09-27 05:55:23', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(267, 19, 'admin', '2025-09-27 05:58:25', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(268, 83, 'Tsisjanwn', '2025-09-27 06:57:10', '112.204.161.240', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Mobile Safari/537.36 ABB/133.0.6943.51'),
(269, 21, 'roy', '2025-09-27 11:00:37', '175.176.26.133', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(270, 25, 'olga', '2025-09-27 13:59:57', '136.158.8.52', 'Mozilla/5.0 (Linux; Android 9; JKM-LX2 Build/HUAWEIJKM-LX2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.179 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/525.0.0.44.108;]'),
(271, 19, 'admin', '2025-09-27 16:51:35', '175.176.26.133', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(272, 84, 'Cherie', '2025-09-27 19:58:47', '175.176.26.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(273, 19, 'admin', '2025-09-28 08:21:48', '175.176.27.219', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(274, 21, 'roy', '2025-09-28 08:27:38', '175.176.27.219', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(275, 25, 'olga', '2025-09-28 10:53:39', '136.158.8.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(276, 21, 'roy', '2025-09-28 16:11:26', '136.158.63.247', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(277, 25, 'olga', '2025-09-29 02:25:32', '136.158.8.52', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(278, 19, 'admin', '2025-09-29 05:55:57', '175.176.29.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(279, 25, 'olga', '2025-09-30 18:45:46', '175.176.19.199', 'Mozilla/5.0 (Linux; Android 9; JKM-LX2 Build/HUAWEIJKM-LX2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.179 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/526.0.0.52.108;]'),
(280, 19, 'admin', '2025-10-01 09:17:48', '175.176.24.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(281, 21, 'roy', '2025-10-05 22:31:04', '175.176.27.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(282, 19, 'admin', '2025-10-07 18:59:24', '175.176.17.226', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(283, 19, 'admin', '2025-10-09 22:15:56', '175.176.27.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(284, 19, 'admin', '2025-10-13 17:38:41', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(285, 21, 'roy', '2025-10-13 17:45:53', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(286, 19, 'admin', '2025-10-14 06:51:54', '175.176.27.177', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(287, 21, 'roy', '2025-10-14 06:52:36', '175.176.27.177', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(288, 21, 'roy', '2025-10-14 06:52:42', '175.176.27.177', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(289, 19, 'admin', '2025-10-14 06:52:59', '175.176.27.177', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(290, 19, 'admin', '2025-10-14 12:40:25', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(291, 21, 'roy', '2025-10-14 12:41:51', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(292, 21, 'roy', '2025-10-14 16:25:40', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(293, 85, 'Roy', '2025-10-14 16:35:27', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(294, 85, 'Roy', '2025-10-14 23:04:56', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(295, 85, 'Roy', '2025-10-15 07:31:54', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(296, 85, 'Roy', '2025-10-15 08:57:48', '175.176.27.177', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(297, 19, 'admin', '2025-10-15 11:00:53', '175.176.24.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(298, 85, 'Roy', '2025-10-15 12:11:19', '175.176.24.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(299, 86, 'User1', '2025-10-15 12:17:03', '175.176.24.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(300, 85, 'Roy', '2025-10-15 15:04:18', '175.176.24.56', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(301, 85, 'Roy', '2025-10-15 18:05:36', '175.176.24.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(302, 19, 'admin', '2025-10-15 20:43:25', '175.176.24.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(303, 85, 'Roy', '2025-10-15 21:19:12', '175.176.24.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(304, 85, 'Roy', '2025-10-16 06:09:45', '175.176.24.56', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(305, 85, 'Roy', '2025-10-16 20:05:30', '175.176.24.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(306, 19, 'admin', '2025-10-16 20:09:18', '175.176.24.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(307, 85, 'Roy', '2025-10-17 07:40:38', '175.176.24.153', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(308, 85, 'Roy', '2025-10-17 07:53:35', '175.176.24.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(309, 86, 'User1', '2025-10-17 08:14:12', '175.176.24.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(310, 85, 'Roy', '2025-10-17 10:10:25', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(311, 19, 'admin', '2025-10-17 10:22:42', '136.158.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(312, 85, 'Roy', '2025-10-17 10:59:36', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(313, 87, 'User1', '2025-10-17 13:06:17', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(314, 19, 'admin', '2025-10-17 13:29:38', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(315, 85, 'Roy', '2025-10-17 14:11:06', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(316, 19, 'admin', '2025-10-17 14:13:04', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(317, 85, 'Roy', '2025-10-17 14:15:24', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(318, 85, 'Roy', '2025-10-17 14:45:27', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(319, 85, 'Roy', '2025-10-17 17:12:10', '175.176.27.222', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(320, 85, 'Roy', '2025-10-17 20:09:26', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(321, 87, 'User1', '2025-10-17 20:15:49', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(322, 19, 'admin', '2025-10-17 20:33:39', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(323, 19, 'admin', '2025-10-17 22:00:47', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(324, 19, 'admin', '2025-10-18 07:13:44', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(325, 85, 'Roy', '2025-10-18 09:06:09', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(326, 85, 'Roy', '2025-10-18 10:38:23', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36');
INSERT INTO `activity_log` (`id`, `user_id`, `username`, `login_time`, `ip_address`, `user_agent`) VALUES
(327, 19, 'admin', '2025-10-18 11:01:19', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(328, 85, 'Roy', '2025-10-18 15:57:55', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(329, 19, 'admin', '2025-10-18 17:47:50', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(330, 85, 'Roy', '2025-10-18 20:05:38', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(331, 19, 'admin', '2025-10-18 20:30:20', '136.158.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(332, 88, 'JustineL', '2025-10-18 20:55:58', '136.158.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(333, 19, 'admin', '2025-10-18 21:56:30', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(334, 85, 'Roy', '2025-10-18 21:59:48', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(335, 85, 'Roy', '2025-10-18 21:59:49', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(336, 25, 'olga', '2025-10-18 22:05:04', '136.158.7.206', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(337, 85, 'Roy', '2025-10-19 05:33:49', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(338, 19, 'admin', '2025-10-19 05:36:40', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(339, 85, 'Roy', '2025-10-19 05:49:47', '175.176.27.222', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(340, 19, 'admin', '2025-10-19 05:50:22', '175.176.27.222', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(341, 85, 'Roy', '2025-10-19 05:51:11', '175.176.27.222', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(342, 25, 'olga', '2025-10-19 09:28:39', '136.158.7.206', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(343, 85, 'Roy', '2025-10-19 12:48:33', '175.176.27.222', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(344, 19, 'admin', '2025-10-19 16:41:40', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(345, 85, 'Roy', '2025-10-19 17:11:37', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(346, 19, 'admin', '2025-10-19 17:15:54', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(347, 88, 'JustineL', '2025-10-19 17:27:27', '2001:fd8:412:3dea:bc54:6f57:4e27:c417', 'Mozilla/5.0 (Linux; Android 11; CPH1969 Build/RP1A.200720.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/140.0.7339.207 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/528.0.0.62.107;]'),
(348, 85, 'Roy', '2025-10-19 18:05:41', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(349, 85, 'Roy', '2025-10-19 18:52:16', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(350, 85, 'Roy', '2025-10-19 22:31:13', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(351, 19, 'admin', '2025-10-19 22:32:11', '175.176.27.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(352, 25, 'olga', '2025-10-20 10:52:46', '175.176.17.142', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/529.0.0.30.108;FBBV/806382991;FBDV/iPhone12,1;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/2;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]'),
(353, 25, 'olga', '2025-10-20 10:52:47', '175.176.17.142', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/529.0.0.30.108;FBBV/806382991;FBDV/iPhone12,1;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/2;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]'),
(354, 25, 'olga', '2025-10-20 10:54:13', '175.176.17.142', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/529.0.0.30.108;FBBV/806382991;FBDV/iPhone12,1;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/2;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]'),
(355, 88, 'JustineL', '2025-10-20 10:55:08', '136.158.2.252', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(356, 19, 'admin', '2025-10-20 10:56:49', '136.158.2.252', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(357, 91, 'Cammy', '2025-10-20 10:57:16', '175.176.17.142', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(358, 85, 'Roy', '2025-10-20 15:46:09', '175.176.24.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(359, 19, 'admin', '2025-10-20 15:47:07', '175.176.24.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(360, 88, 'JustineL', '2025-10-20 15:52:06', '175.176.20.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(361, 25, 'olga', '2025-10-20 18:46:44', '136.158.7.206', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(362, 85, 'Roy', '2025-10-20 19:15:19', '175.176.24.23', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(363, 85, 'Roy', '2025-10-20 19:28:47', '2001:4451:454c:6400:f8ae:925:b7ad:5706', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(364, 88, 'JustineL', '2025-10-20 19:42:12', '136.158.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(365, 85, 'Roy', '2025-10-20 20:13:15', '175.176.24.23', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(366, 85, 'Roy', '2025-10-20 21:24:54', '175.176.24.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(367, 102, 'Admin', '2025-10-20 21:27:40', '175.176.24.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(368, 85, 'Roy', '2025-10-20 21:31:02', '175.176.24.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(369, 85, 'Roy', '2025-10-21 08:59:51', '112.198.27.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(370, 102, 'Admin', '2025-10-21 09:08:56', '112.198.27.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(371, 85, 'Roy', '2025-10-21 14:38:51', '175.176.24.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(372, 102, 'Admin', '2025-10-21 14:41:28', '175.176.24.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(373, 102, 'Admin', '2025-10-21 15:25:29', '175.176.24.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(374, 85, 'Roy', '2025-10-21 15:27:43', '175.176.24.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(375, 85, 'Roy', '2025-10-21 17:07:22', '175.176.24.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(376, 88, 'JustineL', '2025-10-21 17:16:52', '175.176.16.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(377, 88, 'JustineL', '2025-10-21 23:09:37', '136.158.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(378, 85, 'Roy', '2025-10-22 12:25:52', '175.176.24.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(379, 85, 'Roy', '2025-10-22 21:37:11', '175.176.27.124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(380, 102, 'Admin', '2025-10-22 22:41:16', '175.176.27.124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(381, 85, 'Roy', '2025-10-23 05:27:07', '175.176.27.124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(382, 85, 'Roy', '2025-10-23 06:36:26', '175.176.27.124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(383, 25, 'olga', '2025-10-23 08:36:49', '136.158.7.206', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(384, 85, 'Roy', '2025-10-23 10:23:36', '175.176.27.124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(385, 85, 'Roy', '2025-10-23 13:13:14', '175.176.27.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(386, 85, 'Roy', '2025-10-23 13:37:14', '175.176.27.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(387, 102, 'Admin', '2025-10-23 14:02:54', '175.176.27.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(388, 102, 'Admin', '2025-10-23 22:56:12', '175.176.27.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(389, 116, 'Roy', '2025-10-23 23:30:13', '175.176.27.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(390, 102, 'Admin', '2025-10-24 16:50:13', '175.176.27.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(391, 117, 'JustineL', '2025-10-24 19:10:17', '136.158.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(392, 117, 'JustineL', '2025-10-24 19:16:36', '136.158.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'),
(393, 116, 'Roy', '2025-10-25 19:49:47', '175.176.24.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(394, 116, 'Roy', '2025-10-25 20:54:12', '175.176.24.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(395, 102, 'Admin', '2025-10-26 07:45:15', '175.176.24.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(396, 116, 'Roy', '2025-10-26 07:53:25', '175.176.24.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(397, 102, 'Admin', '2025-10-26 07:54:59', '175.176.24.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(398, 116, 'Roy', '2025-10-26 09:35:11', '209.35.165.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(399, 116, 'Roy', '2025-10-26 09:46:49', '209.35.165.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(400, 116, 'Roy', '2025-10-26 09:47:04', '209.35.165.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(401, 102, 'Admin', '2025-10-26 09:48:37', '209.35.165.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(402, 116, 'Roy', '2025-10-26 10:09:29', '175.176.16.58', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(403, 116, 'Roy', '2025-10-26 10:28:38', '175.176.16.58', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(404, 102, 'Admin', '2025-10-26 10:39:58', '175.176.16.58', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(405, 118, 'Admin1', '2025-10-26 21:31:08', '136.158.37.143', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(406, 116, 'Roy', '2025-10-27 20:02:27', '175.176.24.83', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(407, 116, 'Roy', '2025-10-28 14:25:15', '175.176.24.150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(408, 116, 'Roy', '2025-10-28 21:14:08', '175.176.24.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(409, 116, 'Roy', '2025-10-29 08:23:18', '175.176.24.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(410, 102, 'Admin', '2025-10-29 08:26:10', '175.176.24.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(411, 116, 'Roy Valle', '2025-10-29 09:02:44', '175.176.24.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(412, 116, 'Valle Roy', '2025-10-29 09:04:42', '175.176.24.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(413, 116, 'Valle Roy', '2025-10-29 16:10:47', '175.176.24.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(414, 119, 'Test1', '2025-11-01 17:56:49', '112.203.160.88', 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0'),
(415, 120, 'Testtest', '2025-11-11 14:32:52', '112.203.160.138', 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0'),
(416, 116, 'Valle Roy', '2025-11-19 21:39:36', '175.176.24.118', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(417, 116, 'Valle Roy', '2025-11-23 21:39:23', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(418, 116, 'Valle Roy', '2025-11-26 21:49:21', '175.176.29.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(419, 102, 'Admin', '2025-11-26 21:55:30', '175.176.29.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(420, 116, 'Valle Roy', '2025-11-27 16:06:23', '120.28.136.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(421, 116, 'Valle Roy', '2025-11-30 19:17:53', '112.203.61.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(422, 116, 'Valle Roy', '2025-11-30 19:18:15', '112.203.61.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(423, 102, 'Admin', '2025-12-02 13:09:11', '175.176.27.14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(424, 116, 'Valle Roy', '2025-12-02 13:10:28', '175.176.27.14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(425, 116, 'Valle Roy', '2025-12-04 09:17:18', '175.176.16.149', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(426, 102, 'Admin', '2025-12-10 17:57:27', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(427, 102, 'Admin', '2025-12-10 22:40:40', '112.203.61.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(428, 102, 'Admin', '2025-12-11 17:30:35', '112.203.61.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(429, 102, 'Admin', '2025-12-12 23:21:50', '120.28.136.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(430, 116, 'Valle Roy', '2025-12-19 11:26:17', '112.203.61.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(431, 121, 'Asd', '2025-12-19 16:27:34', '180.191.32.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0'),
(432, 122, 'ASS', '2025-12-19 16:28:48', '180.191.32.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0'),
(433, 116, 'Valle Roy', '2025-12-19 18:41:31', '112.203.61.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(434, 116, 'Valle Roy', '2025-12-19 22:27:04', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(435, 116, 'Valle Roy', '2025-12-20 09:43:02', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(436, 116, 'Valle Roy', '2025-12-20 11:59:58', '175.176.27.245', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(437, 116, 'Valle Roy', '2025-12-20 14:28:34', '175.176.27.245', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(438, 116, 'Valle Roy', '2025-12-20 20:35:46', '120.28.136.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(439, 116, 'Valle Roy', '2025-12-20 22:46:16', '175.176.24.45', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(440, 116, 'Valle Roy', '2025-12-22 13:33:16', '175.176.24.45', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(441, 116, 'Valle Roy', '2025-12-22 18:22:22', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(442, 116, 'Valle Roy', '2025-12-22 18:47:30', '112.200.113.184', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(443, 116, 'Valle Roy', '2025-12-22 19:53:44', '112.200.113.184', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(444, 116, 'Valle Roy', '2025-12-23 17:39:02', '112.200.113.184', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(445, 102, 'Admin', '2025-12-27 14:03:15', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(446, 116, 'Valle Roy', '2025-12-29 22:18:32', '175.176.27.116', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(447, 116, 'Valle Roy', '2025-12-31 11:22:36', '175.176.27.19', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(448, 116, 'Valle Roy', '2026-01-02 11:40:12', '112.198.27.7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(449, 123, 'Joshuagarcia', '2026-01-02 11:42:56', '112.202.247.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(450, 116, 'Valle Roy', '2026-01-02 12:00:15', '112.198.27.7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(451, 123, 'Joshuagarcia', '2026-01-02 12:28:51', '112.203.220.139', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(452, 117, 'JustineL', '2026-01-02 20:29:14', '136.158.33.189', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0'),
(453, 116, 'Valle Roy', '2026-01-02 22:15:02', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(454, 102, 'Admin', '2026-01-02 22:20:03', '120.28.136.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(455, 102, 'Admin', '2026-01-02 23:09:08', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(456, 102, 'Admin', '2026-01-02 23:09:56', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(457, 116, 'Valle Roy', '2026-01-03 23:21:11', '120.28.136.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(458, 102, 'Admin', '2026-01-04 21:17:35', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(459, 116, 'Valle Roy', '2026-01-04 22:23:38', '175.176.28.233', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(460, 116, 'Valle Roy', '2026-01-05 05:20:48', '175.176.28.42', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(461, 116, 'Valle Roy', '2026-01-05 06:47:46', '112.198.27.2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(462, 116, 'Valle Roy', '2026-01-05 13:35:48', '112.198.27.2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(463, 124, 'AGHIK', '2026-01-05 14:17:43', '180.191.32.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0'),
(464, 116, 'Valle Roy', '2026-01-05 17:25:37', '120.28.136.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(465, 116, 'Valle Roy', '2026-01-05 17:50:25', '112.200.113.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(466, 116, 'Valle Roy', '2026-01-06 10:05:24', '112.200.113.184', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(467, 116, 'Valle Roy', '2026-01-06 10:39:48', '112.200.113.184', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(468, 116, 'Valle Roy', '2026-01-06 12:52:53', '2405:8d40:484c:4c11:b0f4:ede3:fa74:e1be', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(469, 116, 'Valle Roy', '2026-01-06 13:23:39', '175.158.203.35', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(470, 125, 'Olgab', '2026-01-06 17:08:37', '152.32.100.62', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(471, 116, 'Valle Roy', '2026-01-07 18:10:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(472, 116, 'Valle Roy', '2026-01-08 17:32:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(473, 102, 'Admin', '2026-01-09 09:54:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(474, 116, 'Valle Roy', '2026-01-09 10:13:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(475, 102, 'Admin', '2026-01-09 12:32:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(476, 116, 'Valle Roy', '2026-01-10 10:12:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(477, 116, 'Valle Roy', '2026-01-10 10:22:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(478, 116, 'Valle Roy', '2026-01-10 11:01:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(479, 102, 'Admin', '2026-01-10 11:33:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(480, 116, 'Valle Roy', '2026-01-10 12:13:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(481, 102, 'Admin', '2026-01-10 12:15:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(482, 116, 'Valle Roy', '2026-01-10 15:04:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(483, 116, 'Valle Roy', '2026-01-10 23:13:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(484, 102, 'Admin', '2026-01-10 23:14:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(485, 116, 'Valle Roy', '2026-01-11 09:06:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(486, 102, 'Admin', '2026-01-11 09:06:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(487, 102, 'Admin', '2026-01-11 14:21:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(488, 116, 'Valle Roy', '2026-01-11 15:22:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(489, 102, 'Admin', '2026-01-12 13:11:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(490, 116, 'Valle Roy', '2026-01-13 03:13:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(491, 102, 'Admin', '2026-01-13 03:16:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(492, 116, 'Valle Roy', '2026-01-13 03:24:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(493, 102, 'Admin', '2026-01-13 06:27:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(494, 116, 'Valle Roy', '2026-01-13 06:32:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(495, 125, 'Olgab', '2026-01-13 07:01:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(496, 125, 'Olgab', '2026-01-13 07:01:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(497, 102, 'Admin', '2026-01-14 11:13:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(498, 127, 'Valle', '2026-01-14 12:24:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(499, 102, 'Admin', '2026-01-14 12:28:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(500, 102, 'Admin', '2026-01-15 14:25:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity`
--

CREATE TABLE `admin_activity` (
  `id` int(11) NOT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `module` varchar(100) NOT NULL,
  `activity` text NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity`
--

INSERT INTO `admin_activity` (`id`, `date`, `module`, `activity`, `status`) VALUES
(3, '2025-09-01 19:02:00', 'CRM', 'Added new customer: nemon (nemon.corp)', 'Success'),
(5, '2025-09-01 19:09:26', 'CRM', 'Deleted customer: bhrruuu4 (PIHAN4)', 'Success'),
(6, '2025-09-01 19:09:47', 'CRM', 'Updated customer: TJK (DJK)', 'Success'),
(7, '2025-09-01 19:22:26', 'CRM', 'Added new customer: neee (neee.corp)', 'Success'),
(8, '2025-09-01 19:58:12', 'CRM', 'Updated customer: neee (neee.corp)', 'Success'),
(9, '2025-09-01 19:58:27', 'CRM', 'Deleted customer: dsad (PKG)', 'Success'),
(10, '2025-09-01 19:59:02', 'CRM', 'Added new customer: santa (santa)', 'Success'),
(11, '2025-09-01 20:01:53', 'CRM', 'Updated customer: Pota (Pota.corp)', 'Success'),
(12, '2025-09-01 20:01:55', 'CRM', 'Updated customer: Pota (Pota.corp)', 'Success'),
(13, '2025-09-01 20:03:42', 'CRM', 'Updated customer: tryt (try.corp342)', 'Success'),
(14, '2025-09-01 20:18:21', 'CRM', 'Updated customer ID 89 → nemon (nemon.corp)', 'Success'),
(15, '2025-09-01 20:18:23', 'CRM', 'Updated customer ID 92 → santa (santa)', 'Success'),
(16, '2025-09-01 23:48:10', 'CRM', 'Deleted customer: 123 (123)', 'Success'),
(17, '2025-09-02 00:10:39', 'CRM', 'Added new customer: tr (tr)', 'Success'),
(18, '2025-09-02 00:10:53', 'CRM', 'Updated customer ID 3: tr (tr)', 'Success'),
(19, '2025-09-02 00:16:23', 'CSM', 'Added new contract: tr - tr', 'Success'),
(20, '2025-09-02 00:17:24', 'CSM', 'Added new contract: tru - tru', 'Success'),
(21, '2025-09-02 00:20:30', 'CSM', 'Added new contract: 786876876 - gygyh', 'Success'),
(22, '2025-09-02 00:40:48', 'E-Documentation', 'Uploaded document: try', 'Pending Review'),
(23, '2025-09-02 00:41:35', 'E-Documentation', 'Edited document: try', 'Pending Review'),
(24, '2025-09-02 00:41:46', 'E-Documentation', 'Deleted document: try', 'Deleted'),
(25, '2025-09-02 03:38:55', 'CRM', 'Updated customer: tr (tr)', 'Success'),
(26, '2025-09-02 03:39:22', 'CRM', 'Added new customer: try (try)', 'Success'),
(27, '2025-09-02 03:39:41', 'CRM', 'Deleted customer: tr (tr)', 'Success'),
(28, '2025-09-02 03:40:56', 'CSM', 'Added new contract: 22052118321 - ulet', 'Success'),
(29, '2025-09-02 03:41:46', 'E-Documentation', 'Uploaded document: 123wqe', 'Pending Review'),
(30, '2025-09-02 03:42:01', 'E-Documentation', 'Edited document: 123wqe', 'Expired'),
(31, '2025-09-02 03:42:55', 'E-Documentation', 'Deleted document: qwee', 'Deleted'),
(32, '2025-09-02 03:44:12', 'E-Documentation', 'Deleted document: w1231', 'Deleted'),
(33, '2025-09-02 16:17:47', 'CRM', 'Updated customer: try (try)', 'Success'),
(34, '2025-09-02 16:31:38', 'CRM', 'Deleted customer: try (try)', 'Success'),
(35, '2025-09-02 17:40:07', 'CSM', 'Added new contract: 873485783478 - Roy', 'Success'),
(36, '2025-09-02 18:01:57', 'CSM', 'Added new contract: 878346573 - All G', 'Success'),
(37, '2025-09-02 18:03:03', 'CRM', 'Added new customer: ALLG (ALLG)', 'Success'),
(38, '2025-09-03 21:52:22', 'CSM', 'Added new contract: 645624324 - Oppss', 'Success'),
(39, '2025-09-03 22:51:50', 'CRM', 'Added new customer: testing (testing.corp)', 'Success'),
(40, '2025-09-03 22:52:00', 'CRM', 'Updated customer: ALLG (ALLG)', 'Success'),
(41, '2025-09-03 22:52:25', 'E-Documentation', 'Edited document: 123wqe', 'Expired'),
(42, '2025-09-03 22:52:35', 'CRM', 'Updated customer: testing (testing.corp)', 'Success'),
(43, '2025-09-03 22:52:43', 'E-Documentation', 'Edited document: 123wqe', 'Expired'),
(44, '2025-09-03 22:53:43', 'E-Documentation', 'Edited document: 123wqe', 'Expired'),
(45, '2025-09-03 22:54:10', 'E-Documentation', 'Deleted document: sadasd', 'Deleted'),
(46, '2025-09-04 00:18:51', 'CRM', 'Updated customer: testing (testing.corp)', 'Success'),
(47, '2025-09-04 21:26:24', 'CSM', 'Added new contract: 4353523123 - Try ule', 'Success'),
(48, '2025-09-04 21:37:56', 'CSM', 'Added new contract:  - ', 'Success'),
(49, '2025-09-04 21:38:25', 'CSM', 'Added new contract:  - ', 'Success'),
(50, '2025-09-04 21:44:23', 'CSM', 'Added new contract: 123 - 123123', 'Success'),
(51, '2025-09-04 21:51:38', 'CSM', 'Added new contract: 0980934758 - Try nga', 'Success'),
(52, '2025-09-04 22:24:24', 'CSM', 'Added new contract: 467456767 - haaa', 'Success'),
(53, '2025-09-04 22:37:23', 'E-Documentation', 'Uploaded document: qweee', 'Pending Review'),
(54, '2025-09-04 22:37:49', 'E-Documentation', 'Edited document: qweee', 'Compliant'),
(55, '2025-09-04 22:42:53', 'CRM', 'Updated customer: testing (testing.corp)', 'Success'),
(56, '2025-09-04 22:43:26', 'CRM', 'Added new customer: werqwe (123123qwe)', 'Success'),
(57, '2025-09-04 22:43:44', 'CRM', 'Deleted customer: werqwe (123123qwe)', 'Success'),
(58, '2025-09-07 17:33:07', 'CRM', 'Updated customer: testing (testing.corp)', 'Success'),
(59, '2025-09-07 17:33:11', 'CRM', 'Updated customer: testing (testing.corp)', 'Success'),
(60, '2025-09-07 17:33:13', 'CRM', 'Updated customer: ALLG (ALLG)', 'Success'),
(61, '2025-09-07 17:33:16', 'CRM', 'Updated customer: ALLG (ALLG)', 'Success'),
(62, '2025-09-07 17:34:06', 'CRM', 'Updated customer: ALLG (ALLG)', 'Success'),
(63, '2025-09-07 17:34:37', 'CRM', 'Deleted customer: testing (testing.corp)', 'Success'),
(64, '2025-09-07 18:12:06', 'CRM', 'Updated customer: ALLG (ALLG)', 'Success'),
(65, '2025-09-07 19:41:26', 'CSM', 'Added new contract: 123123 - 31231w', 'Success'),
(66, '2025-09-07 19:42:53', 'E-Documentation', 'Edited document: qweee', 'Compliant'),
(67, '2025-09-07 19:51:48', 'E-Documentation', 'Deleted document: 123wqe', 'Deleted'),
(68, '2025-09-07 19:55:43', 'E-Documentation', 'Edited document: qweee', 'Compliant'),
(69, '2025-09-07 19:55:55', 'E-Documentation', 'Uploaded document: 123qwe', 'Pending Review'),
(70, '2025-09-08 00:35:38', 'CRM', 'Updated customer: ALLG (ALLG)', 'Success'),
(71, '2025-09-08 23:39:24', 'Accounts', 'Updated account: 123333 (user)', 'Success'),
(72, '2025-09-08 23:39:51', 'Accounts', 'Deleted account: 123333 (user)', 'Success'),
(73, '2025-09-08 23:48:03', 'Accounts', 'Updated account: 097 (user)', 'Success'),
(74, '2025-09-09 00:00:24', 'CRM', 'Updated account: 097 (admin)', 'Success'),
(75, '2025-09-09 00:23:08', 'CRM', 'Deleted account: 00000 (admin)', 'Success'),
(76, '2025-09-10 23:16:54', 'CRM', 'Updated account: 097 (admin)', 'Success'),
(77, '2025-09-13 08:43:58', 'CSM', 'Added new contract: CSM-20250913-084330-6551 - user1', 'Success'),
(78, '2025-09-13 14:37:39', 'CRM', 'Deleted user: qwe', 'Success'),
(79, '2025-09-13 14:41:49', 'CRM', 'Deleted user: Sheen', 'Success'),
(80, '2025-09-13 14:42:44', 'CRM', 'Deleted user: user2', 'Success'),
(81, '2025-09-13 17:37:56', 'CRM', 'Deleted user: user1', 'Success'),
(82, '2025-09-13 17:37:59', 'CRM', 'Deleted user: 097', 'Success'),
(83, '2025-09-13 17:38:05', 'CRM', 'Deleted user: erer', 'Success'),
(84, '2025-09-13 17:38:08', 'CRM', 'Deleted user: 123', 'Success'),
(85, '2025-09-13 17:49:04', 'CSM', 'Added new contract: CSM-20250913-174821-7670 - user1', 'Success'),
(86, '2025-09-14 17:51:09', 'E-Documentation', 'Uploaded document: asd', 'Pending Review'),
(87, '2025-09-14 17:51:42', 'CRM', 'Updated user: user1', 'Success'),
(88, '2025-09-14 17:52:02', 'CRM', 'Updated user: user1', 'Success'),
(89, '2025-09-16 16:04:07', 'CRM', 'Updated user: user1', 'Success'),
(90, '2025-09-16 16:19:57', 'CRM', 'Updated user: user1', 'Success'),
(91, '2025-09-16 16:23:33', 'E-Documentation', 'Edited document: <script>   alert(\"di sya secure\");   window.location.href = \"https://www.youtube.com/watch?v=FPcsJTxnaBQ\"; </script>', 'Pending Review'),
(92, '2025-09-21 08:40:51', 'CSM', 'Added new contract: CSM-20250921-163450-7565 - reniel', 'Success'),
(93, '2025-09-21 09:59:57', 'CSM', 'Added new contract: CSM-20250921-175249-7272 - marcos', 'Success'),
(94, '2025-09-21 10:10:16', 'CSM', 'Added new contract: CSM-20250921-180424-1172 - justine', 'Success'),
(95, '2025-09-21 12:39:55', 'CSM', 'Added new contract: CSM-20250921-203407-1748 - jose', 'Success'),
(96, '2025-09-22 16:15:27', 'CSM', 'Added new contract: CSM-20250922-161331-6961 - debug', 'Success'),
(97, '2025-09-22 16:17:46', 'E-Documentation', 'Uploaded document: tryasdsad', 'Pending Review'),
(98, '2025-09-22 16:18:22', 'E-Documentation', 'Edited document: tryasdsadasdas', 'Expired'),
(99, '2025-09-22 16:18:29', 'E-Documentation', 'Deleted document: tryasdsadasdas', 'Deleted'),
(100, '2025-09-22 16:40:25', 'CSM', 'Added new contract: CSM-20250922-163953-1983 - Roy', 'Success'),
(101, '2025-09-22 18:04:18', 'CSM', 'Added new contract: CSM-20250922-180405-6428 - debug', 'Success'),
(102, '2025-09-22 18:06:08', 'E-Documentation', 'Edited document: 123qwe', 'Pending Review'),
(103, '2025-09-22 20:59:53', 'CSM', 'Added new contract: CSM-20250922-205933-8864 - defense', 'Success'),
(104, '2025-09-22 21:00:33', 'E-Documentation', 'Uploaded document: defense', 'Pending Review'),
(105, '2025-09-23 12:53:57', 'CSM', 'Added new contract: CSM-20250923-124645-9409 - dalisay', 'Success'),
(106, '2025-09-23 17:42:15', 'CSM', 'Added new contract: CSM-20250923-173619-2193 - cherie', 'Success'),
(107, '2025-09-24 12:40:39', 'CSM', 'Added new contract: CSM-20250924-123948-7686 - bagyo', 'Success'),
(108, '2025-09-24 12:42:59', 'CSM', 'Added new contract: CSM-20250924-124236-2166 - cherie', 'Success'),
(109, '2025-09-24 12:52:22', 'CSM + E-Docs', 'Added new contract: CSM-20250924-124433-7618 - dalisay (auto-created in E-Doc)', 'Success'),
(110, '2025-09-24 13:17:52', 'CSM', 'Added new contract: CSM-20250924-131633-2505 - sample', 'Success'),
(111, '2025-09-24 15:05:51', 'CSM', 'Added new contract: CSM-20250924-150436-2278 - cherie', 'Success'),
(112, '2025-09-24 15:08:21', 'CSM', 'Added new contract: CSM-20250924-150436-2278 - cherie', 'Success'),
(113, '2025-09-24 20:29:00', 'E-Documentation', 'Deleted document: qweee', 'Deleted'),
(114, '2025-09-24 20:29:08', 'E-Documentation', 'Deleted document: 123qwe', 'Deleted'),
(115, '2025-09-24 20:29:18', 'E-Documentation', 'Deleted document: defense', 'Deleted'),
(116, '2025-09-25 00:43:58', 'CSM', 'Added new contract: CSM-20250925-004255-3729 - Olga', 'Success'),
(117, '2025-09-25 00:45:01', 'E-Documentation', 'Uploaded document: Olga', 'Pending Review'),
(118, '2025-09-25 01:16:24', 'E-Documentation', 'Deleted document: CSM-20250924-124433-7618 - dalisay', 'Deleted'),
(119, '2025-09-25 01:16:50', 'E-Documentation', 'Uploaded document: klyde', 'Pending Review'),
(120, '2025-10-14 13:00:26', 'E-Documentation', 'Edited document: klydes', 'Pending Review'),
(121, '2025-10-14 13:01:06', 'E-Documentation', 'Deleted document: Olga', 'Deleted'),
(122, '2025-10-14 13:01:23', 'E-Documentation', 'Deleted document: klydes', 'Deleted'),
(123, '2025-10-14 13:04:42', 'E-Documentation', 'Uploaded document: zxc', 'Pending Review'),
(124, '2025-10-14 16:22:16', 'E-Documentation', 'Edited document: zxcd', 'Pending Review'),
(125, '2025-10-14 16:43:42', 'CSM', 'Added new contract: CSM-20251014-164241-9984 - KKP', 'Success'),
(126, '2025-10-17 14:08:55', 'CSM', 'Added new contract: CSM-20251017-140805-6419 - 121', 'Success'),
(127, '2025-10-17 20:58:45', 'E-Documentation', 'Archived document: zxcd', 'Archived'),
(128, '2025-10-17 20:58:54', 'E-Documentation', 'Resent archived document: zxcd', 'Pending Review'),
(129, '2025-10-17 21:01:19', 'E-Documentation', 'Uploaded document: ttt', 'Pending Review'),
(130, '2025-10-17 21:11:10', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(131, '2025-10-17 21:11:19', 'E-Documentation', 'Resent document: ttt', 'Pending Review'),
(132, '2025-10-17 21:37:45', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(133, '2025-10-17 21:43:37', 'E-Documentation', 'Resent document: ttt', 'Pending Review'),
(134, '2025-10-17 21:43:48', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(135, '2025-10-17 21:43:56', 'E-Documentation', 'Resent document: ttt', 'Pending Review'),
(136, '2025-10-17 21:46:59', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(137, '2025-10-17 21:51:11', 'E-Documentation', 'Resent document: ttt', 'Pending Review'),
(138, '2025-10-17 21:51:32', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(139, '2025-10-17 21:54:14', 'E-Documentation', 'Archived document: zxcd', 'Archived'),
(140, '2025-10-17 21:58:45', 'E-Documentation', 'Resent document: ttt', 'Pending Review'),
(141, '2025-10-17 21:58:49', 'E-Documentation', 'Resent document: zxcd', 'Pending Review'),
(142, '2025-10-17 21:58:57', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(143, '2025-10-17 22:16:49', 'E-Documentation', 'Archived document: zxcd', 'Archived'),
(144, '2025-10-18 07:28:22', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(145, '2025-10-18 07:49:29', 'E-Documentation', 'Restored document: ttt', 'Restored'),
(146, '2025-10-18 07:50:46', 'E-Documentation', 'Restored document: zxcd', 'Restored'),
(147, '2025-10-18 08:09:31', 'E-Documentation', 'Archived document: zxcd', 'Archived'),
(148, '2025-10-18 08:09:44', 'E-Documentation', 'Restored document: zxcd', 'Restored'),
(149, '2025-10-18 08:17:15', 'E-Documentation', 'Uploaded document: sdf', 'Pending Review'),
(150, '2025-10-18 08:17:41', 'E-Documentation', 'Archived document: sdf', 'Archived'),
(151, '2025-10-18 08:17:47', 'E-Documentation', 'Archived document: zxcd', 'Archived'),
(152, '2025-10-18 08:19:46', 'E-Documentation', 'Restored document: zxcd', 'Restored'),
(153, '2025-10-18 08:37:02', 'E-Documentation', 'Restored document: sdf', 'Restored'),
(154, '2025-10-18 08:37:34', 'E-Documentation', 'Archived document: sdf', 'Archived'),
(155, '2025-10-18 08:47:40', 'E-Documentation', 'Restored document: sdf', 'Restored'),
(156, '2025-10-18 11:09:04', 'E-Documentation', 'Archived document: sdf', 'Archived'),
(157, '2025-10-18 11:09:21', 'E-Documentation', 'Restored document: sdf', 'Restored'),
(158, '2025-10-18 11:09:46', 'E-Documentation', 'Edited document: vvv', 'Pending Review'),
(159, '2025-10-18 17:53:22', 'E-Documentation', 'Archived document: vvv', 'Archived'),
(160, '2025-10-19 22:46:35', 'E-Documentation', 'Restored document: vvv', 'Restored'),
(161, '2025-10-19 22:46:51', 'E-Documentation', 'Archived document: vvv', 'Archived'),
(162, '2025-10-19 22:47:00', 'E-Documentation', 'Archived document: zxcd', 'Archived'),
(163, '2025-10-19 22:47:09', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(164, '2025-10-19 22:47:16', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(165, '2025-10-21 09:09:40', 'CSM', 'Added new contract: CSM-20251021-090915-8701 - valle', 'Success'),
(166, '2025-10-21 09:10:18', 'E-Documentation', 'Restored document: ttt', 'Restored'),
(167, '2025-10-21 14:43:15', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(168, '2025-10-21 14:43:26', 'E-Documentation', 'Restored document: ttt', 'Restored'),
(169, '2025-10-23 14:45:19', 'E-Documentation', 'Archived document: ttt', 'Archived'),
(170, '2025-10-24 16:51:13', 'E-Documentation', 'Restored document: ttt', 'Restored'),
(171, '2025-10-26 08:02:57', 'CSM', 'Added new contract: CSM-20251026-080236-8830 - Roy valle', 'Success'),
(172, '2026-01-02 22:47:34', 'CSM', 'Added Contract: CSM-20260102-29-7430 (resr)', 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `archive_crm`
--

CREATE TABLE `archive_crm` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `archived_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_doc`
--

CREATE TABLE `archive_doc` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'Archived',
  `archived_on` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `archive_doc`
--

INSERT INTO `archive_doc` (`id`, `title`, `doc_type`, `filename`, `status`, `archived_on`) VALUES
(9, 'vvv', 'Bill of Lading', 'Contract_CSM-20251017-140805-6419.pdf', 'Archived', '2025-10-19 22:46:51'),
(10, 'zxcd', 'Bill of Lading', 'Contract_CSM-20250925-004255-3729 (2).pdf', 'Archived', '2025-10-19 22:47:00'),
(11, 'ttt', 'Bill of Lading', 'Session 5_ Cybersecurity.pdf', 'Archived', '2025-10-19 22:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `contract_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Expired','Terminated') DEFAULT 'Active',
  `contract_file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `contract_number`, `user_id`, `client_name`, `start_date`, `end_date`, `status`, `contract_file`, `created_at`) VALUES
(2, 'TEST-CONTRACT-001', 1, 'Bes Testing Corp', '2025-01-01', '2030-12-31', 'Active', NULL, '2026-01-12 22:22:15'),
(3, 'CN-2026-B59BB', 116, 'lazada', '2026-01-09', '2026-01-19', 'Active', NULL, '2026-01-12 22:22:15'),
(4, 'CN-2026-BBE96', 123, 'tiktok', '2026-01-11', '2026-02-11', 'Active', NULL, '2026-01-12 22:22:15'),
(5, 'CNT-20260112-389', 117, 'lbc', '2026-01-12', '2026-02-02', 'Active', 'uploads/contracts/CNT-20260112-389.png', '2026-01-12 22:22:15'),
(6, 'CNT-20260112-888', 118, 'one documents', '2026-01-12', '2026-02-02', 'Active', NULL, '2026-01-12 22:22:15'),
(7, 'CNT-20260112-342', 125, 'olga', '2026-01-12', '2026-01-26', 'Active', NULL, '2026-01-12 23:22:06'),
(8, 'CNT-20260113-727', 118, 'jnt', '2026-01-13', '2026-02-03', 'Active', NULL, '2026-01-13 10:30:18'),
(9, 'CNT-20260113-721', 116, 'facebook', '2026-01-13', '2026-01-20', 'Active', NULL, '2026-01-13 13:31:12'),
(10, 'CNT-20260113-524', 125, 'fb', '2026-01-13', '2026-01-20', 'Active', NULL, '2026-01-13 14:31:06'),
(11, 'MASTER-SLA', 0, 'STANDARD SERVICE AGREEMENT', '2026-01-14', '2036-01-14', '', NULL, '2026-01-14 18:33:45'),
(12, 'CNT-2026-0119', 119, 'TEST1', '2026-01-14', '2031-01-14', 'Active', NULL, '2026-01-14 18:50:25'),
(13, 'CNT-2026-0120', 120, 'TESTTEST', '2026-01-14', '2031-01-14', 'Active', NULL, '2026-01-14 18:50:25'),
(14, 'CNT-2026-0121', 121, 'ASD', '2026-01-14', '2031-01-14', 'Active', NULL, '2026-01-14 18:50:25'),
(15, 'CNT-2026-0122', 122, 'ASS', '2026-01-14', '2031-01-14', 'Active', NULL, '2026-01-14 18:50:25'),
(16, 'CNT-2026-0124', 124, 'AGHIK', '2026-01-14', '2031-01-14', 'Active', NULL, '2026-01-14 18:50:26'),
(17, 'CNT-2026-0127', 127, 'VALLE', '2026-01-14', '2031-01-14', 'Active', NULL, '2026-01-14 19:28:55');

-- --------------------------------------------------------

--
-- Table structure for table `crm`
--

CREATE TABLE `crm` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `company` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('Active','Prospect','Inactive') DEFAULT 'Prospect',
  `last_contract` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crm`
--

INSERT INTO `crm` (`id`, `customer_name`, `company`, `email`, `phone`, `status`, `last_contract`) VALUES
(5, 'ALLG', 'ALLG', 'ALLG@gmail.com', '909878747', 'Active', '2025-09-02 10:03:03'),
(8, '12', '32', 'roy.valle.ge2019l@gmail.com', '09499508447', 'Active', '2025-10-18 10:45:14');

-- --------------------------------------------------------

--
-- Table structure for table `csm`
--

CREATE TABLE `csm` (
  `id` int(11) NOT NULL,
  `contract_id` varchar(50) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `contract_type` varchar(50) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `contract_file` varchar(255) DEFAULT NULL,
  `status` enum('Active','Expired','Pending') NOT NULL,
  `sla_target` decimal(5,2) DEFAULT 95.00,
  `sla_actual` decimal(5,2) DEFAULT 0.00,
  `sla_compliance` enum('Compliant','Non-Compliant') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `csm`
--

INSERT INTO `csm` (`id`, `contract_id`, `client_name`, `contract_type`, `start_date`, `end_date`, `contract_file`, `status`, `sla_target`, `sla_actual`, `sla_compliance`, `file_path`) VALUES
(83, 'CSM-20250925-004255-3729', 'Olga', NULL, '2025-02-09', '2025-02-12', NULL, 'Active', 95.00, 0.00, 'Compliant', NULL),
(86, 'CSM-20251021-090915-8701', 'valle', NULL, '2025-10-21', '2025-10-28', NULL, 'Active', 95.00, 0.00, 'Compliant', NULL),
(87, 'CSM-20251026-080236-8830', 'Roy valle', NULL, '2025-10-26', '2025-11-02', NULL, 'Active', 95.00, 0.00, 'Compliant', NULL),
(88, 'CSM-20260102-29-7430', 'resr', 'Warehousing', '2026-01-02', '2026-01-30', 'uploads/contracts/CSM-20260102-29-7430_Print Label.pdf', 'Active', 95.00, 98.00, 'Compliant', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `e_doc`
--

CREATE TABLE `e_doc` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `uploaded_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending Review'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `e_doc`
--

INSERT INTO `e_doc` (`id`, `title`, `doc_type`, `filename`, `uploaded_on`, `status`) VALUES
(34, 'ttt', 'Bill of Lading', 'Session 5_ Cybersecurity.pdf', '2025-10-24 08:51:13', 'Pending Review');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread',
  `reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `account_id`, `comment`, `created_at`, `status`, `reply`) VALUES
(44, 116, 'OK', '2025-10-26 02:39:16', 'unread', NULL),
(45, 120, 'cat /etc/psswrd', '2025-11-11 06:47:02', 'unread', NULL),
(46, 123, 'From saudi with love', '2026-01-02 04:18:49', 'unread', NULL),
(47, 116, 'hello', '2026-01-04 13:18:12', 'read', NULL),
(48, 116, 'ayos', '2026-01-10 11:24:16', 'unread', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `method` varchar(50) DEFAULT 'GCash',
  `reference_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `amount`, `payment_date`, `status`, `method`, `reference_no`) VALUES
(42, 116, 354.19, '2025-10-25 19:54:49', 'Pending', 'cod', NULL),
(43, 116, 195.12, '2025-10-26 10:38:10', 'Pending', 'cod', NULL),
(45, 116, 622.37, '2025-10-28 22:08:55', 'Pending', 'cod', NULL),
(48, 116, 4202.69, '2025-12-19 22:38:07', 'Pending', 'cod', NULL),
(49, 116, 960.19, '2025-12-20 10:33:34', 'Pending', 'cod', NULL),
(50, 116, 689.45, '2025-12-20 21:19:35', 'Pending', 'cod', NULL),
(51, 116, 2386.85, '2025-12-22 19:32:10', 'Pending', 'cod', NULL),
(52, 116, 1304.21, '2025-12-29 22:21:30', 'Pending', 'cod', NULL),
(53, 123, 351.53, '2026-01-02 11:55:30', 'Pending', 'cod', NULL),
(54, 123, 506.50, '2026-01-02 12:00:30', 'Pending', 'cod', NULL),
(55, 116, 589.20, '2026-01-03 23:35:45', 'Pending', 'cod', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `reply_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`id`, `feedback_id`, `admin_id`, `reply_message`, `created_at`) VALUES
(24, 47, 102, 'gg', '2026-01-04 13:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `shipment`
--

CREATE TABLE `shipment` (
  `id` varchar(50) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `sender_contact` varchar(20) DEFAULT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_contact` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `destination_island` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `sla_status` varchar(20) DEFAULT 'On Time',
  `created_at` datetime DEFAULT current_timestamp(),
  `delivery_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `contract_number` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `sender_contact` varchar(50) DEFAULT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_contact` varchar(50) DEFAULT NULL,
  `origin_address` varchar(255) DEFAULT NULL,
  `destination_address` varchar(255) DEFAULT NULL,
  `specific_address` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `weight` decimal(10,2) NOT NULL,
  `package_type` varchar(50) DEFAULT NULL,
  `package_description` text NOT NULL,
  `distance_km` decimal(10,2) DEFAULT 0.00,
  `price` decimal(10,2) DEFAULT 0.00,
  `sla_agreement` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'Unspecified',
  `bank_name` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `sla_status` enum('Met','Breached','Pending') DEFAULT 'Pending',
  `proof_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `ai_estimated_time` varchar(100) DEFAULT 'Calculating...',
  `target_delivery_date` datetime DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `feedback_text` text DEFAULT NULL,
  `destination_island` varchar(50) DEFAULT 'Luzon'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `contract_number`, `user_id`, `sender_name`, `sender_contact`, `receiver_name`, `receiver_contact`, `origin_address`, `destination_address`, `specific_address`, `address`, `weight`, `package_type`, `package_description`, `distance_km`, `price`, `sla_agreement`, `payment_method`, `bank_name`, `status`, `sla_status`, `proof_image`, `created_at`, `ip_address`, `ai_estimated_time`, `target_delivery_date`, `rating`, `feedback_text`, `destination_island`) VALUES
(101, NULL, 0, 'Juan Dela Cruz', NULL, 'Maria Clara', NULL, NULL, NULL, NULL, '123 Rizal St. Manila', 0.00, NULL, '', 0.00, 500.00, NULL, 'Unspecified', NULL, 'Pending', 'Pending', NULL, '2026-01-11 13:48:16', NULL, 'Calculating...', NULL, 0, NULL, 'Luzon'),
(102, NULL, 0, 'roy Dela Cruz', NULL, 'Maria Clara', NULL, NULL, NULL, NULL, '123 Rizal St. Manila', 0.00, NULL, '', 0.00, 500.00, NULL, 'Unspecified', NULL, 'Pending', 'Pending', NULL, '2026-01-11 13:57:25', NULL, 'Calculating...', NULL, 0, NULL, 'Luzon'),
(143, NULL, 116, 'Roy valle', NULL, 'Justine', NULL, NULL, NULL, NULL, 'F. B. Harrison Street, Barangay 70, Zone 9, District 1, Pasay, Southern Manila District, Metro Manila, 1302, Philippines', 8.00, NULL, 'electronics', 14.76, 354.19, NULL, 'cod', NULL, 'Pending', 'Pending', NULL, '2025-10-25 11:54:49', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(144, NULL, 116, 'justine', NULL, 'roy', NULL, NULL, NULL, NULL, 'Quezon city', 20.00, NULL, 'ok', 8.13, 195.12, NULL, 'cod', NULL, 'Pending', 'Pending', NULL, '2025-10-26 02:38:10', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(145, NULL, 0, 'Roy', NULL, 'ako lang', NULL, NULL, NULL, NULL, 'Barangay 3, Calamba, Laguna, CALABARZON, Philippines', 5.00, NULL, 'asfaf', 55.40, 1329.48, NULL, 'cod', NULL, 'Pending', 'Pending', NULL, '2025-10-28 14:07:03', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(146, NULL, 116, 'roy', NULL, 'asf', NULL, NULL, NULL, NULL, 'sdfasf', 4.00, NULL, 'affs', 25.93, 622.37, NULL, 'cod', NULL, 'Pending', 'Pending', NULL, '2025-10-28 14:08:55', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(147, NULL, 0, 'resr', NULL, 'test', NULL, NULL, NULL, NULL, 'dfadfadsf', 155.00, NULL, 'fadsf', 15.37, 368.78, NULL, 'cod', NULL, 'Pending', 'Pending', NULL, '2025-11-11 06:52:27', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(148, NULL, 0, 'dfadsf', NULL, 'dfasdfasdf', NULL, NULL, NULL, NULL, 'fdsadfasdf', 222.00, NULL, 'dfad', 15.37, 368.78, NULL, 'cod', NULL, 'Pending', 'Pending', NULL, '2025-11-11 07:26:18', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(149, NULL, 116, 'royzxcasd@gmail.com', NULL, 'GG', NULL, NULL, NULL, NULL, 'Novaliches Proper, Quezon City, Metro Manila, NCR, Philippines', 5.00, NULL, 'BOOK', 175.11, 4202.69, NULL, 'cod', NULL, 'Cancelled', 'Pending', NULL, '2025-12-19 14:38:07', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(150, NULL, 116, 'royzxcasd@gmail.com', NULL, 'ere', NULL, NULL, NULL, NULL, 'Cofradia', 4.00, NULL, 'ggasd', 40.01, 960.19, NULL, 'cod', NULL, 'Cancelled', 'Pending', NULL, '2025-12-20 02:33:34', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(151, NULL, 116, 'royzxcasd@gmail.com', NULL, 'ako lang', NULL, 'Tondo, Manila, Metro Manila, NCR, Philippines', 'Maliksi III, Bacoor, Cavite, CALABARZON, Philippines', NULL, 'maliksi', 12.00, NULL, 'bato', 28.73, 689.45, NULL, 'cod', NULL, 'Cancelled', 'Pending', NULL, '2025-12-20 13:19:35', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(153, NULL, 116, 'royzxcasd@gmail.com', NULL, 'ROY EVERO VALLE', NULL, 'Bagong Silang, Caloocan, Metro Manila, NCR, Philippines', 'San Felipe, San Jose, Del Pilar, San Fernando, Pampanga, Central Luzon, 2000, Philippines', NULL, 'Jdhdh', 5.00, NULL, 'Hdh', 54.34, 1304.21, NULL, 'cod', NULL, 'Delivered', 'Pending', NULL, '2025-12-29 14:21:30', NULL, 'Calculating...', '2026-01-16 18:13:02', 5, 'sdfaf', 'Luzon'),
(154, NULL, 123, 'gerrychogonzales1234+slate@gmail.com', NULL, 'Diwata', NULL, 'Bagong Silangan, Quezon City, Metro Manila, NCR, Philippines', 'Bagong Silang, Caloocan, Metro Manila, NCR, Philippines', NULL, 'bcp mv campus', 2.00, NULL, 'asdasdasd', 14.65, 351.53, NULL, 'cod', NULL, 'Delivered', 'Met', 'POD_154_1768038490.jpg', '2026-01-02 03:55:30', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(155, NULL, 123, 'gerrychogonzales1234+slate@gmail.com', NULL, '<h1>Boy Dila</h1>', NULL, 'Tondo, Manila, Metro Manila, NCR, Philippines', 'Bagong Silangan, Quezon City, Metro Manila, NCR, Philippines', NULL, '<h1>biringan city</h1>', 0.00, NULL, '<h1>Sample description</>', 21.10, 506.50, NULL, 'cod', NULL, 'Pending', 'Pending', NULL, '2026-01-02 04:00:30', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(156, NULL, 116, 'royzxcasd@gmail.com', NULL, 'asdsad', NULL, 'Tondo, Manila, Metro Manila, NCR, Philippines', '207, Molino Road, T. Kalugdan Compound, Ligas II, Bacoor, Cavite, Calabarzon, 4102, Philippines', NULL, 'asda', 3.00, NULL, 'werewr', 24.55, 589.20, NULL, 'cod', NULL, 'Cancelled', 'Pending', NULL, '2026-01-03 15:35:45', NULL, 'Calculating...', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(157, 'CN-20260103-705631', 116, 'royzxcasd@gmail.com', NULL, 'ararsdfs', NULL, 'Botocan, Quezon City, Metro Manila, Philippines', 'Bagong Silang, Caloocan, Metro Manila, Philippines', 'sfs', NULL, 5.00, 'standard', 'GG', 19.30, 463.30, 'Shipment Handling Policy, Delay Policy, Loss and Damage Policy, Cancellation Policy, Confidentiality Policy, Force Majeure', 'cod', '', 'Cancelled', 'Pending', NULL, '2026-01-03 17:10:43', NULL, '35-45 minutes - Light Traffic', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(158, 'CN-20260103-926822', 116, 'royzxcasd@gmail.com', NULL, 'daryl', NULL, 'San Roque, Quezon City, Metro Manila, Philippines', 'Mambog Road, Springside Villas, Mambog II, Mambog, Bacoor, Cavite, Calabarzon, 4102, Philippines', 'Mambog Road, Springside Villas, Mambog II, Mambog, Bacoor, Cavite, Calabarzon, 4102, Philippines', NULL, 1.00, 'standard', 'fragile', 30.01, 720.28, 'Shipment Handling Policy, Delay Policy, Loss and Damage Policy, Cancellation Policy, Confidentiality Policy, Compliance Policy, Force Majeure', 'cod', '', 'Cancelled', 'Pending', NULL, '2026-01-03 17:16:01', NULL, '50 minutes - Very Light Traffic', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(159, 'CN-20260103-212954', 116, 'royzxcasd@gmail.com', NULL, 'ian', NULL, 'Bagong Silang, Caloocan, Metro Manila, Philippines', 'Catmon, Malolos, Bulacan, Philippines', 'Catmon, Malolos, Bulacan, Philippines', NULL, 7.00, 'standard', 'POP', 30.77, 738.37, 'Shipment Handling Policy, Delay Policy, Loss and Damage Policy, Cancellation Policy, Confidentiality Policy, Force Majeure', 'cod', '', 'Cancelled', 'Pending', NULL, '2026-01-03 17:22:28', NULL, '1 hour - Light Traffic', '2026-01-16 18:13:02', 0, 'Booked by mistake', 'Luzon'),
(160, 'CN-20260104-806604', 116, 'royzxcasd@gmail.com', NULL, 'cherie mae', NULL, 'Bagong Silang, Caloocan, Metro Manila, Philippines', 'Baguio City – Twin River – Keweng – Dalupirip National Road, Petican, Tinongdan, Benguet, Cordillera Administrative Region, Philippines', 'Baguio City – Twin River – Keweng – Dalupirip National Road, Petican, Tinongdan, Benguet, Cordillera Administrative Region, Philippines', NULL, 8.00, 'standard', 'bag', 278.41, 6681.82, 'Shipment Handling Policy, Delay Policy, Loss and Damage Policy, Cancellation Policy, Confidentiality Policy, Compliance Policy, Force Majeure', 'cod', '', 'Delivered', 'Pending', NULL, '2026-01-04 02:54:56', NULL, '7 to 8 hours - Moderate to Heavy Traffic', '2026-01-16 18:13:02', 5, '', 'Luzon'),
(161, 'CN-20260104-806604', 116, 'royzxcasd@gmail.com', NULL, 'cherie mae', NULL, 'Bagong Silang, Caloocan, Metro Manila, Philippines', 'Baguio City – Twin River – Keweng – Dalupirip National Road, Petican, Tinongdan, Benguet, Cordillera Administrative Region, Philippines', 'Baguio City – Twin River – Keweng – Dalupirip National Road, Petican, Tinongdan, Benguet, Cordillera Administrative Region, Philippines', NULL, 8.00, 'standard', 'bag', 278.41, 6681.82, 'Shipment Handling Policy, Delay Policy, Loss and Damage Policy, Cancellation Policy, Confidentiality Policy, Compliance Policy, Force Majeure', 'cod', '', 'Cancelled', 'Pending', NULL, '2026-01-04 02:55:41', NULL, '7 to 8 hours - Moderate to Heavy Traffic', '2026-01-16 18:13:02', 0, 'aray', 'Luzon'),
(162, 'CN-20260104-264066', 116, 'royzxcasd@gmail.com', NULL, 'moy', NULL, 'Bagong Silang, Caloocan, Metro Manila, Philippines', 'Dalumpinas Road, Bangcusay, San Fernando, La Union, Ilocos Region, 2500, Philippines', 'Dalumpinas Road, Bangcusay, San Fernando, La Union, Ilocos Region, 2500, Philippines', NULL, 10.00, 'standard', 'kamote', 261.99, 6287.79, 'Shipment Handling Policy, Delay Policy, Loss and Damage Policy, Cancellation Policy, Confidentiality Policy, Compliance Policy, Force Majeure', 'cod', '', 'Delivered', 'Pending', NULL, '2026-01-04 04:29:34', NULL, '5 hours - Moderate Traffic', '2026-01-16 18:13:02', 5, 'hjgojg', 'Luzon'),
(163, 'CN-20260104-739020', 116, 'royzxcasd@gmail.com', NULL, 'valle', NULL, 'Caloocan, Metro Manila, Philippines', 'Baguio City – Twin River – Keweng – Dalupirip National Road, Petican, Tinongdan, Benguet, Cordillera Administrative Region, Philippines', 'Baguio City – Twin River – Keweng – Dalupirip National Road, Petican, Tinongdan, Benguet, Cordillera Administrative Region, Philippines', NULL, 5.00, 'standard', 'baso', 278.41, 4176.14, 'Shipment Handling Policy, Delay Policy', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-04 12:17:56', NULL, '7 hours 15 minutes - Moderate Traffic with Potential Fog', '2026-01-16 18:13:02', 5, 'nc', 'Luzon'),
(164, 'CN-20260104-194405', 116, 'royzxcasd@gmail.com', NULL, 'rhey', NULL, 'Adsia Logistics Johnstown, Elisco Road, Uyvico Compound, Kalawaan, Pasig First District, Pasig, Eastern Manila District, Metro Manila, 1638, Philippines', 'A. Consunji Street, Santo Rosario, San Jose, Del Pilar, San Fernando, Pampanga, Central Luzon, 2000, Philippines', 'Consunji Street', NULL, 10.00, 'box', 'box', 74.90, 1123.48, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-04 12:54:13', NULL, '1 hour 35 minutes to 1 hour 50 minutes - Light Traffic', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(165, 'CN-20260104-434976', 116, 'royzxcasd@gmail.com', NULL, 'mae', NULL, 'Caloocan, Metro Manila, Philippines', 'San Fernando, Pampanga, Philippines', 'Malolos', NULL, 6.00, 'box', 'box', 59.63, 894.50, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-04 13:28:10', NULL, '1 hour 45 minutes - Light Traffic', '2026-01-16 18:13:02', 5, '', 'Luzon'),
(166, 'CN-20260104-802667', 116, 'royzxcasd@gmail.com', NULL, 'Allan', NULL, 'Caloocan, Metro Manila, Philippines', 'San Fernando, Pampanga, Philippines', 'Santo rosario', NULL, 5.00, 'crate', 'Box', 59.63, 894.50, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-04 22:50:50', NULL, '2 hours 15 minutes - Heavy Traffic', '2026-01-16 18:13:02', 5, 'Good', 'Luzon'),
(167, 'CN-20260105-518115', 116, 'royzxcasd@gmail.com', NULL, 'ROY EVERO VALLE', NULL, 'Taguig, Metro Manila, Philippines', 'Marcos Ira Street, Saint Francis, Meycauayan, Bulacan, Central Luzon, 3020, Philippines', 'B.silang', NULL, 10.00, 'box', 'Yy', 36.37, 545.59, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-05 05:39:42', NULL, '2 hours 15 minutes - Moderate to Heavy Traffic', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(168, 'CN-20260105-593361', 116, 'royzxcasd@gmail.com', NULL, 'ako lang', NULL, 'Caloocan, Metro Manila, Philippines', 'Baguio City, Benguet, Philippines', 'Baguio ', NULL, 10.00, 'parcel', 'DOCS', 278.41, 4176.14, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-05 13:28:44', NULL, '4 hours 30 minutes - Light Traffic, Good Weather', '2026-01-16 18:13:02', 5, 'wow', 'Luzon'),
(169, 'CN-20260106-150256', 116, 'royzxcasd@gmail.com', NULL, 'Ttt', NULL, 'Caloocan, Metro Manila, Philippines', 'F. Tirona Street, Poblacion I-C, Imus, Cavite, Calabarzon, 4103, Philippines', 'Hdhsh', NULL, 5.00, 'box', 'Hdhs', 30.42, 456.35, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-06 04:55:28', NULL, '2 hours 15 minutes - Moderate Traffic', '2026-01-16 18:13:02', 5, 'Hdhd', 'Luzon'),
(170, 'CN-20260106-132081', 116, 'royzxcasd@gmail.com', NULL, 'Yshs', NULL, 'Makati, Metro Manila, Philippines', 'A. Consunji Street, Santo Rosario, San Jose, Del Pilar, San Fernando, Pampanga, Central Luzon, 2000, Philippines', 'Bdhs', NULL, 58.00, 'crate', 'Hdjd', 73.54, 1103.13, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-06 05:26:06', NULL, '2 hours - Moderate Traffic', '2026-01-16 18:13:02', 5, '', 'Luzon'),
(171, 'CN-20260106-702005', 116, 'royzxcasd@gmail.com', NULL, 'goku', NULL, 'Manila, Metro Manila, Philippines', 'Bacoor, Cavite, Philippines', 'Bacoor, Cavite, Philippines', NULL, 10.00, 'parcel', 'document', 17.01, 255.13, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', NULL, '2026-01-06 08:42:18', NULL, '1 hour 30 minutes - Heavy Traffic', '2026-01-16 18:13:02', 5, 'oh', 'Luzon'),
(172, 'CN-20260108-554006', 116, 'royzxcasd@gmail.com', NULL, 'apr', NULL, 'Manila, Metro Manila, Philippines', 'Quezon City, Metro Manila, Philippines', 'Quezon City, Metro Manila, Philippines', NULL, 10.00, 'parcel', 'papers', 12.86, 192.96, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', 'uploads/proofs/proof_172_1767867142.jpg', '2026-01-08 09:40:24', NULL, '1 hour 30 minutes - Heavy Traffic', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(173, 'STANDARD-RATE', 116, 'royzxcasd@gmail.com', '09499508447', 'pogi', '09077100481', 'Manila, Metro Manila, Philippines', 'Caloocan, Metro Manila, Philippines', 'Caloocan, Metro Manila, Philippines', NULL, 10.00, 'box', 'bag', 9.26, 138.91, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', 'uploads/proofs/proof_173_1767952753.png', '2026-01-09 09:50:03', NULL, '', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(174, 'STANDARD-RATE', 116, 'royzxcasd@gmail.com', '09499508447', 'nce', '09077100481', 'Caloocan, Metro Manila, Philippines', 'Pasig, Metro Manila, Philippines', 'Pasig', NULL, 10.00, 'box', 'Payong', 17.99, 269.86, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Met', 'POD_174_1768038298.jpg', '2026-01-09 10:02:41', NULL, '1 hour 45 minutes to 2 hours - Severe Rush Hour Traffic', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(175, 'STANDARD-RATE', 116, 'royzxcasd@gmail.com', '09499508447', 'nce', '09077100481', 'Caloocan, Metro Manila, Philippines', 'Knit Joy Manufacturing, Inc., Cainta, Rizal, Philippines', 'Knit Joy Manufacturing, Inc., Cainta, Rizal, Philippines', NULL, 10.00, 'box', 'Payong', 22.71, 340.59, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Pending', 'uploads/proofs/proof_175_1767954058.png', '2026-01-09 10:10:22', NULL, '2 hours 15 minutes - Very Heavy Traffic', '2026-01-16 18:13:02', 0, NULL, 'Luzon'),
(176, 'STANDARD-RATE', 116, 'royzxcasd@gmail.com', '09499508447', 'mindanao', '09077100481', 'Caloocan, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', NULL, 25.00, 'crate', 'BOX', 1382.25, 20733.77, 'Agreed to Standard Terms & Conditions', 'online', 'GCash', 'Delivered', 'Met', 'POD_176_1768038075.jpg', '2026-01-09 10:40:13', NULL, '24 - 36 hours (via Air Cargo Express)', '2026-01-16 18:49:18', 0, NULL, 'Luzon'),
(177, 'STANDARD-RATE', 116, 'royzxcasd@gmail.com', '09499508447', 'zxc', '09077100481', 'Pasay, Metro Manila, Philippines', 'SM City Davao, Quimpo Boulevard, Davao City, Davao Region, Philippines', 'SM City Davao, Quimpo Boulevard, Davao City, Davao Region, Philippines', NULL, 5.00, 'box', 'bag', 1477.10, 22156.57, 'Agreed to Standard Terms & Conditions', 'online', 'Maya', 'Delivered', 'Pending', 'POD_177_1768038561.jpg', '2026-01-09 10:51:07', NULL, '', NULL, 0, NULL, 'Luzon'),
(178, 'test', 116, 'royzxcasd@gmail.com', '09499508447', 'min', '09077100481', 'Quezon City, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', NULL, 12.00, 'parcel', 'zcx', 1385.84, 20787.61, 'Agreed to Standard Terms & Conditions', 'cod', '', 'Delivered', 'Met', NULL, '2026-01-09 11:09:38', NULL, '20-24 hours (Delivery by Saturday afternoon/evening, heavily impacted by Metro Manila traffic and Tr', '2026-01-14 19:14:35', 0, NULL, 'Luzon'),
(179, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'gumana', '09077100481', 'Caloocan, Metro Manila, Philippines', 'General Santos, Soccsksargen, Philippines', 'Soccsksargen', NULL, 10.00, 'box', 'BAG', 1620.46, 24306.89, 'Agreed to Standard Terms & Conditions', 'cod', '', 'Delivered', 'Met', NULL, '2026-01-09 11:46:56', NULL, 'Estimated time: 18-24 hours - Very Heavy Traffic & Event Impact', '2026-01-17 00:00:00', 5, '', 'Luzon'),
(180, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'justine', '09071004815', 'Manila, Metro Manila, Philippines', 'General Santos, Soccsksargen, Philippines', 'sdfaf', NULL, 25.00, 'box', 'wewr', 1613.61, 24204.16, 'Agreed to Standard Terms & Conditions', 'cod', '', 'Delivered', 'Met', 'POD_180_1768040771.jpg', '2026-01-10 10:25:36', NULL, '18-24 hours via air cargo (targeting Sunday, January 11, 2026 evening delivery)', '2026-01-18 00:00:00', 0, NULL, 'Luzon'),
(181, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'teng', '090771004815', 'Manila, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Xavier University', NULL, 15.00, 'box', 'GAMES', 1375.40, 20631.04, 'Agreed to Standard Terms & Conditions', 'cod', '', 'Delivered', 'Met', 'POD_181_1768044159.jpg', '2026-01-10 11:21:54', NULL, '48 hours - Light Traffic, Favorable Weather', '2026-01-18 00:00:00', 1, 'nice', 'Luzon'),
(182, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'asd', '090771004815', 'Quezon City, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', NULL, 16.00, 'box', 'kamatis', 1385.84, 20787.61, 'Standard Terms', 'cod', '', 'Pending', 'Pending', NULL, '2026-01-10 12:59:42', NULL, '24-48 hours - Moderate Traffic', '2026-01-18 00:00:00', 0, NULL, 'Luzon'),
(183, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'visca', '090771004815', 'SM City Davao, Quimpo Boulevard, Davao City, Davao Region, Philippines', 'General Santos, Soccsksargen, Philippines', 'Santos', NULL, 25.00, 'crate', 'papers', 138.20, 2073.03, NULL, 'cod', '', 'Pending', 'Pending', NULL, '2026-01-10 13:16:41', NULL, '2 hours 45 minutes - Light Traffic', '2026-01-17 00:00:00', 0, NULL, 'Mindanao'),
(184, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'visca', '090771004815', 'Roxas Airport, Roxas City Airport Bypass Road, Roxas, Capiz, Philippines', 'General Santos, Soccsksargen, Philippines', 'Santos', NULL, 25.00, 'crate', 'papers', 901.44, 13521.59, NULL, 'cod', '', 'Delivered', 'Met', 'POD_184_1768053039.jpg', '2026-01-10 13:19:06', NULL, '', '2026-01-15 00:00:00', 0, NULL, 'Mindanao'),
(185, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'visca', '090771004815', 'Roxas Airport, Roxas City Airport Bypass Road, Roxas, Capiz, Philippines', 'Old Bacolod Airport, 108 Street, Bacolod, Negros Occidental, Philippines', 'Santos', NULL, 25.00, 'crate', 'papers', 174.28, 2614.14, NULL, 'cod', '', 'Cancelled', 'Pending', NULL, '2026-01-10 13:19:55', NULL, '', '2026-01-17 00:00:00', 0, 'Changed mind', 'Visayas'),
(186, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'mm', '090771004815', 'Quezon City, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Ateneo de Cagayan, Corral', NULL, 10.00, 'box', 'zzzx', 1385.84, 20787.61, NULL, 'online', 'GCash', 'Pending', 'Pending', NULL, '2026-01-10 14:07:12', NULL, '12-24 hours (Air Cargo) - Light Traffic', '2026-01-18 00:00:00', 0, NULL, 'Mindanao'),
(187, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'mm', '090771004815', 'Quezon City, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Ateneo de Cagayan, Corral', NULL, 10.00, 'box', 'zzzx22', 1385.84, 20787.61, NULL, 'cod', '', 'Delivered', 'Met', 'POD_187_1768141480.jpg', '2026-01-10 14:08:02', NULL, 'Approximately 3.5 days - Light Traffic & Favorable Weather', '2026-01-18 00:00:00', 0, NULL, 'Mindanao'),
(188, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'mm', '090771004815', 'Quezon City, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Ateneo de Cagayan, Corral', NULL, 10.00, 'box', 'zzzx22', 1385.84, 20787.61, NULL, 'online', 'GCash', 'Delivered', 'Met', 'POD_188_1768054246.jpg', '2026-01-10 14:08:23', NULL, 'Approximately 3.5 days - Light Traffic & Favorable Weather', '2026-01-18 00:00:00', 0, NULL, 'Mindanao'),
(189, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'jr', '09071004815', 'Caloocan, Metro Manila, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', 'Xavier University - Ateneo de Cagayan, Corrales Avenue, Cagayan de Oro, Northern Mindanao, Philippines', NULL, 25.00, 'crate', 'bigas', 1382.25, 20733.77, NULL, 'online', 'GCash', 'Delivered', 'Met', 'POD_189_1768083769.jpg', '2026-01-10 22:18:50', NULL, '4-5 days - Light Traffic & Favorable Weather', '2026-01-20 00:00:00', 4, '/', 'Mindanao'),
(190, NULL, 0, 'Jose Rizal', NULL, 'Andres Bonifacio', NULL, NULL, NULL, NULL, '456 Dapitan St.', 0.00, NULL, '', 0.00, 1200.50, NULL, 'Unspecified', NULL, 'In Transit', 'Pending', NULL, '2026-01-11 13:48:16', NULL, 'Calculating...', NULL, 0, NULL, 'Visayas'),
(192, NULL, 0, 'Jose Rizal', NULL, 'Andres Bonifacio', NULL, NULL, NULL, NULL, '456 Dapitan St.', 0.00, NULL, '', 0.00, 1200.50, NULL, 'Unspecified', NULL, 'In Transit', 'Pending', NULL, '2026-01-11 13:57:25', NULL, 'Calculating...', NULL, 0, NULL, 'Visayas'),
(193, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'valle', '0907100481', 'Manila, Metro Manila, Philippines', 'SM City Davao, Quimpo Boulevard, Davao City, Davao Region, Philippines', 'davao', NULL, 15.00, 'furniture', 'll', 1480.34, 22205.16, NULL, 'online', 'Credit Card', 'Delivered', 'Met', 'POD_193_1768270991.jpg', '2026-01-13 02:21:22', NULL, 'Wednesday, January 14, 2026 by 2:00 PM - Moderate Traffic, Good Weather', '2026-01-23 00:00:00', 0, NULL, 'Mindanao'),
(194, 'CN-2026-B59BB', 116, 'royzxcasd@gmail.com', '09499508447', 'jus', '0907100481', 'Manila, Metro Manila, Philippines', 'Tagbilaran City Friendship Park, Tagbilaran, Bohol, Philippines', 'Tagbilaran City Friendship Park, Tagbilaran, Bohol, Philippines', NULL, 25.00, 'box', 'gamot', 1160.04, 17400.63, NULL, 'online', 'GCash', 'Delivered', 'Met', 'POD_194_1768282963.jpg', '2026-01-13 05:34:16', NULL, '18-24 hours (Air Cargo, Moderate Manila Traffic)', '2026-01-18 00:00:00', 4, '', 'Visayas'),
(195, 'CNT-20260112-342', 125, 'olgabercasio18@gmail.com', '09499508447', 'ako', '0907100481', 'Manila, Metro Manila, Philippines', 'Tagbilaran City Friendship Park, Tagbilaran, Bohol, Philippines', 'Manila', NULL, 25.00, 'box', 'kamote', 1160.04, 17400.63, NULL, 'cod', '', 'Pending', 'Pending', NULL, '2026-01-13 06:05:14', NULL, '8 - 10 hours - Moderate Traffic, Dry Season', '2026-01-20 00:00:00', 0, NULL, 'Visayas'),
(196, 'CNT-20260112-342', 125, 'olgabercasio18@gmail.com', '09499508447', 'boy', '0907100481', 'Manila, Metro Manila, Philippines', 'Roxas Airport, Roxas City Airport Bypass Road, Roxas, Capiz, Philippines', 'manila', NULL, 2.00, 'box', 'box', 572.96, 8594.34, NULL, 'online', 'GCash', 'Delivered', 'Met', 'POD_196_1768287815.jpg', '2026-01-13 06:36:36', NULL, '9-10 hours - Express Air Cargo (delivered late evening, Jan 13 or early morning, Jan 14)', '2026-01-20 00:00:00', 4, 'egerg', 'Visayas'),
(197, 'CNT-20260112-342', 125, 'olgabercasio18@gmail.com', '09499508447', 'sdf', '0907100481', 'Quezon City, Metro Manila, Philippines', 'Roxas Airport, Roxas City Airport Bypass Road, Roxas, Capiz, Philippines', 'Roxas Airport, Roxas City Airport Bypass Road, Roxas, Capiz, Philippines', NULL, 12.00, 'box', 'Roxas Airport, Roxas City Airport Bypass Road, Roxas, Capiz, Philippines', 583.39, 8750.90, NULL, 'online', 'GCash', 'In Transit', 'Pending', NULL, '2026-01-13 07:08:09', NULL, 'Approx. 19 hours (Next-Day Delivery) - Moderate Traffic for Initial Ground Transport, Favorable Air ', '2026-01-20 00:00:00', 0, NULL, 'Visayas'),
(198, 'CNT-2026-0127', 127, 'valleroy851@gmail.com', '09499508447', 'klyde', '09071004815', 'Bacoor, Cavite, Philippines', 'Port of Dumaguete, Dumaguete, Negros Oriental, Philippines', 'Negros ', NULL, 5.00, 'box', 'bublee wrap', 1309.76, 19646.40, NULL, 'online', 'GCash', 'Delivered', 'Pending', 'uploads/proofs/proof_198_1768401442.jpg', '2026-01-14 14:36:14', NULL, '', '2026-01-21 00:00:00', 0, NULL, 'Visayas');

-- --------------------------------------------------------

--
-- Table structure for table `shipment_documents`
--

CREATE TABLE `shipment_documents` (
  `id` int(11) NOT NULL,
  `tracking_number` varchar(50) NOT NULL,
  `doc_type` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` varchar(100) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipment_documents`
--

INSERT INTO `shipment_documents` (`id`, `tracking_number`, `doc_type`, `file_name`, `file_path`, `uploaded_by`, `uploaded_at`) VALUES
(1, '189', 'Permit', '189_Permit_1768123829.docx', 'uploads/189_Permit_1768123829.docx', 'Admin', '2026-01-11 17:30:29'),
(2, '188', 'Waybill', '188_Waybill_1768134259.pdf', 'uploads/188_Waybill_1768134259.pdf', 'Admin', '2026-01-11 20:24:19'),
(3, '101', 'Commercial Invoice', '101_CommercialInvoice_1768139328.docx', 'uploads/101_CommercialInvoice_1768139328.docx', 'API User (Sales Dept)', '2026-01-11 21:48:48'),
(4, '101', 'Commercial Invoice', '101_CommercialInvoice_1768139437.docx', 'uploads/101_CommercialInvoice_1768139437.docx', 'API User (Sales Dept)', '2026-01-11 21:50:37'),
(5, '101', 'Commercial Invoice', '101_CommercialInvoice_1768139733.docx', 'uploads/101_CommercialInvoice_1768139733.docx', 'API User (Sales Dept)', '2026-01-11 21:55:33'),
(6, '102', 'Commercial Invoice', '102_CommercialInvoice_1768139863.docx', 'uploads/102_CommercialInvoice_1768139863.docx', 'API User (Sales Dept)', '2026-01-11 21:57:43'),
(7, '101', 'Commercial Invoice', '101_CommercialInvoice_1768229354.docx', 'uploads/101_CommercialInvoice_1768229354.docx', 'API User (Sales Dept)', '2026-01-12 22:49:14'),
(8, '162', 'Commercial Invoice', '162_CommercialInvoice_1768230342.docx', 'uploads/162_CommercialInvoice_1768230342.docx', 'API User (Sales Dept)', '2026-01-12 23:05:42'),
(9, '193', 'Proof of Delivery', '193_ProofofDelivery_1768271360.docx', 'uploads/193_ProofofDelivery_1768271360.docx', 'Admin', '2026-01-13 10:29:20'),
(10, '193', 'Proof of Delivery', '193_ProofofDelivery_1768271532.docx', 'uploads/193_ProofofDelivery_1768271532.docx', 'Admin', '2026-01-13 10:32:12'),
(11, '193', 'Proof of Delivery', '193_ProofofDelivery_1768271726.pdf', 'uploads/193_ProofofDelivery_1768271726.pdf', 'Admin', '2026-01-13 10:35:26'),
(12, '101', 'Commercial Invoice', '101_CommercialInvoice_1768272557.jpg', 'uploads/101_CommercialInvoice_1768272557.jpg', 'API User (Sales Dept)', '2026-01-13 10:49:17'),
(13, '101', 'Commercial Invoice', '101_CommercialInvoice_1768283257.jpg', 'uploads/101_CommercialInvoice_1768283257.jpg', 'API User (Sales Dept)', '2026-01-13 13:47:37'),
(14, '101', 'Commercial Invoice', '101_CommercialInvoice_1768287866.pdf', 'uploads/101_CommercialInvoice_1768287866.pdf', 'API User (Sales Dept)', '2026-01-13 15:04:26');

-- --------------------------------------------------------

--
-- Table structure for table `sla_policies`
--

CREATE TABLE `sla_policies` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `origin_group` varchar(50) NOT NULL,
  `destination_group` varchar(50) NOT NULL,
  `max_days` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sla_policies`
--

INSERT INTO `sla_policies` (`id`, `contract_id`, `origin_group`, `destination_group`, `max_days`) VALUES
(81, 14, 'Luzon', 'Visayas', 7),
(82, 14, 'Luzon', 'Visayas', 7),
(83, 14, 'Luzon', 'Visayas', 7),
(84, 14, 'Luzon', 'Visayas', 7),
(85, 14, 'Luzon', 'Visayas', 7),
(86, 14, 'Luzon', 'Mindanao', 7),
(87, 14, 'Luzon', 'Mindanao', 7),
(88, 14, 'Luzon', 'Mindanao', 7),
(89, 14, 'Metro Manila', 'Metro Manila', 3),
(90, 14, 'Metro Manila', 'Metro Manila', 3),
(91, 14, 'Metro Manila', 'Visayas', 5),
(92, 14, 'Metro Manila', 'Visayas', 5),
(93, 15, 'Luzon', 'Visayas', 7),
(94, 15, 'Luzon', 'Visayas', 7),
(95, 15, 'Luzon', 'Visayas', 7),
(96, 15, 'Luzon', 'Visayas', 7),
(97, 15, 'Luzon', 'Visayas', 7),
(98, 15, 'Luzon', 'Visayas', 7),
(99, 15, 'Luzon', 'Visayas', 7),
(100, 15, 'Luzon', 'Visayas', 7),
(101, 15, 'Luzon', 'Visayas', 7),
(102, 15, 'Luzon', 'Visayas', 7),
(103, 15, 'Luzon', 'Visayas', 7),
(104, 15, 'Luzon', 'Mindanao', 7),
(105, 15, 'Luzon', 'Mindanao', 7),
(106, 15, 'Luzon', 'Mindanao', 7),
(107, 15, 'Metro Manila', 'Metro Manila', 3),
(108, 15, 'Metro Manila', 'Metro Manila', 3),
(109, 15, 'Metro Manila', 'Visayas', 5),
(110, 15, 'Metro Manila', 'Visayas', 5),
(111, 16, 'Luzon', 'Visayas', 7),
(112, 16, 'Luzon', 'Visayas', 7),
(113, 16, 'Luzon', 'Visayas', 7),
(114, 16, 'Luzon', 'Visayas', 7),
(115, 16, 'Luzon', 'Visayas', 7),
(116, 16, 'Luzon', 'Visayas', 7),
(117, 16, 'Luzon', 'Visayas', 7),
(118, 16, 'Luzon', 'Visayas', 7),
(119, 16, 'Luzon', 'Visayas', 7),
(120, 16, 'Luzon', 'Visayas', 7),
(121, 16, 'Luzon', 'Visayas', 7),
(122, 16, 'Luzon', 'Mindanao', 7),
(123, 16, 'Luzon', 'Mindanao', 7),
(124, 16, 'Luzon', 'Mindanao', 7),
(125, 16, 'Metro Manila', 'Metro Manila', 3),
(126, 16, 'Metro Manila', 'Metro Manila', 3),
(127, 16, 'Metro Manila', 'Visayas', 5),
(128, 16, 'Metro Manila', 'Visayas', 5),
(129, 0, 'Metro Manila', 'Metro Manila', 3),
(130, 0, 'Luzon', 'Visayas', 7),
(131, 0, 'Luzon', 'Mindanao', 10),
(132, 0, 'Visayas', 'Mindanao', 7),
(133, 17, 'Metro Manila', 'Metro Manila', 3),
(134, 17, 'Luzon', 'Visayas', 7),
(135, 17, 'Luzon', 'Mindanao', 10),
(136, 17, 'Visayas', 'Mindanao', 7);

-- --------------------------------------------------------

--
-- Table structure for table `user_data`
--

CREATE TABLE `user_data` (
  `data_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_activity`
--
ALTER TABLE `admin_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archive_crm`
--
ALTER TABLE `archive_crm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archive_doc`
--
ALTER TABLE `archive_doc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crm`
--
ALTER TABLE `crm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `csm`
--
ALTER TABLE `csm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_doc`
--
ALTER TABLE `e_doc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_id` (`feedback_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `shipment`
--
ALTER TABLE `shipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipment_documents`
--
ALTER TABLE `shipment_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sla_policies`
--
ALTER TABLE `sla_policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_data`
--
ALTER TABLE `user_data`
  ADD PRIMARY KEY (`data_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=501;

--
-- AUTO_INCREMENT for table `admin_activity`
--
ALTER TABLE `admin_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `archive_crm`
--
ALTER TABLE `archive_crm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `archive_doc`
--
ALTER TABLE `archive_doc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `crm`
--
ALTER TABLE `crm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `csm`
--
ALTER TABLE `csm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `e_doc`
--
ALTER TABLE `e_doc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `shipment_documents`
--
ALTER TABLE `shipment_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sla_policies`
--
ALTER TABLE `sla_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `user_data`
--
ALTER TABLE `user_data`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`);

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_data`
--
ALTER TABLE `user_data`
  ADD CONSTRAINT `user_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
