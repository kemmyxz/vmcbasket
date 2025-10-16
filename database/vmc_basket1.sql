-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2025 at 12:38 AM
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
-- Database: `vmc_basket1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_login`
--

CREATE TABLE `admin_login` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_pass` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login`
--

INSERT INTO `admin_login` (`admin_id`, `admin_name`, `admin_pass`) VALUES
(1, 'VMCADMIN', '$2y$10$SRX3TAutGKLb9MLtsvXuhuAttC7hppulhs57Vyf623Wld.SRBao2.');

-- --------------------------------------------------------

--
-- Table structure for table `basket`
--

CREATE TABLE `basket` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `basket`
--

INSERT INTO `basket` (`id`, `product_id`, `product_name`, `price`, `image`, `size`, `quantity`, `user_id`) VALUES
(346, 79, 'BSBA Blouse ', 250.00, 'uploads/1759648333_68e21a4d67cc6.png', NULL, 1, 12);

-- --------------------------------------------------------

--
-- Table structure for table `chat_notifications`
--

CREATE TABLE `chat_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `favorite` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `favorite`) VALUES
(32, 12, 79, 1),
(34, 12, 83, 1),
(35, 12, 84, 0),
(36, 12, 80, 1),
(37, 12, 81, 1),
(38, 80, 87, 1),
(39, 80, 84, 1),
(40, 80, 85, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `sender` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `user_id`, `message`, `created_at`, `is_read`, `sender`) VALUES
(35, 12, 'sadasd', '2025-10-04 14:30:22', 1, 'user'),
(36, 12, 'dsads', '2025-10-04 14:30:30', 1, 'admin'),
(37, 12, 'dsad', '2025-10-07 06:48:12', 1, 'admin'),
(38, 12, 'pogi ako', '2025-10-07 07:06:25', 1, 'user'),
(39, 12, 'ganda ni kath', '2025-10-07 07:56:04', 1, 'user'),
(40, 12, 'heelo', '2025-10-07 07:56:15', 1, 'admin'),
(41, 80, 'hello', '2025-10-07 14:04:36', 1, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `school_id` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `order_date` date NOT NULL DEFAULT curdate(),
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `payment_method` enum('Cash (Pay at the Counter)','Send Online Receipt') NOT NULL DEFAULT 'Cash (Pay at the Counter)',
  `status` enum('Pending','ToPickUp','Complete','Cancelled','Refunded') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `product_name`, `quantity`, `customer_name`, `school_id`, `email`, `phone`, `price`, `order_date`, `user_id`, `product_id`, `size`, `total_price`, `image`, `receipt_no`, `payment_method`, `status`) VALUES
(630, '\n                                                VMC Lace                                            ', 1, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 83, 'N/A', 250.00, 'uploads/1759817937_68e4b0d1aeb23.png', 'VMC-20251009-150947-805', 'Cash (Pay at the Counter)', 'ToPickUp'),
(631, '\n                                                VMC Lace                                            ', 1, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 83, 'N/A', 250.00, 'uploads/1759817937_68e4b0d1aeb23.png', 'VMC-20251009-151157-904', 'Cash (Pay at the Counter)', 'ToPickUp'),
(632, '\n                                                Elementary - Senior HS Pants                                            ', 2, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 80, 'XS', 500.00, 'uploads/1759733587_68e36753e126e.png', 'VMC-20251009-153524-012', 'Cash (Pay at the Counter)', 'ToPickUp'),
(633, '\n                                                VMC Lace                                            ', 2, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 83, 'N/A', 500.00, 'uploads/1759817937_68e4b0d1aeb23.png', 'VMC-20251009-154310-308', 'Cash (Pay at the Counter)', 'ToPickUp'),
(634, '\n                                                Crayola-8                                            ', 2, 'Kem', '', NULL, NULL, 40.00, '2025-10-09', NULL, 84, 'N/A', 80.00, 'uploads/1759819474_68e4b6d2367c0.png', 'VMC-20251009-155108-186', 'Cash (Pay at the Counter)', 'ToPickUp'),
(635, '\n                                                Elementary - Senior HS Pants                                            ', 2, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 80, 'XL', 500.00, 'uploads/1759733587_68e36753e126e.png', 'VMC-20251009-155305-111', 'Cash (Pay at the Counter)', 'ToPickUp'),
(636, '\n                                                BSBA Blouse                                             ', 2, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 79, 'XS', 500.00, 'uploads/1759648333_68e21a4d67cc6.png', 'VMC-20251009-162725-844', 'Cash (Pay at the Counter)', 'ToPickUp'),
(637, 'VMC Lace', 1, 'Kathleen Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 250.00, '2025-10-09', 80, 83, 'N/A', 250.00, 'uploads/1759817937_68e4b0d1aeb23.png', '2025100911133480', 'Cash (Pay at the Counter)', 'Complete'),
(638, '\n                                                BSBA Blouse                                             ', 2, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 79, '2XL', 500.00, 'uploads/1759648333_68e21a4d67cc6.png', 'VMC-20251009-172402-307', 'Cash (Pay at the Counter)', 'ToPickUp'),
(639, '\n                                                VMC Lace                                            ', 2, 'Kem', '', NULL, NULL, 250.00, '2025-10-09', NULL, 83, 'N/A', 500.00, 'uploads/1759817937_68e4b0d1aeb23.png', 'VMC-20251009-172746-136', 'Cash (Pay at the Counter)', 'ToPickUp'),
(640, 'Crayola-8', 1, 'Kathleen Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 40.00, '2025-10-09', 80, 84, 'N/A', 40.00, 'uploads/1759819474_68e4b6d2367c0.png', '2025100911332980', 'Cash (Pay at the Counter)', 'Pending'),
(641, '1/4 paper', 1, 'Kathleen Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 15.00, '2025-10-09', 80, 85, 'N/A', 15.00, 'uploads/1759819514_68e4b6faee163.png', '2025100911331580', 'Cash (Pay at the Counter)', 'ToPickUp'),
(642, 'Crayola-8', 1, 'Kathleen Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 40.00, '2025-10-09', 80, 84, 'N/A', 40.00, 'uploads/1759819474_68e4b6d2367c0.png', '2025100911371880', 'Send Online Receipt', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_receipt`
--

CREATE TABLE `order_receipt` (
  `receipt_id` varchar(255) NOT NULL,
  `order_status` enum('Pending','ToPickUp','Complete','Cancelled','Refunded') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_receipt`
--

INSERT INTO `order_receipt` (`receipt_id`, `order_status`) VALUES
('2025100911133480', 'Complete'),
('2025100911331580', 'ToPickUp'),
('2025100911332980', 'Pending'),
('2025100911333180', 'Pending'),
('2025100911371880', 'Pending'),
('VMC-20251009-150947-805', 'ToPickUp'),
('VMC-20251009-151157-904', 'ToPickUp'),
('VMC-20251009-153524-012', 'ToPickUp'),
('VMC-20251009-154310-308', 'ToPickUp'),
('VMC-20251009-155108-186', 'ToPickUp'),
('VMC-20251009-155305-111', 'ToPickUp'),
('VMC-20251009-162725-844', 'Complete'),
('VMC-20251009-172402-307', 'Complete'),
('VMC-20251009-172746-136', 'Complete');

-- --------------------------------------------------------

--
-- Table structure for table `order_receipts_images`
--

CREATE TABLE `order_receipts_images` (
  `id` int(11) NOT NULL,
  `receipt_id` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_receipts_images`
--

INSERT INTO `order_receipts_images` (`id`, `receipt_id`, `image_path`, `uploaded_at`, `order_id`) VALUES
(49, NULL, 'admin/uploads/receipts/receipt_80_1760002638.png', '2025-10-09 09:37:18', 642);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `dr_number` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` enum('Uniform','Supplies') NOT NULL,
  `date_modified` date NOT NULL,
  `admin_handled` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `max_quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `dr_number`, `price`, `type`, `date_modified`, `admin_handled`, `image`, `qr_code`, `rating`, `tags`, `max_quantity`) VALUES
(79, 'BSBA Blouse ', 'DR123456', 250.00, 'Uniform', '2025-10-05', '', 'uploads/1759648333_68e21a4d67cc6.png', NULL, NULL, 'BS Hotel and Restaurant Management', 3),
(80, 'Elementary - Senior HS Pants', 'DR123456', 250.00, 'Uniform', '2025-10-06', '', 'uploads/1759733587_68e36753e126e.png', NULL, NULL, 'Elementary', 3),
(81, 'Basic Education PE Shirt', 'DR123456', 250.00, 'Uniform', '2025-10-06', '', 'uploads/1759733665_68e367a115ea8.png', NULL, NULL, 'Elementary', 3),
(82, 'BSTM Blouse', 'DR123456', 250.00, 'Uniform', '2025-10-06', '', 'uploads/1759737087_68e374ff01ab4.png', NULL, NULL, 'Kindergarten, Elementary, Junior High School, BS Tourism Management', 3),
(83, 'VMC Lace', 'DR123456', 250.00, 'Supplies', '2025-10-07', '', 'uploads/1759817937_68e4b0d1aeb23.png', NULL, 5, 'School Merchandise', 2),
(84, 'Crayola-8', 'DR123456', 40.00, 'Supplies', '2025-10-07', '', 'uploads/1759819474_68e4b6d2367c0.png', NULL, NULL, 'Art Supplies', 100),
(85, '1/4 paper', 'DR123456', 15.00, 'Supplies', '2025-10-07', '', 'uploads/1759819514_68e4b6faee163.png', NULL, NULL, 'Paper Products', 10),
(86, 'Ballpen', 'DR123456', 15.00, 'Supplies', '2025-10-07', '', 'uploads/1759826815_68e4d37fb2530.png', NULL, NULL, 'Writing Tools', 5),
(87, 'Clay', 'DR123456', 45.00, 'Supplies', '2025-10-07', '', 'uploads/1759826842_68e4d39a9ecbe.png', NULL, NULL, 'Art Supplies', 5),
(88, 'Cartolina', 'DR123456', 45.00, 'Supplies', '2025-10-07', '', 'uploads/1759826861_68e4d3ad45329.png', NULL, NULL, 'Paper Products', 5);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `order_id`, `product_id`, `user_id`, `rating`, `review_text`, `is_anonymous`, `created_at`) VALUES
(31, 559, 83, 80, 5, '', 1, '2025-10-07 13:22:15');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `gender` enum('Male','Female','Unisex') DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `gender`, `stock`, `last_updated`) VALUES
(156, 79, 'XS', 'Unisex', 90, '2025-10-09 08:59:08'),
(157, 79, 'Medium', 'Unisex', 97, '2025-10-08 01:21:51'),
(158, 79, '2XL', 'Unisex', 98, '2025-10-09 09:27:17'),
(159, 80, 'XS', 'Male', 19, '2025-10-07 14:26:36'),
(160, 80, 'XS', 'Female', 20, '2025-10-06 06:53:07'),
(161, 80, 'XL', 'Male', 10, '2025-10-06 06:53:07'),
(162, 80, 'XL', 'Female', 30, '2025-10-06 06:53:07'),
(163, 81, 'XS', 'Female', 4, '2025-10-06 06:54:25'),
(164, 81, 'Small', 'Female', 5, '2025-10-06 06:54:25'),
(165, 82, 'XS', 'Male', 20, '2025-10-06 07:51:27'),
(166, 82, 'XS', 'Female', 22, '2025-10-06 07:51:27'),
(167, 82, 'Small', 'Male', 23, '2025-10-06 07:51:27'),
(168, 82, 'Small', 'Female', 233, '2025-10-06 07:51:27'),
(169, 83, '', '', 94, '2025-10-09 09:28:36'),
(170, 84, '', '', 60, '2025-10-07 14:26:48'),
(171, 85, '', '', 98, '2025-10-08 01:48:06'),
(172, 86, '', '', 100, '2025-10-07 08:46:55'),
(173, 87, '', '', 99, '2025-10-07 14:23:11'),
(174, 88, '', '', 100, '2025-10-07 08:47:41');

-- --------------------------------------------------------

--
-- Table structure for table `restock_history`
--

CREATE TABLE `restock_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `dr_number` varchar(50) NOT NULL,
  `added_stock` int(11) NOT NULL,
  `updated_by` varchar(100) NOT NULL,
  `restock_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `variant_details` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_images`
--

CREATE TABLE `review_images` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `quantity_sold` int(11) NOT NULL DEFAULT 0,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `student_pass` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `student_fname` varchar(100) NOT NULL,
  `student_lname` varchar(100) NOT NULL,
  `student_mname` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL,
  `phone_number` varchar(11) DEFAULT NULL,
  `year_level` varchar(150) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `otp` varchar(100) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `active_status` enum('Active','Disabled') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_no`, `student_pass`, `created_at`, `updated_at`, `student_fname`, `student_lname`, `student_mname`, `email`, `phone_number`, `year_level`, `birthday`, `photo`, `otp`, `otp_expiry`, `last_activity`, `active_status`) VALUES
(12, '220787', '$2y$10$VTvMMMyLFgb.8RPClIAZg.lp6TYTDpxIuVb4NOHSeCgbhVA8qiXEu', '2025-04-15 03:08:11', '2025-10-05 06:32:12', 'Patrick', 'francisco', 'balderas', 'lopanggokem@gmail.com', '01234567899', 'Bachelor of Science in Criminology', '2003-11-11', '1759645932_SDO-VAL-Logo.png', NULL, NULL, '2025-10-05 06:31:59', 'Active'),
(73, '111111', '$2y$10$Of4.bjHhqdnr5DfJagPKx.2hMWYqRt3YsKZ/v/7c1pevBMx66Z/Fa', '2025-10-02 08:33:11', '2025-10-02 08:33:11', 'Kathleen', 'Entic', 'Nicolas', 'lopanggokem@gmail.com', '09318734292', 'Bachelor of Science in Information System - 3rd Year', '2003-06-14', 'profile_pic.png', NULL, NULL, '2025-10-02 08:33:11', 'Active'),
(77, '123956', '$2y$10$RFO1pTg9VnhBeCnaaJWz/urSxqe73MzX0.KSN2EliemQE7WYZ6qGu', '2025-10-06 07:37:55', '2025-10-06 07:37:55', 'Janella', 'Clare', 'Gomez', 'gomezjanella@gmail.com', '09123456789', 'Elementary Grade 6', '2002-05-15', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Active'),
(78, '123557', '$2y$10$6h/w0aHB7TNNU/WIJaOYPe4Y/lfPGK9XdtlE14Q7CpZmKefDvco3K', '2025-10-06 07:37:55', '2025-10-06 07:37:55', 'Fatima', 'Valencia', 'Balderas', 'fatimavbalderas@gmail.com', '09123456780', 'Junior High School Grade 10', '2003-03-22', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Active'),
(79, '123458', '$2y$10$0PmRzfUKasHHKqx5TasQUOSW/bA63hXo1SQWRbt8Skm9Qt40vYX7m', '2025-10-06 07:37:55', '2025-10-06 07:38:02', 'Raiza', 'Reign', 'Quimpano', 'raizaquimpano@gmail.com', '09123456781', 'Senior High School Grade 12', '2004-11-10', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Disabled'),
(80, '123859', '$2y$10$SjiCowApsULj7p/fJyEO3Oih1f0Jt59GzOQ/s4hEaujff3Y9T9zSW', '2025-10-06 07:37:55', '2025-10-07 13:56:11', 'Kathleen', 'Mae', 'Nicolas', 'nicolaskathleen@gmail.com', '09123456782', 'Bachelor of Science in Hotel and Restaurant Management - 1st Year', '2001-08-25', '1759845371_photo_6242454177410303577_y.jpg', NULL, NULL, '2025-10-07 11:44:52', 'Active'),
(81, '123750', '$2y$10$eYvphdhjAG9at6o4KNxLYe5EO3ZXwe1qgv.R/ADRMH691NTVbVBly', '2025-10-06 07:37:55', '2025-10-06 07:37:55', 'Heather', 'Mae', 'Alcober', 'hthralcober@gmail.com', '09123456783', 'Bachelor of Science in Business Administration - 1st Year', '2002-01-30', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Active'),
(82, '123470', '$2y$10$w8z0Hsti8XcFittCs.2GIehXtlK9FJ2xA6X.zlHwy0qa2ifPTK/ja', '2025-10-06 07:37:55', '2025-10-06 07:37:55', 'Sharica', 'White', 'Banania', 'bananiasharica@gmail.com', '09123456784', 'Senior High School Grade 11', '2003-07-19', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Active'),
(83, '333333', '$2y$10$CNHkVPnKjVxs4YL5TCi.ven2s2k4w79T6fcwR0mJd7CZlCrp3ol5S', '2025-10-06 07:45:55', '2025-10-06 07:45:55', 'KEMBERLY', 'ENTIC', 'LOPANGGO', 'lopanggokem@gmail.com', '09318734292', 'Bachelor of Science in Criminology - 1st Year', '2003-06-14', 'profile_pic.png', NULL, NULL, '2025-10-06 07:45:55', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login`
--
ALTER TABLE `admin_login`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `basket`
--
ALTER TABLE `basket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_notifications`
--
ALTER TABLE `chat_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_favorites_product` (`product_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- Indexes for table `order_receipt`
--
ALTER TABLE `order_receipt`
  ADD UNIQUE KEY `receipt_id` (`receipt_id`);

--
-- Indexes for table `order_receipts_images`
--
ALTER TABLE `order_receipts_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_reviews_ibfk_2` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_variant` (`product_id`,`size`,`gender`);

--
-- Indexes for table `restock_history`
--
ALTER TABLE `restock_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `review_images`
--
ALTER TABLE `review_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_review_images_review` (`review_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login`
--
ALTER TABLE `admin_login`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `basket`
--
ALTER TABLE `basket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=369;

--
-- AUTO_INCREMENT for table `chat_notifications`
--
ALTER TABLE `chat_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=643;

--
-- AUTO_INCREMENT for table `order_receipts_images`
--
ALTER TABLE `order_receipts_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `restock_history`
--
ALTER TABLE `restock_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `review_images`
--
ALTER TABLE `review_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_notifications`
--
ALTER TABLE `chat_notifications`
  ADD CONSTRAINT `chat_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_receipts_images`
--
ALTER TABLE `order_receipts_images`
  ADD CONSTRAINT `order_receipts_images_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `order_receipt` (`receipt_id`),
  ADD CONSTRAINT `order_receipts_images_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restock_history`
--
ALTER TABLE `restock_history`
  ADD CONSTRAINT `restock_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_images`
--
ALTER TABLE `review_images`
  ADD CONSTRAINT `fk_review_images_review` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_images_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
