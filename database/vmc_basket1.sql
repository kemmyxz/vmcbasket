-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 02:24 AM
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
(41, 80, 'hello', '2025-10-07 14:04:36', 1, 'user'),
(42, 80, 'dsda', '2025-10-13 08:43:34', 1, 'user'),
(43, 80, 'kempogoxd', '2025-10-17 03:56:34', 1, 'user');

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
  `status` enum('Pending','ToPickUp','Complete','Cancelled','Refunded') NOT NULL DEFAULT 'Pending',
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `product_name`, `quantity`, `customer_name`, `school_id`, `email`, `phone`, `price`, `order_date`, `user_id`, `product_id`, `size`, `total_price`, `image`, `receipt_no`, `payment_method`, `status`, `cancellation_reason`, `cancelled_at`) VALUES
(725, 'BSBA Skirt ', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 3.00, '2025-10-13', 80, 89, 'Medium', 3.00, 'uploads/1760345511_68ecbda70ca89.png', 'ORD-1760363178-80', 'Send Online Receipt', 'Cancelled', NULL, NULL),
(726, 'VMC LACE', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 150.00, '2025-10-16', 80, 91, '0', 150.00, 'uploads/1760355213_68ece38dbd837.png', 'ORD-1760622596-80', 'Cash (Pay at the Counter)', 'ToPickUp', NULL, NULL),
(727, 'BSBA Skirt ', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 3.00, '2025-10-16', 80, 89, 'Small', 3.00, 'uploads/1760345511_68ecbda70ca89.png', 'ORD-1760622633-80', 'Send Online Receipt', 'ToPickUp', NULL, NULL),
(728, 'Art Paper', 1, 'Patrick balderas francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 3.00, '2025-10-16', 12, 93, '0', 3.00, 'uploads/1760624905_68f1010956f72.png', 'ORD-1760625240-12', 'Cash (Pay at the Counter)', 'ToPickUp', NULL, NULL),
(729, 'Ballpen', 3, 'Patrick balderas francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 3.00, '2025-10-16', 12, 92, '0', 9.00, 'uploads/1760624686_68f1002eced4b.png', 'ORD-1760625258-12', 'Cash (Pay at the Counter)', 'ToPickUp', NULL, NULL),
(730, 'VMC LACE', 1, 'Patrick balderas francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 150.00, '2025-10-16', 12, 91, '0', 150.00, 'uploads/1760355213_68ece38dbd837.png', 'ORD-1760627333-12', 'Cash (Pay at the Counter)', 'Pending', NULL, NULL),
(731, 'Art Paper', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 3.00, '2025-10-16', 80, 93, '0', 3.00, 'uploads/1760624905_68f1010956f72.png', 'ORD-1760628634-80', 'Cash (Pay at the Counter)', 'Pending', NULL, NULL),
(732, 'Ballpen', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 3.00, '2025-10-16', 80, 92, '0', 3.00, 'uploads/1760624686_68f1002eced4b.png', 'ORD-1760628660-80', 'Cash (Pay at the Counter)', 'Pending', NULL, NULL),
(733, 'VMC LACE', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 150.00, '2025-10-16', 80, 91, '0', 150.00, 'uploads/1760355213_68ece38dbd837.png', 'ORD-1760628678-80', 'Cash (Pay at the Counter)', 'ToPickUp', NULL, NULL),
(734, 'Art Paper', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 3.00, '2025-10-16', 80, 93, '0', 3.00, 'uploads/1760624905_68f1010956f72.png', 'ORD-1760628696-80', 'Cash (Pay at the Counter)', 'ToPickUp', NULL, NULL),
(735, 'VMC LACE', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 150.00, '2025-10-16', 80, 91, '0', 150.00, 'uploads/1760355213_68ece38dbd837.png', 'ORD-1760628707-80', 'Cash (Pay at the Counter)', 'ToPickUp', NULL, NULL),
(736, 'Art Paper', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 3.00, '2025-10-17', 80, 93, NULL, 3.00, 'uploads/1760624905_68f1010956f72.png', 'ORD-1760672966-80', 'Send Online Receipt', 'Cancelled', NULL, NULL),
(737, '\n                                                VMC LACE                                            ', 2, 'Kem', '', NULL, NULL, 150.00, '2025-10-17', NULL, 91, 'N/A', 300.00, 'uploads/1760355213_68ece38dbd837.png', 'VMC-20251017-120431-230', 'Cash (Pay at the Counter)', 'ToPickUp', NULL, NULL),
(738, 'Manila Paper', 5, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 20.00, '2025-10-17', 80, 94, '0', 100.00, 'uploads/1760674304_68f1c200efd6a.png', 'ORD-1760674363-80', 'Cash (Pay at the Counter)', 'Pending', NULL, NULL),
(739, 'BSBA Skirt ', 1, 'Kathleen Nicolas Mae', '123859', 'nicolaskathleen@gmail.com', '09123456782', 3.00, '2025-10-17', 80, 89, 'Small', 3.00, 'uploads/1760345511_68ecbda70ca89.png', 'ORD-1760675255-80', 'Cash (Pay at the Counter)', 'Cancelled', 'Wrong Item', '2025-10-20 04:03:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_cancellations`
--

CREATE TABLE `order_cancellations` (
  `id` int(11) NOT NULL,
  `receipt_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `cancelled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_cancellations`
--

INSERT INTO `order_cancellations` (`id`, `receipt_no`, `user_id`, `reason`, `cancelled_at`) VALUES
(1, 'ORD-1760675255-80', 80, 'Wrong Item', '2025-10-20 04:03:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_receipt`
--

CREATE TABLE `order_receipt` (
  `receipt_id` varchar(255) NOT NULL,
  `order_status` enum('Pending','ToPickUp','Complete','Cancelled','Refund Requested','Refunded') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_receipt`
--

INSERT INTO `order_receipt` (`receipt_id`, `order_status`) VALUES
('ORD-1760363178-80', 'Cancelled'),
('ORD-1760622596-80', 'ToPickUp'),
('ORD-1760622633-80', 'ToPickUp'),
('ORD-1760625240-12', 'Complete'),
('ORD-1760625258-12', 'Complete'),
('ORD-1760627333-12', 'Complete'),
('ORD-1760628634-80', 'Pending'),
('ORD-1760628660-80', 'Refund Requested'),
('ORD-1760628678-80', 'Refunded'),
('ORD-1760628696-80', 'ToPickUp'),
('ORD-1760628707-80', 'ToPickUp'),
('ORD-1760672966-80', 'Cancelled'),
('ORD-1760674363-80', 'Complete'),
('ORD-1760675255-80', 'Refunded'),
('VMC-20251017-120431-230', 'Complete');

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
(59, 'ORD-1760363178-80', 'admin/uploads/receipts/ORD-1760363178-80.jpeg', '2025-10-13 13:46:18', NULL),
(60, 'ORD-1760622633-80', 'admin/uploads/receipts/ORD-1760622633-80.png', '2025-10-16 13:50:33', NULL),
(61, 'ORD-1760672966-80', 'admin/uploads/receipts/ORD-1760672966-80.jpg', '2025-10-17 03:49:26', NULL);

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
(89, 'BSBA Skirt ', 'DR123456', 3.00, 'Uniform', '2025-10-13', '', 'uploads/1760345511_68ecbda70ca89.png', NULL, NULL, 'Pre school', 3),
(91, 'VMC LACE', 'DR123456', 150.00, 'Supplies', '2025-10-13', '', 'uploads/1760355213_68ece38dbd837.png', NULL, 4, 'School Merchandise', 2),
(92, 'Ballpen', 'DR123456', 3.00, 'Supplies', '2025-10-16', '', 'uploads/1760624686_68f1002eced4b.png', NULL, 4, 'Writing Tools', 3),
(93, 'Art Paper', 'DR1234670', 3.00, 'Supplies', '2025-10-17', '', 'uploads/1760624905_68f1010956f72.png', NULL, 5, 'Paper Products', 5),
(94, 'Manila Paper', 'DR1234677', 20.00, 'Supplies', '2025-10-17', '', 'uploads/1760674304_68f1c200efd6a.png', NULL, 4, 'Paper Products', 5);

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
(32, 729, 92, 12, 4, '', 1, '2025-10-16 15:04:33'),
(33, 728, 93, 12, 5, '', 1, '2025-10-16 15:04:38'),
(34, 730, 91, 12, 4, '', 1, '2025-10-16 15:09:24'),
(35, 738, 94, 80, 4, 'kempogixd', 1, '2025-10-17 04:24:21');

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
(175, 89, 'XS', 'Female', 50, '2025-10-13 08:51:51'),
(176, 89, 'Small', 'Female', 51, '2025-10-20 04:03:09'),
(177, 89, 'Medium', 'Female', 50, '2025-10-13 08:51:51'),
(179, 91, '', '', 10, '2025-10-16 14:23:58'),
(180, 92, '', '', 5, '2025-10-16 14:24:46'),
(181, 93, '', '', 20, '2025-10-17 04:07:41'),
(182, 94, '', '', 1, '2025-10-17 04:12:18');

-- --------------------------------------------------------

--
-- Table structure for table `refund_images`
--

CREATE TABLE `refund_images` (
  `id` int(11) NOT NULL,
  `refund_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` int(11) NOT NULL,
  `receipt_no` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refund_requests`
--

INSERT INTO `refund_requests` (`id`, `receipt_no`, `user_id`, `reason`, `description`, `status`, `created_at`, `updated_at`) VALUES
(6, 'ORD-1760675255-80', 80, 'Defective or Damage Product', 'dasd', 'Approved', '2025-10-20 07:40:49', '2025-10-20 07:46:03');

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

--
-- Dumping data for table `restock_history`
--

INSERT INTO `restock_history` (`id`, `product_id`, `dr_number`, `added_stock`, `updated_by`, `restock_date`, `variant_details`) VALUES
(4, 93, 'DR1234670', 15, 'kem', '2025-10-17 04:07:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `review_images`
--

CREATE TABLE `review_images` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_images`
--

INSERT INTO `review_images` (`id`, `review_id`, `image_path`) VALUES
(13, 34, 'admin/uploads/reviews/68f10aa43933c_review_image_0.jpg');

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
(12, '220787', '$2y$10$VTvMMMyLFgb.8RPClIAZg.lp6TYTDpxIuVb4NOHSeCgbhVA8qiXEu', '2025-04-15 03:08:11', '2025-10-17 03:51:58', 'Patrick', 'francisco', 'balderas', 'lopanggokem@gmail.com', '01234567899', 'Bachelor of Science in Criminology', '2003-11-11', '1760626575_meme.jpg', NULL, NULL, '2025-10-17 03:51:58', 'Active'),
(73, '111111', '$2y$10$Of4.bjHhqdnr5DfJagPKx.2hMWYqRt3YsKZ/v/7c1pevBMx66Z/Fa', '2025-10-02 08:33:11', '2025-10-02 08:33:11', 'Kathleen', 'Entic', 'Nicolas', 'lopanggokem@gmail.com', '09318734292', 'Bachelor of Science in Information System - 3rd Year', '2003-06-14', 'profile_pic.png', NULL, NULL, '2025-10-02 08:33:11', 'Active'),
(77, '123956', '$2y$10$RFO1pTg9VnhBeCnaaJWz/urSxqe73MzX0.KSN2EliemQE7WYZ6qGu', '2025-10-06 07:37:55', '2025-10-06 07:37:55', 'Janella', 'Clare', 'Gomez', 'gomezjanella@gmail.com', '09123456789', 'Elementary Grade 6', '2002-05-15', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Active'),
(78, '123557', '$2y$10$6h/w0aHB7TNNU/WIJaOYPe4Y/lfPGK9XdtlE14Q7CpZmKefDvco3K', '2025-10-06 07:37:55', '2025-10-06 07:37:55', 'Fatima', 'Valencia', 'Balderas', 'fatimavbalderas@gmail.com', '09123456780', 'Junior High School Grade 10', '2003-03-22', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Active'),
(79, '123458', '$2y$10$0PmRzfUKasHHKqx5TasQUOSW/bA63hXo1SQWRbt8Skm9Qt40vYX7m', '2025-10-06 07:37:55', '2025-10-06 07:38:02', 'Raiza', 'Reign', 'Quimpano', 'raizaquimpano@gmail.com', '09123456781', 'Senior High School Grade 12', '2004-11-10', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Disabled'),
(80, '123859', '$2y$10$SjiCowApsULj7p/fJyEO3Oih1f0Jt59GzOQ/s4hEaujff3Y9T9zSW', '2025-10-06 07:37:55', '2025-10-17 03:54:23', 'Kathleen', 'Mae', 'Nicolas', 'nicolaskathleen@gmail.com', '09123456782', 'Bachelor of Science in Hotel and Restaurant Management - 1st Year', '2001-08-25', '1760622477_photo_6242454177410303577_y.jpg', NULL, NULL, '2025-10-17 03:54:23', 'Active'),
(81, '123750', '$2y$10$eYvphdhjAG9at6o4KNxLYe5EO3ZXwe1qgv.R/ADRMH691NTVbVBly', '2025-10-06 07:37:55', '2025-10-17 04:08:23', 'Heather', 'Mae', 'Alcober', 'hthralcober@gmail.com', '09123456783', 'Bachelor of Science in Business Administration - 1st Year', '2002-01-30', 'profile_pic.png', NULL, NULL, '2025-10-06 07:37:55', 'Disabled'),
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
  ADD UNIQUE KEY `receipt_no` (`receipt_no`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- Indexes for table `order_cancellations`
--
ALTER TABLE `order_cancellations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `receipt_no` (`receipt_no`);

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
-- Indexes for table `refund_images`
--
ALTER TABLE `refund_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refund_id` (`refund_id`);

--
-- Indexes for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `receipt_no` (`receipt_no`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=408;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=740;

--
-- AUTO_INCREMENT for table `order_cancellations`
--
ALTER TABLE `order_cancellations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_receipts_images`
--
ALTER TABLE `order_receipts_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `refund_images`
--
ALTER TABLE `refund_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `refund_requests`
--
ALTER TABLE `refund_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `restock_history`
--
ALTER TABLE `restock_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `review_images`
--
ALTER TABLE `review_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- Constraints for table `order_cancellations`
--
ALTER TABLE `order_cancellations`
  ADD CONSTRAINT `order_cancellations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_cancellations_ibfk_2` FOREIGN KEY (`receipt_no`) REFERENCES `orders` (`receipt_no`) ON DELETE CASCADE;

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
-- Constraints for table `refund_images`
--
ALTER TABLE `refund_images`
  ADD CONSTRAINT `refund_images_ibfk_1` FOREIGN KEY (`refund_id`) REFERENCES `refund_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD CONSTRAINT `refund_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `refund_requests_ibfk_2` FOREIGN KEY (`receipt_no`) REFERENCES `order_receipt` (`receipt_id`);

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
