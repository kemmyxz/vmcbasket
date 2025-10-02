-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 08:29 PM
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
(339, 73, 'Elementary - Senior HS Pants', 250.00, 'uploads/1759414581_68de893578e92.png', NULL, 7, 12);

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
(28, 12, 73, 1),
(29, 12, 75, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `user_id`, `message`, `is_admin`, `created_at`, `is_read`) VALUES
(5, 12, 'PASSED CAPSTONE', 0, '2025-05-19 01:24:30', 1),
(6, 12, 'fdfdf', 0, '2025-10-02 16:34:24', 1),
(7, 12, 'hvgh', 1, '2025-10-02 17:27:06', 0),
(8, 12, 'vdvdv', 1, '2025-10-02 17:32:33', 1),
(9, 12, 'gdfgdfg', 0, '2025-10-02 17:42:09', 1),
(10, 12, 'nvnnb', 1, '2025-10-02 18:28:39', 1);

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
  `status` enum('Pending','To Pick Up','Complete','Cancelled','Refunded') NOT NULL DEFAULT 'Pending',
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `payment_method` enum('Cash (Pay at the Counter)','Send Online Receipt') NOT NULL DEFAULT 'Cash (Pay at the Counter)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('2025051405130612', 'Cancelled'),
('2025051406175012', 'Pending'),
('2025051408341612', 'Pending'),
('2025051408351212', 'ToPickUp'),
('2025051417142212', 'Pending'),
('2025051417171312', 'Complete'),
('2025051421085312', 'Complete'),
('2025051500334012', 'Cancelled'),
('2025051500362412', 'Complete'),
('2025051500365312', 'ToPickUp'),
('2025051500483012', 'Complete'),
('2025051500492112', 'Cancelled'),
('2025051500542412', 'Pending'),
('2025051511552612', 'Cancelled'),
('2025051713320312', 'Pending'),
('2025051713321712', 'Cancelled'),
('2025051715093412', 'Cancelled'),
('2025051715425112', 'Cancelled'),
('2025051717352212', 'Pending'),
('2025051717353612', 'Pending'),
('2025051717354912', 'Cancelled'),
('2025051718034612', 'Cancelled'),
('2025051718054212', 'Complete'),
('2025051718091212', 'ToPickUp'),
('2025051718104112', 'Complete'),
('2025051718123612', 'Pending'),
('2025051718135712', 'Complete'),
('2025051718262512', 'Pending'),
('2025051719170412', 'Pending'),
('2025051719171312', 'Complete'),
('2025051719174812', 'Pending'),
('2025051719182412', 'Complete'),
('2025051719202012', 'Complete'),
('2025051719213612', 'Pending'),
('2025051719225112', 'Complete'),
('2025051719234712', 'Complete'),
('2025051719241212', 'Complete'),
('2025051720132112', 'Complete'),
('2025051720332112', 'Complete'),
('2025051721494312', 'Complete'),
('2025051721540512', 'Complete'),
('2025051722103812', 'Complete'),
('2025051722105112', 'Complete'),
('2025051722105712', 'Complete'),
('2025051804520912', 'Pending'),
('2025051804523512', 'Pending'),
('2025051804524012', 'Pending'),
('2025051804524112', 'Pending'),
('2025051804532112', 'Pending'),
('2025051804540812', 'Pending'),
('2025051804553412', 'Complete'),
('2025051804573712', 'Cancelled'),
('2025051805201358', 'Pending'),
('2025051805201758', 'Pending'),
('2025051805202058', 'Pending'),
('2025051805203158', 'Cancelled'),
('2025051805211358', 'Complete'),
('2025051805212958', 'Complete'),
('2025051805215558', 'Complete'),
('2025051805553858', 'Complete'),
('2025051805575558', 'Cancelled'),
('2025051806023358', 'Complete'),
('2025051806024458', 'Complete'),
('2025051806081458', 'Complete'),
('2025051806093858', 'Complete'),
('2025051806094958', 'Complete'),
('2025051806095758', 'Complete'),
('2025051806134658', 'Complete'),
('2025051806144458', 'Complete'),
('2025051806165658', 'Complete'),
('2025051806195758', 'Pending'),
('2025051806272358', 'Complete'),
('2025051806293658', 'Complete'),
('2025051806294558', 'Complete'),
('2025051806300058', 'Complete'),
('2025051807054258', 'Pending'),
('2025051807054858', 'Pending'),
('2025051807060058', 'Complete'),
('2025051807060658', 'Complete'),
('2025051809474758', 'Complete'),
('2025051809475358', 'Pending'),
('2025051809561658', 'Complete'),
('2025051809583258', 'Complete'),
('2025051810335158', 'Pending'),
('2025051810341158', 'Complete'),
('2025051813105458', 'Complete'),
('2025051813113458', 'Complete'),
('2025051813202958', 'Pending'),
('2025051813214958', 'Cancelled'),
('2025051814030058', 'Complete'),
('2025051815480158', 'Pending'),
('2025051815482158', 'Pending'),
('2025051816221158', 'Pending'),
('2025051816223958', 'Pending'),
('2025051816225958', 'Pending'),
('2025051817112558', 'Complete'),
('2025051817114758', 'Cancelled'),
('2025051817120858', 'Complete'),
('2025051817225858', 'Cancelled'),
('2025051817244358', 'Pending'),
('2025051817245458', 'Cancelled'),
('2025051821334958', 'Pending'),
('2025051821362558', 'Pending'),
('2025051903244612', 'Pending'),
('2025051903253412', 'Pending'),
('2025051903260512', 'Complete'),
('2025051903311812', 'Pending'),
('2025051903324558', 'Complete'),
('2025051905314658', 'Cancelled'),
('2025051905320858', 'Complete'),
('2025051906102958', 'Pending'),
('2025051910140058', 'Pending'),
('2025051910321358', 'Complete'),
('2025051910352358', 'Complete'),
('Receipt ID:', 'Pending'),
('VMC-20250518-181549-825', 'Pending'),
('VMC-20250518-181720-081', 'Pending'),
('VMC-20250518-182526-405', 'Pending'),
('VMC-20250518-183628-535', 'Pending'),
('VMC-20250518-183724-786', 'Pending'),
('VMC-20250518-184039-589', 'Pending'),
('VMC-20250518-184345-329', 'Pending'),
('VMC-20250518-185247-076', 'Pending'),
('VMC-20250518-190237-410', 'Complete'),
('VMC-20250518-190530-531', 'Complete'),
('VMC-20250518-191006-074', 'Complete'),
('VMC-20250518-191423-950', 'Complete'),
('VMC-20250518-192014-790', 'Complete'),
('VMC-20250519-164637-900', 'Complete');

-- --------------------------------------------------------

--
-- Table structure for table `order_receipts_images`
--

CREATE TABLE `order_receipts_images` (
  `id` int(11) NOT NULL,
  `receipt_id` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_receipts_images`
--

INSERT INTO `order_receipts_images` (`id`, `receipt_id`, `image_path`, `uploaded_at`) VALUES
(1, '2025051500365312', 'admin/uploads/receipts/receipt_2025051500365312_1747301777.jpg', '2025-05-15 09:36:17'),
(2, '2025051408351212', 'admin/uploads/receipts/receipt_2025051408351212_1747302475.jpg', '2025-05-15 09:47:55'),
(3, '2025051511552612', 'admin/uploads/receipts/receipt_2025051511552612_1747302944.jpg', '2025-05-15 09:55:44'),
(4, '2025051713321712', 'admin/uploads/receipts/receipt_2025051713321712_1747481648.jpg', '2025-05-17 11:34:08'),
(5, '2025051718091212', 'admin/uploads/receipts/receipt_2025051718091212_1747499464.png', '2025-05-17 16:31:04'),
(6, '2025051719182412', 'admin/uploads/receipts/receipt_2025051719182412_1747503425.png', '2025-05-17 17:37:05'),
(7, '2025051817112558', 'admin/uploads/receipts/receipt_2025051817112558_1747581199.jpg', '2025-05-18 15:13:19'),
(8, '2025051817112558', 'admin/uploads/receipts/receipt_2025051817112558_1747581281.png', '2025-05-18 15:14:41'),
(9, '2025051817245458', 'admin/uploads/receipts/receipt_2025051817245458_1747582007.jpeg', '2025-05-18 15:26:47'),
(10, '2025051821334958', 'admin/uploads/receipts/receipt_2025051821334958_1747596849.png', '2025-05-18 19:34:09'),
(11, '2025051903311812', 'admin/uploads/receipts/receipt_2025051903311812_1747618287.png', '2025-05-19 01:31:27'),
(12, '2025051903324558', 'admin/uploads/receipts/receipt_2025051903324558_1747618382.png', '2025-05-19 01:33:02'),
(13, '2025051910140058', 'admin/uploads/receipts/receipt_2025051910140058_1747642677.png', '2025-05-19 08:17:57'),
(14, '2025051910321358', 'admin/uploads/receipts/receipt_2025051910321358_1747643542.png', '2025-05-19 08:32:22');

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
(73, 'Elementary - Senior HS Pants', 'DR123456', 250.00, 'Uniform', '2025-10-02', '', 'uploads/1759414581_68de893578e92.png', NULL, NULL, 'Pre school', 5),
(74, 'Art Paper', 'DR1234670', 10.00, 'Supplies', '2025-10-02', '', 'uploads/1759414631_68de8967dc966.png', NULL, NULL, 'Paper Products', 1000),
(75, 'BSED Polo', 'DR123456', 250.00, 'Uniform', '2025-10-02', '', 'uploads/1759420048_68de9e90415e0.png', NULL, NULL, 'BS Secondary Education', 5);

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
(144, 73, 'XS', 'Unisex', 100, '2025-10-02 14:16:21'),
(145, 73, 'Small', 'Unisex', 100, '2025-10-02 14:16:21'),
(146, 73, 'Medium', 'Unisex', 100, '2025-10-02 14:16:21'),
(147, 74, '', '', 100, '2025-10-02 14:17:11'),
(148, 75, 'XS', 'Male', 50, '2025-10-02 15:47:28'),
(149, 75, 'XS', 'Female', 50, '2025-10-02 15:47:28'),
(150, 75, 'Small', 'Male', 50, '2025-10-02 15:47:28'),
(151, 75, 'Small', 'Female', 50, '2025-10-02 15:47:28');

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
  `active_status` enum('Active','Disable') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_no`, `student_pass`, `created_at`, `updated_at`, `student_fname`, `student_lname`, `student_mname`, `email`, `phone_number`, `year_level`, `birthday`, `photo`, `otp`, `otp_expiry`, `last_activity`, `active_status`) VALUES
(12, '220787', '$2y$10$VTvMMMyLFgb.8RPClIAZg.lp6TYTDpxIuVb4NOHSeCgbhVA8qiXEu', '2025-04-15 03:08:11', '2025-10-02 16:37:33', 'Patrick', 'francisco', 'balderas', 'lopanggokem@gmail.com', '01234567899', 'Bachelor of Science in Criminology', '2003-11-11', '1747080600_Fati\'s Logo.png', NULL, NULL, '2025-10-02 16:37:33', 'Active'),
(65, '123956', '$2y$10$jQiC0RdawTdwcZMwl4xN0eKbZn/Pop/YOfY/Cq4LxOfb5iTkaxpuq', '2025-10-02 08:23:59', '2025-10-02 08:23:59', 'Janella', 'Clare', 'Gomez', 'gomezjanella@gmail.com', '09123456789', 'Elementary Grade 6', '2002-05-15', 'profile_pic.png', NULL, NULL, '2025-10-02 08:23:59', 'Active'),
(66, '123557', '$2y$10$tv8o2jwWmmL2u40TKiv0fusixJgIl/dRVEd1nYsoX9Jd.KHQG1QD6', '2025-10-02 08:23:59', '2025-10-02 08:23:59', 'Fatima', 'Valencia', 'Balderas', 'fatimavbalderas@gmail.com', '09123456780', 'Junior High School Grade 10', '2003-03-22', 'profile_pic.png', NULL, NULL, '2025-10-02 08:23:59', 'Active'),
(67, '123458', '$2y$10$f7m6qO.dMCGJZwu3X8J3HOKj2jkJLoBNIsXHqT28CmKAogLiyQJ2.', '2025-10-02 08:23:59', '2025-10-02 08:23:59', 'Raiza', 'Reign', 'Quimpano', 'raizaquimpano@gmail.com', '09123456781', 'Senior High School Grade 12', '2004-11-10', 'profile_pic.png', NULL, NULL, '2025-10-02 08:23:59', 'Active'),
(68, '123859', '$2y$10$mp.oNr5UWA5jQiE3D81D9OLfcpHHhLftbhei2hdJNvbTy3vi9t/Ti', '2025-10-02 08:23:59', '2025-10-02 16:37:17', 'Kathleen', 'Mae', 'Nicolas', 'nicolaskathleen@gmail.com', '09123456782', 'Bachelor of Science in Information System - 1st Year', '2001-08-25', 'profile_pic.png', NULL, NULL, '2025-10-02 16:37:17', 'Active'),
(69, '123750', '$2y$10$80oKCQAZiBPkWwH9WB4WYOyngnyXJM5b5Ov4MeVoMq/DXAHOV6ggC', '2025-10-02 08:24:00', '2025-10-02 08:24:00', 'Heather', 'Mae', 'Alcober', 'hthralcober@gmail.com', '09123456783', 'Bachelor of Science in Business Administration - 1st Year', '2002-01-30', 'profile_pic.png', NULL, NULL, '2025-10-02 08:24:00', 'Active'),
(70, '123470', '$2y$10$kt.UN7QAinKeQDU5AW6bLO5E.0N.g2b9teTBuJAOpJzFXpk.ImmiG', '2025-10-02 08:24:00', '2025-10-02 08:24:00', 'Sharica', 'White', 'Banania', 'bananiasharica@gmail.com', '09123456784', 'Senior High School Grade 11', '2003-07-19', 'profile_pic.png', NULL, NULL, '2025-10-02 08:24:00', 'Active'),
(73, '111111', '$2y$10$Of4.bjHhqdnr5DfJagPKx.2hMWYqRt3YsKZ/v/7c1pevBMx66Z/Fa', '2025-10-02 08:33:11', '2025-10-02 08:33:11', 'Kathleen', 'Entic', 'Nicolas', 'lopanggokem@gmail.com', '09318734292', 'Bachelor of Science in Information System - 3rd Year', '2003-06-14', 'profile_pic.png', NULL, NULL, '2025-10-02 08:33:11', 'Active');

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
  ADD KEY `receipt_id` (`receipt_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=340;

--
-- AUTO_INCREMENT for table `chat_notifications`
--
ALTER TABLE `chat_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=547;

--
-- AUTO_INCREMENT for table `order_receipts_images`
--
ALTER TABLE `order_receipts_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

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
  ADD CONSTRAINT `order_receipts_images_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `order_receipt` (`receipt_id`);

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
