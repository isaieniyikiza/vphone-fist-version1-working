-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2026 at 07:25 AM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vphone`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_blocks`
--

CREATE TABLE `chat_blocks` (
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_hidden`
--

CREATE TABLE `chat_hidden` (
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_hidden`
--

INSERT INTO `chat_hidden` (`user_id`, `message_id`, `created_at`) VALUES
(2, 38, '2026-07-15 19:41:40'),
(2, 41, '2026-07-15 19:45:47'),
(2, 46, '2026-07-16 06:47:07'),
(2, 47, '2026-07-16 06:47:04'),
(2, 138, '2026-07-23 09:35:54'),
(2, 140, '2026-07-23 09:35:35'),
(4, 63, '2026-07-20 19:13:12'),
(4, 113, '2026-07-20 19:13:10'),
(4, 121, '2026-07-20 19:13:07'),
(6, 138, '2026-07-23 09:36:04'),
(6, 139, '2026-07-23 09:35:24'),
(6, 143, '2026-07-23 09:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `message_type` varchar(20) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `media_url` varchar(255) DEFAULT NULL,
  `reply_to` int(11) DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_seen` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `seen_at` timestamp NULL DEFAULT NULL,
  `reactions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `message_type`, `created_at`, `is_read`, `media_url`, `reply_to`, `edited_at`, `deleted_at`, `is_seen`, `updated_at`, `seen_at`, `reactions`) VALUES
(61, 4, 2, 'hello', 'text', '2026-07-20 13:24:59', 1, NULL, NULL, NULL, '2026-07-20 19:13:18', 1, '2026-07-20 19:13:18', '2026-07-20 13:25:46', NULL),
(62, 4, 2, 'hi', 'text', '2026-07-20 13:25:10', 1, NULL, NULL, NULL, '2026-07-20 19:13:15', 1, '2026-07-20 19:13:15', '2026-07-20 13:25:46', NULL),
(63, 2, 4, 'ok i will tell you to morr', 'text', '2026-07-20 13:25:55', 1, NULL, NULL, NULL, NULL, 1, '2026-07-20 13:25:56', '2026-07-20 13:25:56', NULL),
(64, 4, 2, 'yeah', 'text', '2026-07-20 13:26:07', 1, NULL, NULL, NULL, '2026-07-20 19:05:31', 1, '2026-07-20 19:05:31', '2026-07-20 13:26:07', NULL),
(65, 4, 2, 'hello', 'text', '2026-07-20 13:34:24', 1, NULL, NULL, NULL, '2026-07-20 19:05:27', 1, '2026-07-20 19:05:27', '2026-07-20 13:34:24', NULL),
(66, 2, 6, 'hello', 'text', '2026-07-20 13:38:13', 1, NULL, NULL, NULL, '2026-07-20 14:16:13', 1, '2026-07-20 14:16:13', '2026-07-20 13:38:23', NULL),
(67, 2, 6, 'hi', 'text', '2026-07-20 13:38:27', 1, NULL, NULL, NULL, '2026-07-20 14:16:10', 1, '2026-07-20 14:16:10', '2026-07-20 13:38:27', NULL),
(68, 6, 2, 'hello', 'text', '2026-07-20 13:38:37', 1, NULL, NULL, NULL, '2026-07-20 14:16:04', 1, '2026-07-20 14:16:04', '2026-07-20 13:38:37', NULL),
(69, 6, 2, 'help me to get ai', 'text', '2026-07-20 14:16:50', 1, NULL, NULL, NULL, '2026-07-20 14:19:49', 1, '2026-07-20 14:19:49', '2026-07-20 14:16:50', NULL),
(70, 6, 2, 'hello my frnely', 'text', '2026-07-20 14:19:17', 1, NULL, NULL, NULL, '2026-07-20 14:19:46', 1, '2026-07-20 14:19:46', '2026-07-20 14:19:17', NULL),
(71, 6, 2, 'an you giveme the ai command', 'text', '2026-07-20 14:19:59', 1, NULL, NULL, NULL, '2026-07-20 14:29:38', 1, '2026-07-20 14:29:38', '2026-07-20 14:19:59', NULL),
(72, 6, 2, 'hi', 'text', '2026-07-20 14:20:33', 1, NULL, NULL, NULL, '2026-07-20 14:29:34', 1, '2026-07-20 14:29:34', '2026-07-20 14:20:41', NULL),
(73, 6, 2, 'hello', 'text', '2026-07-20 14:22:57', 1, NULL, NULL, NULL, '2026-07-20 14:29:29', 1, '2026-07-20 14:29:29', '2026-07-20 14:22:57', '{\"6\":\"🙏\"}'),
(74, 2, 6, 'hello my friendly', 'text', '2026-07-20 14:29:54', 1, NULL, NULL, NULL, '2026-07-20 14:38:14', 1, '2026-07-20 14:38:14', '2026-07-20 14:29:54', NULL),
(75, 6, 2, 'yeah i  am good', 'text', '2026-07-20 14:30:04', 1, NULL, NULL, NULL, '2026-07-20 14:38:09', 1, '2026-07-20 14:38:09', '2026-07-20 14:30:04', NULL),
(76, 6, 2, 'tell me some message i gate', 'text', '2026-07-20 14:30:16', 1, NULL, NULL, NULL, '2026-07-20 14:38:06', 1, '2026-07-20 14:38:06', '2026-07-20 14:30:16', NULL),
(77, 2, 6, 'kimana', 'text', '2026-07-20 14:30:46', 1, NULL, NULL, NULL, '2026-07-20 14:38:02', 1, '2026-07-20 14:38:02', '2026-07-20 14:30:46', NULL),
(78, 2, 6, 'hi', 'text', '2026-07-20 14:30:48', 1, NULL, NULL, NULL, '2026-07-20 14:37:59', 1, '2026-07-20 14:37:59', '2026-07-20 14:30:48', NULL),
(86, 6, 2, 'givemthe message', 'text', '2026-07-20 14:38:30', 1, NULL, NULL, NULL, '2026-07-20 14:48:24', 1, '2026-07-20 14:48:24', '2026-07-20 14:38:30', NULL),
(87, 6, 2, 'hi', 'text', '2026-07-20 14:48:30', 1, NULL, NULL, NULL, '2026-07-20 18:19:18', 1, '2026-07-20 18:19:18', '2026-07-20 14:48:30', NULL),
(88, 6, 2, 'hello', 'text', '2026-07-20 14:48:43', 1, NULL, NULL, NULL, '2026-07-20 18:19:13', 1, '2026-07-20 18:19:13', '2026-07-20 14:48:44', NULL),
(89, 6, 2, 'in nned to mak ai', 'text', '2026-07-20 14:49:01', 1, NULL, NULL, NULL, '2026-07-20 18:19:08', 1, '2026-07-20 18:19:08', '2026-07-20 14:49:03', NULL),
(90, 2, 6, 'yah i nned you', 'text', '2026-07-20 14:50:01', 1, NULL, NULL, NULL, '2026-07-20 18:18:59', 1, '2026-07-20 18:18:59', '2026-07-20 14:50:01', NULL),
(91, 2, 6, 'we bit', 'text', '2026-07-20 14:56:57', 1, NULL, NULL, NULL, '2026-07-20 18:18:55', 1, '2026-07-20 18:18:55', '2026-07-20 14:57:20', NULL),
(92, 6, 2, 'ni frsh', 'text', '2026-07-20 14:57:23', 1, NULL, NULL, NULL, '2026-07-20 18:19:05', 1, '2026-07-20 18:19:05', '2026-07-20 14:57:24', NULL),
(93, 2, 6, 'tell me th message', 'text', '2026-07-20 17:47:33', 1, NULL, NULL, NULL, '2026-07-20 18:18:47', 1, '2026-07-20 18:18:47', '2026-07-20 17:48:01', NULL),
(94, 2, 6, 'yeah are you good', 'text', '2026-07-20 17:48:15', 1, NULL, NULL, NULL, '2026-07-20 18:18:42', 1, '2026-07-20 18:18:42', '2026-07-20 17:48:28', NULL),
(95, 2, 6, 'i ma good', 'text', '2026-07-20 17:48:26', 1, NULL, NULL, NULL, '2026-07-20 18:18:39', 1, '2026-07-20 18:18:39', '2026-07-20 17:48:28', NULL),
(96, 2, 6, 'hi', 'text', '2026-07-20 18:19:29', 1, NULL, NULL, NULL, '2026-07-21 18:53:45', 1, '2026-07-21 18:53:45', '2026-07-20 18:19:34', NULL),
(97, 6, 2, 'hello', 'text', '2026-07-20 18:19:39', 1, NULL, NULL, NULL, '2026-07-21 18:53:41', 1, '2026-07-21 18:53:41', '2026-07-20 18:19:39', NULL),
(98, 6, 2, 'are you good bro', 'text', '2026-07-20 18:19:49', 1, NULL, NULL, NULL, '2026-07-20 18:39:49', 1, '2026-07-20 18:39:49', '2026-07-20 18:19:51', NULL),
(99, 6, 2, 'are you goosd', 'text', '2026-07-20 18:23:28', 1, NULL, NULL, NULL, '2026-07-20 18:32:01', 1, '2026-07-20 18:32:01', '2026-07-20 18:23:28', NULL),
(100, 6, 2, 'hi', 'text', '2026-07-20 18:31:51', 1, NULL, NULL, NULL, '2026-07-20 18:31:56', 1, '2026-07-20 18:34:02', '2026-07-20 18:34:02', NULL),
(101, 6, 2, 'hello', 'text', '2026-07-20 18:32:05', 1, NULL, NULL, NULL, '2026-07-20 18:39:45', 1, '2026-07-20 18:39:45', '2026-07-20 18:34:02', NULL),
(102, 6, 2, 'hi', 'text', '2026-07-20 18:38:06', 1, NULL, NULL, NULL, '2026-07-20 18:39:42', 1, '2026-07-20 18:39:42', '2026-07-20 18:38:06', NULL),
(103, 6, 2, 'hi', 'text', '2026-07-20 18:39:51', 1, NULL, NULL, NULL, '2026-07-21 18:53:39', 1, '2026-07-21 18:53:39', '2026-07-20 18:39:53', NULL),
(104, 6, 2, 'hello', 'text', '2026-07-20 18:39:59', 1, NULL, NULL, NULL, '2026-07-21 18:53:34', 1, '2026-07-21 18:53:34', '2026-07-20 18:39:59', NULL),
(105, 6, 2, 'hi', 'text', '2026-07-20 18:40:02', 1, NULL, NULL, NULL, '2026-07-21 18:53:32', 1, '2026-07-21 18:53:32', '2026-07-20 18:40:02', NULL),
(106, 2, 6, 'ok mercii bokk ☺️☺️😘', 'text', '2026-07-20 18:47:34', 1, NULL, NULL, NULL, '2026-07-21 18:53:26', 1, '2026-07-21 18:53:26', '2026-07-20 18:47:34', NULL),
(107, 6, 2, 'but are you working well', 'text', '2026-07-20 18:48:05', 1, NULL, NULL, NULL, '2026-07-21 18:53:20', 1, '2026-07-21 18:53:20', '2026-07-20 18:48:05', NULL),
(108, 2, 6, 'hello', 'text', '2026-07-20 18:49:04', 1, NULL, NULL, NULL, '2026-07-21 10:52:37', 1, '2026-07-21 10:52:37', '2026-07-20 18:49:04', NULL),
(109, 6, 2, 'yeha', 'text', '2026-07-20 18:49:16', 1, NULL, NULL, NULL, '2026-07-21 10:52:32', 1, '2026-07-21 10:52:32', '2026-07-20 18:49:16', NULL),
(110, 2, 6, 'ok', 'text', '2026-07-20 18:49:26', 1, NULL, NULL, NULL, '2026-07-21 10:52:29', 1, '2026-07-21 10:52:29', '2026-07-20 18:49:26', NULL),
(111, 2, 6, 'tekll me what you want', 'text', '2026-07-20 18:49:31', 1, NULL, NULL, NULL, '2026-07-21 10:52:25', 1, '2026-07-21 10:52:25', '2026-07-20 18:49:31', NULL),
(112, 2, 6, 'are you doing fresh', 'text', '2026-07-20 18:49:36', 1, NULL, NULL, NULL, '2026-07-21 10:52:22', 1, '2026-07-21 10:52:22', '2026-07-20 18:49:36', NULL),
(113, 2, 4, 'what is ai', 'text', '2026-07-20 18:49:58', 1, NULL, NULL, NULL, NULL, 1, '2026-07-20 18:50:00', '2026-07-20 18:50:00', NULL),
(114, 4, 2, 'yeha', 'text', '2026-07-20 18:52:39', 1, NULL, NULL, NULL, '2026-07-20 19:05:24', 1, '2026-07-20 19:05:24', '2026-07-20 18:52:39', NULL),
(115, 4, 2, 'ok', 'text', '2026-07-20 18:52:48', 1, NULL, NULL, NULL, '2026-07-20 19:05:19', 1, '2026-07-20 19:05:19', '2026-07-20 18:52:48', NULL),
(116, 4, 2, 'merciii', 'text', '2026-07-20 18:52:50', 1, NULL, NULL, NULL, '2026-07-20 19:05:14', 1, '2026-07-20 19:05:14', '2026-07-20 18:52:50', NULL),
(117, 4, 2, 'we sh', 'text', '2026-07-20 18:59:47', 1, NULL, NULL, NULL, '2026-07-20 19:05:17', 1, '2026-07-20 19:05:17', '2026-07-20 19:02:49', NULL),
(118, 6, 2, 'hello', 'text', '2026-07-20 19:01:52', 1, NULL, NULL, NULL, '2026-07-21 10:52:19', 1, '2026-07-21 10:52:19', '2026-07-20 19:02:45', NULL),
(119, 4, 2, 'HI', 'text', '2026-07-20 19:05:07', 1, NULL, NULL, NULL, '2026-07-20 19:05:11', 1, '2026-07-20 19:12:10', '2026-07-20 19:12:10', NULL),
(120, 4, 2, 'HELO', 'text', '2026-07-20 19:05:41', 1, NULL, NULL, NULL, '2026-07-20 19:13:05', 1, '2026-07-20 19:13:05', '2026-07-20 19:12:10', NULL),
(121, 2, 4, 'i ma good', 'text', '2026-07-20 19:12:16', 1, NULL, NULL, NULL, NULL, 1, '2026-07-20 19:12:59', '2026-07-20 19:12:59', NULL),
(122, 2, 4, 'hello', 'text', '2026-07-20 19:20:33', 1, NULL, NULL, NULL, NULL, 1, '2026-07-20 19:22:21', '2026-07-20 19:22:21', NULL),
(123, 2, 6, 'hi', 'text', '2026-07-20 19:29:34', 1, NULL, NULL, NULL, '2026-07-21 10:52:14', 1, '2026-07-21 10:52:14', '2026-07-20 19:35:53', NULL),
(124, 2, 6, 'hello', 'text', '2026-07-20 19:37:01', 1, NULL, NULL, NULL, '2026-07-21 10:52:12', 1, '2026-07-21 10:52:12', '2026-07-20 19:37:01', NULL),
(125, 2, 6, 'are you  goo', 'text', '2026-07-20 19:37:09', 1, NULL, NULL, NULL, '2026-07-21 10:52:09', 1, '2026-07-21 10:52:09', '2026-07-20 19:41:30', NULL),
(126, 2, 6, 'we sh', 'text', '2026-07-20 19:41:06', 1, NULL, NULL, NULL, '2026-07-21 10:52:05', 1, '2026-07-21 10:52:05', '2026-07-20 19:41:30', NULL),
(127, 2, 6, 'hello', 'text', '2026-07-20 19:44:50', 1, NULL, NULL, NULL, '2026-07-21 10:52:01', 1, '2026-07-21 10:52:01', '2026-07-20 19:45:56', NULL),
(128, 2, 6, 'share this vedeo wth   anther more people', 'text', '2026-07-21 10:50:46', 1, NULL, NULL, NULL, '2026-07-21 10:51:58', 1, '2026-07-21 10:51:58', '2026-07-21 10:51:48', NULL),
(129, 2, 6, 'share this vedeo wth   anther more people', 'text', '2026-07-21 10:50:46', 1, NULL, NULL, NULL, '2026-07-21 10:51:56', 1, '2026-07-21 10:51:56', '2026-07-21 10:51:48', NULL),
(130, 2, 6, 'this is the our   vatual machine chat', 'text', '2026-07-21 10:53:44', 1, NULL, NULL, NULL, '2026-07-21 18:53:15', 1, '2026-07-21 18:53:15', '2026-07-21 10:53:51', NULL),
(131, 2, 6, 'hello  my', 'text', '2026-07-21 10:54:47', 1, NULL, NULL, NULL, '2026-07-21 10:54:55', 1, '2026-07-21 10:56:05', '2026-07-21 10:56:05', NULL),
(132, 2, 6, 'o love you guys', 'text', '2026-07-21 10:55:56', 1, NULL, NULL, NULL, '2026-07-21 18:53:11', 1, '2026-07-21 18:53:11', '2026-07-21 10:56:05', NULL),
(133, 2, 6, 'i am  making it professinal like real phone', 'text', '2026-07-21 10:57:09', 1, NULL, NULL, NULL, '2026-07-21 18:53:05', 1, '2026-07-21 18:53:05', '2026-07-21 10:57:19', NULL),
(134, 2, 6, 'share this vedeo with anathers', 'text', '2026-07-21 10:58:36', 1, NULL, NULL, NULL, '2026-07-21 18:53:02', 1, '2026-07-21 18:53:02', '2026-07-21 10:58:50', NULL),
(135, 2, 6, 'share this vedeo with anathers', 'text', '2026-07-21 10:58:39', 1, NULL, NULL, NULL, '2026-07-21 18:52:59', 1, '2026-07-21 18:52:59', '2026-07-21 10:58:50', NULL),
(136, 6, 2, 'tell me what you need', 'text', '2026-07-21 18:54:05', 1, NULL, NULL, NULL, '2026-07-21 18:57:11', 1, '2026-07-21 18:57:11', '2026-07-21 18:54:05', NULL),
(137, 2, 5, 'hi', 'text', '2026-07-22 19:04:14', 1, NULL, NULL, NULL, NULL, 1, '2026-07-22 19:04:20', '2026-07-22 19:04:20', NULL),
(138, 2, 6, 'hello', 'text', '2026-07-23 08:34:05', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 08:35:54', '2026-07-23 08:35:54', NULL),
(139, 2, 6, 'hi', 'text', '2026-07-23 08:50:32', 1, NULL, NULL, NULL, '2026-07-23 09:35:52', 1, '2026-07-23 09:35:52', '2026-07-23 08:50:35', NULL),
(140, 6, 2, '☺️', 'text', '2026-07-23 08:50:46', 1, NULL, NULL, NULL, '2026-07-23 09:36:02', 1, '2026-07-23 09:36:02', '2026-07-23 08:50:46', NULL),
(141, 6, 2, '', 'image', '2026-07-23 09:10:30', 1, '/virtual-phone/uploads/messages/1784797830422-256583236.png', NULL, NULL, '2026-07-23 09:35:40', 1, '2026-07-23 09:35:40', '2026-07-23 09:10:46', '{\"6\":\"😂\"}'),
(142, 2, 6, 'yeha', 'text', '2026-07-23 09:10:53', 1, NULL, NULL, NULL, '2026-07-23 09:35:49', 1, '2026-07-23 09:35:49', '2026-07-23 09:10:54', NULL),
(143, 2, 6, 'ok', 'text', '2026-07-23 09:10:59', 1, NULL, NULL, NULL, '2026-07-23 09:35:46', 1, '2026-07-23 09:35:46', '2026-07-23 09:10:59', NULL),
(144, 6, 2, 'hello', 'text', '2026-07-23 09:50:32', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 09:50:41', '2026-07-23 09:50:41', NULL),
(145, 2, 6, 'hi bro', 'text', '2026-07-23 09:50:45', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 09:50:45', '2026-07-23 09:50:45', NULL),
(146, 2, 6, 'are you good', 'text', '2026-07-23 09:50:51', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 09:50:58', '2026-07-23 09:50:51', '{\"6\":\"👍\"}'),
(147, 6, 2, 'i ms playing foot ball', 'text', '2026-07-23 09:51:05', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 09:51:05', '2026-07-23 09:51:05', NULL),
(148, 3, 6, 'hi', 'text', '2026-07-23 09:53:22', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 09:53:32', '2026-07-23 09:53:32', NULL),
(149, 6, 2, 'we bit', 'text', '2026-07-23 11:31:39', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 11:31:46', '2026-07-23 11:31:46', NULL),
(150, 4, 6, 'hi', 'text', '2026-07-23 15:21:59', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 16:03:25', '2026-07-23 16:03:25', NULL),
(151, 4, 6, 'hello', 'text', '2026-07-23 15:22:05', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 16:03:25', '2026-07-23 16:03:25', NULL),
(152, 5, 6, 'hi', 'text', '2026-07-23 16:43:52', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 16:43:55', '2026-07-23 16:43:55', NULL),
(153, 6, 5, 'hello my frniely', 'text', '2026-07-23 16:43:59', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 16:43:59', '2026-07-23 16:43:59', NULL),
(154, 6, 5, 'are you goos', 'text', '2026-07-23 16:44:02', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 16:44:02', '2026-07-23 16:44:02', NULL),
(155, 5, 6, 'yeah', 'text', '2026-07-23 16:44:18', 1, NULL, NULL, NULL, NULL, 1, '2026-07-23 16:44:41', '2026-07-23 16:44:19', '{\"6\":\"👍\"}'),
(156, 6, 5, 'hi are you typing', 'text', '2026-07-24 11:20:13', 1, NULL, NULL, NULL, NULL, 1, '2026-07-24 11:20:16', '2026-07-24 11:20:16', NULL),
(158, 2, 22, 'hello', 'text', '2026-11-07 12:35:11', 1, NULL, NULL, NULL, NULL, 1, '2026-11-07 12:35:15', '2026-11-07 12:35:15', NULL),
(159, 22, 2, 'hi are you good bro', 'text', '2026-11-07 12:35:23', 1, NULL, 158, NULL, NULL, 1, '2026-11-07 12:35:23', '2026-11-07 12:35:23', NULL),
(160, 6, 22, 'bit zx mwn', 'text', '2026-11-10 19:01:47', 1, NULL, NULL, NULL, NULL, 1, '2026-11-10 19:01:52', '2026-11-10 19:01:52', NULL),
(161, 22, 6, 'heloo i am good br', 'text', '2026-11-10 19:02:01', 1, NULL, NULL, NULL, NULL, 1, '2026-11-10 19:02:01', '2026-11-10 19:02:01', NULL),
(162, 22, 2, 'we  isaiw bimez bit x mwn', 'text', '2026-11-11 06:59:49', 1, NULL, NULL, NULL, NULL, 1, '2026-11-11 07:00:35', '2026-11-11 07:00:35', NULL),
(163, 22, 2, 'ni saw x Gee', 'text', '2026-11-11 07:00:09', 1, NULL, NULL, NULL, NULL, 1, '2026-11-11 07:00:35', '2026-11-11 07:00:35', NULL),
(164, 2, 22, 'eee  nage ntrb', 'text', '2026-11-11 07:01:16', 1, NULL, 163, NULL, NULL, 1, '2026-11-11 07:01:16', '2026-11-11 07:01:16', NULL),
(165, 22, 2, 'hhh  nonex ko nakubuz mwn', 'text', '2026-11-11 07:01:28', 1, NULL, 164, NULL, NULL, 1, '2026-11-11 07:01:28', '2026-11-11 07:01:28', NULL),
(166, 2, 22, 'igo x warambuz', 'text', '2026-11-11 07:01:45', 1, NULL, NULL, NULL, NULL, 1, '2026-11-11 07:01:45', '2026-11-11 07:01:45', NULL),
(167, 22, 2, 'cyn ahibw', 'text', '2026-11-11 07:01:50', 1, NULL, NULL, NULL, NULL, 1, '2026-11-11 07:01:50', '2026-11-11 07:01:50', NULL),
(168, 22, 2, 'nza kubwira ntrb', 'text', '2026-11-11 07:02:01', 1, NULL, NULL, NULL, NULL, 1, '2026-11-11 07:02:18', '2026-11-11 07:02:18', NULL),
(169, 22, 2, 'eheeee', 'text', '2026-11-11 07:02:46', 0, NULL, NULL, NULL, NULL, 0, '2026-11-11 07:02:46', NULL, NULL),
(170, 4, 2, 'HELLO    umez utx mwn', 'text', '2026-11-20 12:58:23', 1, NULL, NULL, NULL, NULL, 1, '2026-11-20 12:59:31', '2026-11-20 12:59:31', NULL),
(171, 2, 4, 'ni  sw ntrb', 'text', '2026-11-20 12:59:38', 1, NULL, NULL, NULL, NULL, 1, '2026-11-20 12:59:38', '2026-11-20 12:59:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_stars`
--

CREATE TABLE `chat_stars` (
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_stars`
--

INSERT INTO `chat_stars` (`user_id`, `message_id`, `created_at`) VALUES
(4, 57, '2026-07-19 06:51:19');

-- --------------------------------------------------------

--
-- Table structure for table `chat_typing`
--

CREATE TABLE `chat_typing` (
  `user_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_typing`
--

INSERT INTO `chat_typing` (`user_id`, `receiver_id`, `updated_at`) VALUES
(2, 3, '2026-07-15 19:08:41'),
(3, 2, '2026-07-15 19:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `user_id`, `name`, `number`, `photo`, `created_at`) VALUES
(6, 2, 'kimana', '0782828282', '', '2026-07-19 13:45:14'),
(7, 2, 'kimana', '0987654567', '', '2026-07-19 13:57:26'),
(8, 2, 'kigama', '0792987656', '', '2026-07-19 14:06:15'),
(9, 2, 'IT Teachnician nyacyina', '0790758612', '', '2026-09-04 10:12:11'),
(10, 6, 'It nyacyina', '0790758612', '', '2026-09-09 15:54:47'),
(11, 6, 'It nyacyina', '0790758612', '', '2026-09-09 15:54:47'),
(12, 6, 'VIE', '0798802971', '', '2026-09-09 16:05:50'),
(13, 6, 'VIE', '072744247', '', '2026-09-09 16:06:15'),
(14, 6, 'VIE', '0782827795', '', '2026-09-09 16:06:45'),
(15, 6, 'MAAM', '0724340678', '', '2026-09-09 16:07:38'),
(16, 6, 'FOR', '0783380100', '', '2026-09-09 16:08:05'),
(17, 22, 'manzii', '0798901713', '', '2026-09-14 13:43:37'),
(18, 22, 'comand  winget install --id VideoLAN.VLC -e', '123456543', '', '2026-10-04 13:26:36'),
(19, 22, 'comand  winget install --id VideoLAN.VLC -e', '123456543', '', '2026-10-04 13:26:36'),
(20, 22, 'comand  winget install --id VideoLAN.VLC -e', '123456543', '', '2026-10-04 13:26:37'),
(21, 22, 'comand  winget install --id VideoLAN.VLC -e', '123456543', '', '2026-10-04 13:26:37'),
(22, 22, 'isaie', '07929711', '', '2026-10-12 16:04:04'),
(23, 22, 'Faddy', '0791086657', '', '2026-10-24 16:17:56'),
(24, 28, 'fghj', '078556666', '', '2026-11-02 15:04:22'),
(25, 22, 'muhizi_bvbu  pwd \"@gixakind\"', '1234567', '', '2026-11-04 10:14:44'),
(26, 22, 'ga.di720  pwd \"gad75jesus\"', '1234567', '', '2026-11-04 10:15:23'),
(27, 22, 'hirwayakini@gmail.com  PWD\"diella\"', '123456543', '', '2026-11-04 10:17:47'),
(28, 22, 'gitosuggy  \"101010@@\"', '234565432', '', '2026-11-04 10:19:00'),
(29, 22, 'fb\"0794037108\"', 'peace joy', '', '2026-11-04 10:27:51'),
(30, 22, 'from pynput import keyboard from plyer import notification  print(\"Space Detector is running...\") pr', '123456', '', '2026-11-04 17:04:38'),
(31, 22, 'kendrill 45  pwdkennedy 12345', '234565432', '', '2026-11-05 08:20:22'),
(32, 22, 'musabyimanaclaudien077@gmail.com  pwd avengers1234567', '123456543', '', '2026-11-05 08:20:45'),
(33, 2, 'isaie', '079204554', '', '2026-11-07 12:59:34');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `time` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `done` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `event_date`, `title`, `time`, `location`, `notes`, `tags`, `done`, `created_at`) VALUES
(1, 4, '2026-07-18', 'visit my mom', '12:11', 'sdkalfj', 'will go', 'no', 1, '2026-07-16 08:18:36'),
(2, 4, '2026-07-16', 'visi', 'sdf', 'asd', 'asd', 'asd', 1, '2026-07-16 08:26:47'),
(3, 4, '2026-07-17', 'kim', '12:12', 'dsafklj;', 'i will do  the ai allin', 'dsafjkl', 1, '2026-07-17 04:58:58'),
(4, 2, '2026-07-17', 'kim', 'sadfhkj', 'sadjkf', 'dsafkl', 'dsafk', 1, '2026-07-17 06:10:43'),
(5, 2, '2026-07-08', 'ki', 'ki', 'waeljodf', 'sdafjl', 'jsdafljk', 1, '2026-07-18 18:13:54');

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 7, 'pending', '2026-07-23 09:26:26', '2026-07-23 09:26:26'),
(5, 6, 4, 'declined', '2026-07-23 15:13:29', '2026-07-23 15:14:27'),
(9, 2, 11, 'pending', '2026-11-07 12:21:06', '2026-11-07 12:21:06'),
(10, 22, 2, 'accepted', '2026-11-07 12:34:57', '2026-11-07 12:35:08'),
(11, 6, 5, 'pending', '2026-11-07 14:07:30', '2026-11-07 14:07:30'),
(13, 4, 2, 'accepted', '2026-11-20 12:57:38', '2026-11-20 12:57:55');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `created_by`, `avatar`, `created_at`) VALUES
(2, 'family', 6, '/virtual-phone/uploads/groups/1784823780927-951280711.png', '2026-07-23 16:23:00'),
(3, 'gruop', 6, '/virtual-phone/uploads/groups/1784823939334-33225036.png', '2026-07-23 16:25:39'),
(4, 'kigali', 5, '/virtual-phone/uploads/groups/1784825108296-723531471.png', '2026-07-23 16:45:08'),
(5, 'coding', 6, NULL, '2026-07-25 19:05:41');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','active','declined') DEFAULT 'pending',
  `invited_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`group_id`, `user_id`, `status`, `invited_by`, `created_at`) VALUES
(1, 2, 'pending', 6, '2026-07-23 08:51:39'),
(1, 6, 'active', 6, '2026-07-23 08:51:39'),
(2, 5, 'pending', 6, '2026-07-23 16:23:00'),
(2, 6, 'active', 6, '2026-07-23 16:23:00'),
(2, 7, 'pending', 6, '2026-07-23 16:23:00'),
(3, 4, 'pending', 6, '2026-07-23 16:25:39'),
(3, 6, 'active', 6, '2026-07-23 16:25:39'),
(4, 3, 'pending', 5, '2026-07-23 16:45:08'),
(4, 5, 'active', 5, '2026-07-23 16:45:08'),
(5, 2, 'pending', 6, '2026-07-25 19:05:41'),
(5, 5, 'pending', 6, '2026-07-25 19:05:41'),
(5, 6, 'active', 6, '2026-07-25 19:05:41'),
(5, 7, 'pending', 6, '2026-07-25 19:05:41');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `media_type` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `media_url`, `media_type`, `created_at`, `updated_at`) VALUES
(2, 2, 'get one', '/virtual-phone/uploads/posts/1784795743372-676024103.png', 'image', '2026-07-23 08:35:43', '2026-07-23 08:35:43'),
(3, 5, 'the new lathing', '/virtual-phone/uploads/posts/1784825178657-81733164.mp4', 'video', '2026-07-23 16:46:18', '2026-07-23 16:46:18'),
(13, 1, 'shjfkl', '/virtual-phone/uploads/posts/post_6a65ef0f53f887.51945461.png', 'image', '2026-07-26 11:27:11', '2026-07-26 11:27:11'),
(14, 44, 'dsafjkla', '/virtual-phone/uploads/posts/post_6a65ef480f2319.18859127.jpg', 'image', '2026-07-26 11:28:08', '2026-07-26 11:28:08'),
(15, 12, 'dsahflkjhjkaslf', '/virtual-phone/uploads/posts/post_6a65f5c96b3f1.png', 'image', '2026-07-26 11:55:53', '2026-07-26 11:55:53'),
(16, 1, 'thiai mew post message', '/virtual-phone/uploads/posts/1785067360-217307.png', 'image', '2026-07-26 12:02:40', '2026-07-26 12:02:40'),
(17, 1, 'dslajfksdaf', 'post/uploads/posts/1785067763-488216.png', 'image', '2026-07-26 12:09:23', '2026-07-26 12:09:23'),
(18, 6, 'kiman', '/virtual-phone/uploads/posts/post_6a65f9bbd19992.74780831.png', 'image', '2026-07-26 12:12:43', '2026-07-26 12:12:43'),
(19, 6, 'sdajkfl', '/virtual-phone/uploads/posts/post_6a65fa0e96d549.27645125.png', 'image', '2026-07-26 12:14:06', '2026-07-26 12:14:06'),
(21, 6, 'kim', '/virtual-phone/uploads/posts/post_6a65fb0c2007c6.66127148.png', 'image', '2026-07-26 12:18:20', '2026-07-26 12:18:20'),
(24, 2, 'this is mypos', '/virtual-phone/uploads/posts/1794055278763-431768143.jfif', 'image', '2026-11-07 12:41:18', '2026-11-07 12:41:18'),
(25, 22, 'my post about the  innovation club', '/virtual-phone/uploads/posts/1794337594480-87854557.jpg', 'image', '2026-11-10 19:06:34', '2026-11-10 19:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `comment`, `created_at`) VALUES
(3, 1, 6, 'hello', '2026-07-23 08:57:32'),
(4, 1, 6, 'dsafhjklahsfjkdsa', '2026-07-23 08:57:36'),
(5, 2, 2, 'KASHD', '2026-07-23 09:05:09'),
(6, 2, 6, 'kii', '2026-07-23 09:27:23'),
(7, 2, 6, 'hello', '2026-07-23 14:52:24'),
(8, 1, 4, 'hi', '2026-07-23 15:22:41'),
(9, 1, 6, 'hi', '2026-07-24 14:04:19'),
(10, 1, 6, 'hi', '2026-07-24 14:11:06'),
(11, 2, 6, 'hello', '2026-07-24 14:11:10'),
(12, 1, 6, 'dasfjlk', '2026-07-25 17:47:33'),
(13, 2, 6, 'dskjkfla', '2026-07-26 09:58:39'),
(14, 3, 6, 'sdajkls;adfjs', '2026-07-26 09:58:46'),
(15, 2, 6, 'kim are you foo', '2026-07-26 10:00:24'),
(21, 24, 2, 'bu', '2026-11-07 12:41:32'),
(25, 24, 22, 'feed-ui-fixed-1794055958157', '2026-11-07 12:52:38'),
(27, 17, 22, 'jhhhh  areyou  fulish', '2026-11-10 16:22:15'),
(28, 25, 6, 'cool bro', '2026-11-10 19:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`post_id`, `user_id`, `created_at`) VALUES
(1, 2, '2026-07-23 08:35:22'),
(2, 2, '2026-07-23 08:51:20'),
(2, 4, '2026-07-23 16:04:09'),
(2, 22, '2026-11-07 12:44:38'),
(18, 22, '2026-11-10 16:22:06'),
(21, 2, '2026-11-07 12:21:32'),
(21, 22, '2026-11-07 12:53:41'),
(24, 2, '2026-11-07 12:41:24'),
(24, 22, '2026-11-07 16:13:59'),
(25, 4, '2026-11-20 12:59:53'),
(25, 6, '2026-11-10 19:07:17'),
(25, 22, '2026-11-10 19:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `post_views`
--

CREATE TABLE `post_views` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_views`
--

INSERT INTO `post_views` (`post_id`, `user_id`, `viewed_at`) VALUES
(1, 2, '2026-07-23 08:35:13'),
(1, 4, '2026-07-23 15:22:10'),
(1, 5, '2026-07-23 11:31:00'),
(1, 6, '2026-07-23 08:35:58'),
(2, 2, '2026-07-23 08:35:43'),
(2, 4, '2026-07-23 15:22:09'),
(2, 5, '2026-07-23 11:30:59'),
(2, 6, '2026-07-26 10:28:01'),
(2, 22, '2026-11-07 12:44:36'),
(3, 2, '2026-11-07 12:21:35'),
(3, 5, '2026-07-23 16:46:19'),
(3, 6, '2026-07-26 10:28:01'),
(3, 22, '2026-11-07 12:44:36'),
(13, 2, '2026-11-07 12:21:21'),
(13, 22, '2026-11-07 12:44:36'),
(14, 2, '2026-11-07 12:21:20'),
(14, 22, '2026-11-07 12:44:35'),
(15, 2, '2026-11-07 12:21:20'),
(15, 22, '2026-11-07 12:44:35'),
(16, 2, '2026-11-07 12:21:20'),
(16, 4, '2026-11-07 13:21:25'),
(16, 22, '2026-11-07 12:44:35'),
(17, 2, '2026-11-07 12:21:19'),
(17, 4, '2026-11-07 13:21:25'),
(17, 22, '2026-11-07 12:44:22'),
(18, 2, '2026-11-07 12:21:19'),
(18, 4, '2026-11-07 13:21:24'),
(18, 22, '2026-11-07 12:44:22'),
(19, 2, '2026-11-07 12:21:15'),
(19, 4, '2026-11-07 13:21:24'),
(19, 6, '2026-11-10 19:07:20'),
(19, 22, '2026-11-07 12:44:13'),
(21, 2, '2026-11-07 12:21:15'),
(21, 4, '2026-11-07 13:21:24'),
(21, 6, '2026-11-10 19:07:20'),
(21, 22, '2026-11-07 12:42:34'),
(24, 2, '2026-11-07 12:41:18'),
(24, 4, '2026-11-07 13:21:23'),
(24, 6, '2026-11-10 19:07:19'),
(24, 22, '2026-11-07 12:42:34'),
(25, 4, '2026-11-20 12:59:49'),
(25, 6, '2026-11-10 19:06:46'),
(25, 22, '2026-11-10 19:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NULL DEFAULT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `storage_used_mb` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `email` varchar(254) DEFAULT NULL,
  `google_sub` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `profile_image`, `created_at`, `last_seen`, `is_blocked`, `storage_used_mb`, `email`, `google_sub`) VALUES
(2, 'isaie', '$2y$10$Opq0PihSmEEz2YBhyKTSR.Qrovc7QYbBeGRPEf6AVez6xcohPLZN6', '6a5653020ea09.png', '2026-07-14 15:17:22', '2026-11-21 05:05:30', 0, 0, NULL, NULL),
(3, 'sally', '$2y$10$VA7QJMm09zNQSxVmr2jFh.xn/lfBXe2rXuQQ1k0uO0K5hfDxwuNK6', '6a572e2e62511.png', '2026-07-15 06:52:30', '2026-07-23 11:30:10', 0, 0, NULL, NULL),
(4, 'kim', '$2y$10$Ns5F6f98Nf/ohI1Io0QSw.3dkRog0CIcOHWzb23zATtXpCUxD7vZu', '/virtual-phone/uploads/profile/profile_6a5c74e00e0cf1.94494836.png', '2026-07-15 19:48:56', '2026-11-20 13:00:19', 0, 0, NULL, NULL),
(5, 'emmy', '$2y$10$eshSv1x.2HwY9n15Wp6gSOJ/e.C2DzrSyeiMmT5O0oPEXQ4Vf5fTq', '6a57e47caf510.jpg', '2026-07-15 19:50:21', '2026-07-25 19:06:07', 0, 0, NULL, NULL),
(6, 'bob', '$2y$10$ngNc3AteIZL3S37LSi0nTO7Q.UwbBshkcfBh.MYF1AZ7narg1mHOO', '6a5a0c81d1fc3.jpeg', '2026-07-17 11:05:37', '2026-11-10 19:08:22', 0, 0, NULL, NULL),
(7, 'admin', '$2y$10$qq4CnhAzF8SsghaVN22nYeHk3AN35hGm0Z7vKiNzZfByd.TDQAyNG', '6a5c733dc76c5.png', '2026-07-19 06:48:30', NULL, 0, 0, NULL, NULL),
(8, 'hello', '$2y$10$9/GLsdKQjBX1cGTTqe/Zg.TyiT4NpU0T2T9TZzBGSDes58X4y0vBe', '6a6637dc5ea38_Gemini_Generated_Image_o5lwjvo5lwjvo5lw.png', '2026-07-26 16:37:47', NULL, 0, 0, NULL, NULL),
(9, '@elda', '$2y$10$4b/LBmVpUd6/cpYsspm0bePc/Sx6Y/agGECCjTPtMJWU3bcwq/LIe', '6a678398ed14e_inbound282161816312741521.jpg', '2026-07-27 16:13:12', NULL, 0, 0, NULL, NULL),
(10, 'Ndayisenga Ismael', '$2y$10$lDEDxAfGEUtv57NUnRM6F.BXyP1fikkQKtNIpFvzSbTVadxPUEtXG', '6a6b29f40d1b4_1000003951.jpg', '2026-07-30 10:39:48', NULL, 0, 0, NULL, NULL),
(11, 'leonce', '$2y$10$0Po2GPvzAQ4Y9uW/TB6XieIxu9qiSfsApv7WMS/KZD.dLqXOXPL9C', '', '2026-08-02 08:02:02', NULL, 0, 0, NULL, NULL),
(12, 'Eric', '$2y$10$Apn1FqcZDymQ09qTduExLuuGoCwTJp.6Uedt6XqVT1W7GMc24X.2q', '', '2026-08-05 07:56:36', NULL, 0, 0, NULL, NULL),
(13, 'kik', '$2y$10$0fR1odTqGxrfnBzb6rbIB.cc9DRa5QJJmRc5ggZ1/BxlLSxjScHNi', '6a7b37d0bd1b8_images (2).jpg', '2026-08-11 14:55:12', NULL, 0, 0, NULL, NULL),
(14, 'ishimwe obed', '$2y$10$MICDQt4iYmAY96TyeJo6V.gcwC8PQASLrpYy9WJ.isWrFIaB.egPK', '', '2026-08-14 13:37:14', NULL, 0, 0, NULL, NULL),
(15, 'donatien_r001', '$2y$10$WL7h3nQVZOQWxm9w1AA1A.tFt4QBxpgRXw3ZwbvdJgwl0yoqcY83i', '', '2026-08-27 11:17:57', NULL, 0, 0, NULL, NULL),
(16, 'www', '$2y$10$dCrljJh5wWm56mc7QbLjsOHahGG.vGyUqnNVf6HOcyZzufXc2wu0K', '6a94192d22575_download (2).jpg', '2026-08-30 11:51:08', NULL, 0, 0, NULL, NULL),
(17, 'levis', '$2y$10$9oi5XsEDuj/f2S9rr0NrwuG3P5mAehuwS2/6J8NNtfYk/UuCcizZS', '6a9abff6f4125_ChatGPT Image Aug 5, 2026, 12_13_47 PM.png', '2026-09-04 12:56:23', NULL, 0, 0, NULL, NULL),
(18, 'mapusi', '$2y$10$VHfD7tvrY95syFIHsjcYceG/DF0Y.y3BT6ts3ibS8SSG/RLUTeMTK', '6a9ac01a21ce9_ChatGPT Image Aug 5, 2026, 12_13_47 PM.png', '2026-09-04 12:56:58', NULL, 0, 0, NULL, NULL),
(19, '0792883875', '$2y$10$N7sx0VZ9/Wdbtm7WhU1d0uo3asfJmHDPxCEmZMIk7kc1yNn2.217C', '6a9fe3af65851_684128632_17885722605517846_4442598194356276795_n.jpg', '2026-09-08 10:30:07', NULL, 0, 0, NULL, NULL),
(20, 'patrick', '$2y$10$Zna/GQZpEbPBSRuMfygw1uAAjoUpEEeRScnTSw1/vNW3MILzf5xNC', '', '2026-09-08 11:19:22', NULL, 0, 0, NULL, NULL),
(21, 'UMUGOME', '$2y$10$5KmV69Hi9O4acOlTmjj4Se7CsUC9BSlhnJiGI4PbIwbyhUqPOMMWi', '', '2026-09-08 16:42:28', NULL, 0, 0, NULL, NULL),
(22, 'keza', '$2y$10$sv3MwaohvbOuk4yRGYQ/gOvZA9tJyb6IykfkELGSvSPXzDMaZu6Uy', '', '2026-09-09 16:11:57', '2026-11-11 20:34:08', 0, 0, NULL, NULL),
(23, 'jmv', '$2y$10$N5F6OMhtT.1rerHoeQy/3uudD92n8ZduVkU5N80EYE7/4K2sUYQe2', '', '2026-09-10 18:00:03', NULL, 0, 0, NULL, NULL),
(24, 'obed', '$2y$12$E8qImO/3lEJ9s861DIIfxO385yortVIsexS6IDfLosHD4W81uYjz6', '', '2026-09-26 17:01:04', NULL, 0, 0, NULL, NULL),
(25, 'musonera_nard11', '$2y$12$l9UbCgOQf.tVCHpEQdHOCu9PpPkSpM5i5Jme40QZCKiB5JE2B3XJ.', '', '2026-09-30 10:52:36', NULL, 0, 0, NULL, NULL),
(26, 'nalada', '$2y$12$n53rDvWbGCe.WvqP/gvLUeEfQPC946mCg6OWvb9a7485oY.mpdrvK', '', '2026-09-30 10:53:12', NULL, 0, 0, NULL, NULL),
(27, 'BBQ', '$2y$12$.bxiVT7IVtmKdkrHsKGcMuTJw6/s5LjVWt/RVQjd0vXqApADgiU0u', '', '2026-10-05 13:50:39', NULL, 0, 0, NULL, NULL),
(28, 'jeremie', '$2y$12$p5KMuqaGP.8E1dDgv8QZ0ulQCb4RBARKL8iwpl7i4UEW11lpZcm5a', '', '2026-11-02 14:47:46', NULL, 0, 0, NULL, NULL),
(31, 'rem', '$2y$12$AlxCusUflgnjIv2/MbBAr.wfVCCMZ7blTX9bKaQxI9Y3YX6WrVK4e', '', '2026-11-11 18:24:12', '2026-11-11 18:27:23', 0, 0, 'he.isremy05@gmail.com', '112190759775391227754'),
(32, 'pro', '$2y$12$wASxg3JAAnHTioU4NdYHveGB4h2gj73F4aC9fA8zPIhgTNxAH8n2.', '', '2026-11-11 18:30:45', '2026-11-11 19:28:41', 0, 0, 'isaieniyikiza91@gmail.com', '117148932213091099522'),
(33, 'gift', '$2y$12$Kkpkgxfe7NO3ZKCobOWs0O5KhBYAVr1zgq0.sg/lY5MjaS5BM6tHK', '', '2026-11-16 18:27:42', '2026-11-16 18:39:42', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vphone_admins`
--

CREATE TABLE `vphone_admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_admins`
--

INSERT INTO `vphone_admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'isaie', '$2y$12$r5qbNZHe7/ARM72KCOzhR.Om9ExV2z5l7mvHPXu4M4lQJlhDNNmeq', '2026-11-07 15:13:24'),
(2, 'kim', '$2y$12$yhhYnbNoXT2TXGpXi5RC8egGDq8t6q4BYXUPuxyVSm/I3YKxj3q9C', '2026-11-09 16:03:33'),
(3, 'sally', '$2y$12$UExRE9pPSG5UIp5TM7dc3O7Sy5/IPSc2xO6Sp2A1bNSUwETSbiGD2', '2026-11-09 17:19:49'),
(4, 'admin', '$2y$12$DxP3ewn4UN1ggJxt9alDcutyYk4lvBIiuWCE9QtsYQ6LwPO1syIva', '2026-11-11 23:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `vphone_apps`
--

CREATE TABLE `vphone_apps` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(80) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `icon_path` varchar(255) NOT NULL,
  `entry_file` varchar(255) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_apps`
--

INSERT INTO `vphone_apps` (`id`, `slug`, `name`, `description`, `icon_path`, `entry_file`, `is_published`, `created_by`, `created_at`) VALUES
(2, 'glass_calculator', 'calculator', 'This is the calculator  for  help you to use calculator  in th viritual phone', '/virtual-phone/user/apps/icons/glass_calculator-c9425d9d668c.jpg', 'index.html', 1, 1, '2026-11-07 15:21:58'),
(3, 'editprostudio', 'edting', 'this  may help yo uto edintin a nya application', '/virtual-phone/user/apps/icons/editprostudio-9e48014108c2.jpg', 'index.html', 1, 3, '2026-11-09 17:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `vphone_app_installs`
--

CREATE TABLE `vphone_app_installs` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `app_id` int(10) UNSIGNED NOT NULL,
  `installed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_app_installs`
--

INSERT INTO `vphone_app_installs` (`user_id`, `app_id`, `installed_at`) VALUES
(2, 2, '2026-11-08 04:08:50'),
(6, 2, '2026-11-09 18:27:01'),
(22, 2, '2026-11-10 18:59:12');

-- --------------------------------------------------------

--
-- Table structure for table `vphone_google_connections`
--

CREATE TABLE `vphone_google_connections` (
  `user_id` int(11) NOT NULL,
  `google_sub` varchar(255) NOT NULL,
  `google_email` varchar(254) NOT NULL,
  `encrypted_tokens` mediumtext NOT NULL,
  `granted_scopes` text NOT NULL,
  `connected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vphone_google_contact_map`
--

CREATE TABLE `vphone_google_contact_map` (
  `user_id` int(11) NOT NULL,
  `resource_name` varchar(255) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `etag` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vphone_icon_activity`
--

CREATE TABLE `vphone_icon_activity` (
  `user_id` int(11) NOT NULL,
  `activity_day` date NOT NULL,
  `progress_seconds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `earned_today` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_ping_at` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_icon_activity`
--

INSERT INTO `vphone_icon_activity` (`user_id`, `activity_day`, `progress_seconds`, `earned_today`, `last_ping_at`) VALUES
(2, '2026-11-21', 15, 0, 1795234299);

-- --------------------------------------------------------

--
-- Table structure for table `vphone_icon_transactions`
--

CREATE TABLE `vphone_icon_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `kind` varchar(30) NOT NULL,
  `reference_key` varchar(140) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_icon_transactions`
--

INSERT INTO `vphone_icon_transactions` (`id`, `user_id`, `amount`, `kind`, `reference_key`, `description`, `created_at`) VALUES
(1, 2, 10, 'login_bonus', 'daily-login:2026-11-20', 'Daily sign-in bonus', '2026-11-20 19:50:33'),
(2, 2, 10, 'login_bonus', 'daily-login:2026-11-21', 'Daily sign-in bonus', '2026-11-21 04:11:19');

-- --------------------------------------------------------

--
-- Table structure for table `vphone_icon_upgrades`
--

CREATE TABLE `vphone_icon_upgrades` (
  `user_id` int(11) NOT NULL,
  `upgrade_key` varchar(50) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vphone_icon_wallets`
--

CREATE TABLE `vphone_icon_wallets` (
  `user_id` int(11) NOT NULL,
  `balance` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_icon_wallets`
--

INSERT INTO `vphone_icon_wallets` (`user_id`, `balance`, `updated_at`) VALUES
(2, 20, '2026-11-21 04:11:19');

-- --------------------------------------------------------

--
-- Table structure for table `vphone_user_activity`
--

CREATE TABLE `vphone_user_activity` (
  `user_id` int(11) NOT NULL,
  `app_name` varchar(120) NOT NULL DEFAULT 'Phone Home',
  `app_path` varchar(255) NOT NULL DEFAULT '',
  `app_icon` varchar(255) NOT NULL DEFAULT '',
  `app_started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_ping` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_user_activity`
--

INSERT INTO `vphone_user_activity` (`user_id`, `app_name`, `app_path`, `app_icon`, `app_started_at`, `last_ping`) VALUES
(22, 'Notes', '/virtual-phone/user/notes/notes.html', '', '2026-11-11 17:34:03', '2026-11-11 17:34:03'),
(31, 'Phone Home', '', '', '2026-11-11 15:24:12', '2026-11-11 15:27:23'),
(33, 'Phone Home', '', '', '2026-11-16 15:39:30', '2026-11-16 15:39:30');

-- --------------------------------------------------------

--
-- Table structure for table `vphone_user_activity_daily`
--

CREATE TABLE `vphone_user_activity_daily` (
  `user_id` int(11) NOT NULL,
  `activity_date` date NOT NULL,
  `app_key` char(64) NOT NULL,
  `app_name` varchar(120) NOT NULL,
  `app_path` varchar(255) NOT NULL DEFAULT '',
  `seconds` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vphone_user_activity_daily`
--

INSERT INTO `vphone_user_activity_daily` (`user_id`, `activity_date`, `app_key`, `app_name`, `app_path`, `seconds`) VALUES
(2, '2026-11-20', '55f043fa65b56ee6873592cf601493d6aed080da5cf0556018502f9f2a311f3d', 'Phone Home', '', 20),
(2, '2026-11-21', '55f043fa65b56ee6873592cf601493d6aed080da5cf0556018502f9f2a311f3d', 'Phone Home', '', 34),
(2, '2026-11-21', 'ce2fbe998e27287e72faece6abd5e2e62f19057e82c3f9019ceb296b68fc348c', 'Icon Rewards', '/virtual-phone/user/icon_rewards/index.php', 21);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_blocks`
--
ALTER TABLE `chat_blocks`
  ADD PRIMARY KEY (`blocker_id`,`blocked_id`);

--
-- Indexes for table `chat_hidden`
--
ALTER TABLE `chat_hidden`
  ADD PRIMARY KEY (`user_id`,`message_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender_receiver` (`sender_id`,`receiver_id`),
  ADD KEY `idx_receiver_read` (`receiver_id`,`is_read`);

--
-- Indexes for table `chat_stars`
--
ALTER TABLE `chat_stars`
  ADD PRIMARY KEY (`user_id`,`message_id`);

--
-- Indexes for table `chat_typing`
--
ALTER TABLE `chat_typing`
  ADD PRIMARY KEY (`user_id`,`receiver_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`event_date`);

--
-- Indexes for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`sender_id`,`receiver_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`group_id`,`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post` (`post_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`post_id`,`user_id`);

--
-- Indexes for table `post_views`
--
ALTER TABLE `post_views`
  ADD PRIMARY KEY (`post_id`,`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `uq_users_google_sub` (`google_sub`);

--
-- Indexes for table `vphone_admins`
--
ALTER TABLE `vphone_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vphone_apps`
--
ALTER TABLE `vphone_apps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_apps_published` (`is_published`);

--
-- Indexes for table `vphone_app_installs`
--
ALTER TABLE `vphone_app_installs`
  ADD PRIMARY KEY (`user_id`,`app_id`),
  ADD KEY `idx_app_installs_app` (`app_id`);

--
-- Indexes for table `vphone_google_connections`
--
ALTER TABLE `vphone_google_connections`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vphone_google_contact_map`
--
ALTER TABLE `vphone_google_contact_map`
  ADD PRIMARY KEY (`user_id`,`resource_name`),
  ADD UNIQUE KEY `uq_vphone_google_contact_local` (`user_id`,`contact_id`),
  ADD KEY `idx_vphone_google_contact_id` (`contact_id`);

--
-- Indexes for table `vphone_icon_activity`
--
ALTER TABLE `vphone_icon_activity`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vphone_icon_transactions`
--
ALTER TABLE `vphone_icon_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_vphone_icon_user_reference` (`user_id`,`reference_key`),
  ADD KEY `idx_vphone_icon_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `vphone_icon_upgrades`
--
ALTER TABLE `vphone_icon_upgrades`
  ADD PRIMARY KEY (`user_id`,`upgrade_key`);

--
-- Indexes for table `vphone_icon_wallets`
--
ALTER TABLE `vphone_icon_wallets`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vphone_user_activity`
--
ALTER TABLE `vphone_user_activity`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_vphone_activity_ping` (`last_ping`),
  ADD KEY `idx_vphone_activity_app` (`app_path`);

--
-- Indexes for table `vphone_user_activity_daily`
--
ALTER TABLE `vphone_user_activity_daily`
  ADD PRIMARY KEY (`user_id`,`activity_date`,`app_key`),
  ADD KEY `idx_vphone_activity_daily_date` (`activity_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `vphone_admins`
--
ALTER TABLE `vphone_admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vphone_apps`
--
ALTER TABLE `vphone_apps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vphone_icon_transactions`
--
ALTER TABLE `vphone_icon_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
