-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 09, 2025 at 03:29 PM
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
-- Database: `vmc_basket`
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
(18, 12, 29, 1),
(20, 58, 28, 1),
(22, 58, 29, 1),
(23, 58, 31, 1),
(24, 58, 35, 1),
(25, 58, 33, 1),
(26, 58, 63, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `user_id`, `message`, `created_at`) VALUES
(2, 58, 'test1234', '2025-05-19 01:16:43'),
(3, 58, 'test12345', '2025-05-19 01:22:11'),
(4, 58, 'SANA MAKAPASA KAMI SA DEFENSE', '2025-05-19 01:22:27'),
(5, 12, 'PASSED CAPSTONE', '2025-05-19 01:24:30');

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `product_name`, `quantity`, `customer_name`, `school_id`, `email`, `phone`, `price`, `order_date`, `status`, `user_id`, `product_id`, `size`, `total_price`, `image`, `receipt_no`, `payment_method`) VALUES
(459, 'Art Paper', 1, 'Patrick francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 10.00, '2025-05-18', 'Complete', 12, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051804553412', 'Cash (Pay at the Counter)'),
(460, 'Elementary Polo', 2, 'Patrick francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 150.00, '2025-05-18', 'Complete', 12, 35, 'Small', 300.00, 'uploads/1747511258_Elementary Polo (Front).png', '2025051804553412', 'Cash (Pay at the Counter)'),
(461, 'BSBA Polo', 2, 'Patrick francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 300.00, '2025-05-18', 'Cancelled', 12, 33, 'Small', 600.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051804573712', 'Cash (Pay at the Counter)'),
(462, 'BSBA Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Pending', 58, 33, 'Small', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051810335158', 'Cash (Pay at the Counter)'),
(463, 'BSBA Polo ', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Cancelled', 58, 33, 'Small', 600.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051805203158', 'Cash (Pay at the Counter)'),
(464, '41st  Anniversary Shirt', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 31, 'Small', 600.00, 'uploads/1747510184_41_s Aniv Shirt (Front).png', '2025051805211358', 'Cash (Pay at the Counter)'),
(465, '41st  Anniversary Shirt', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 31, 'Medium', 600.00, 'uploads/1747510184_41_s Aniv Shirt (Front).png', '2025051805212958', 'Cash (Pay at the Counter)'),
(466, 'Crayola-8', 4, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 30.00, '2025-05-18', 'Complete', 58, 29, 'N/A', 120.00, 'uploads/1747493920_Crayola-8.png', '2025051805215558', 'Cash (Pay at the Counter)'),
(467, 'BSBA Polo', 3, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, 'Small', 900.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051805553858', 'Cash (Pay at the Counter)'),
(468, 'Brown Envelope', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Cancelled', 58, 34, 'N/A', 10.00, 'uploads/1747510446_Brown Envelope.png', '2025051805575558', 'Cash (Pay at the Counter)'),
(469, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051806023358', 'Cash (Pay at the Counter)'),
(471, 'BSBA Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, '2XL', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051806081458', 'Cash (Pay at the Counter)'),
(472, 'Elementary Polo', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 150.00, '2025-05-18', 'Complete', 58, 35, '2XL', 300.00, 'uploads/1747511258_Elementary Polo (Front).png', '2025051806093858', 'Cash (Pay at the Counter)'),
(473, '41st  Anniversary Shirt', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 31, 'Medium', 600.00, 'uploads/1747510184_41_s Aniv Shirt (Front).png', '2025051806094958', 'Cash (Pay at the Counter)'),
(474, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051806095758', 'Cash (Pay at the Counter)'),
(475, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051806134658', 'Cash (Pay at the Counter)'),
(476, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051806144458', 'Cash (Pay at the Counter)'),
(477, 'Brown Envelope', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 34, 'N/A', 10.00, 'uploads/1747510446_Brown Envelope.png', '2025051806165658', 'Cash (Pay at the Counter)'),
(478, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Pending', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051806195758', 'Cash (Pay at the Counter)'),
(479, 'BSBA Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, 'Medium', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051806272358', 'Cash (Pay at the Counter)'),
(480, 'Elementary Polo', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 150.00, '2025-05-18', 'Complete', 58, 35, '2XL', 300.00, 'uploads/1747511258_Elementary Polo (Front).png', '2025051806293658', 'Cash (Pay at the Counter)'),
(481, '41st  Anniversary Shirt', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 31, 'XS', 600.00, 'uploads/1747510184_41_s Aniv Shirt (Front).png', '2025051806294558', 'Cash (Pay at the Counter)'),
(482, 'BSBA Polo', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, 'Medium', 600.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051806300058', 'Cash (Pay at the Counter)'),
(484, 'Elementary Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 150.00, '2025-05-18', 'Complete', 58, 35, '2XL', 150.00, 'uploads/1747511258_Elementary Polo (Front).png', '2025051807060058', 'Cash (Pay at the Counter)'),
(485, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051807060658', 'Cash (Pay at the Counter)'),
(486, 'BSBA Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, '2XL', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051809474758', 'Cash (Pay at the Counter)'),
(487, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Pending', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051809475358', 'Cash (Pay at the Counter)'),
(488, 'BSBA Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, '2XL', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051809561658', 'Cash (Pay at the Counter)'),
(490, 'BSBA Polo ', 5, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, '2XL', 1500.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051809583258', 'Cash (Pay at the Counter)'),
(491, 'Art Paper', 8, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 80.00, 'uploads/1747493895_Art Paper.png', '2025051809583258', 'Cash (Pay at the Counter)'),
(510, 'BSBA Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Complete', 58, 33, 'Small', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051813105458', 'Send Online Receipt'),
(511, 'Brown Envelope', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 34, 'N/A', 10.00, 'uploads/1747510446_Brown Envelope.png', '2025051813113458', 'Cash (Pay at the Counter)'),
(512, '\n                                                Crayola-8                                            ', 1, 'Kem', '', NULL, NULL, 30.00, '2025-05-18', 'Complete', NULL, 29, 'N/A', 30.00, 'uploads/1747493920_Crayola-8.png', 'VMC-20250518-191423-950', 'Send Online Receipt'),
(513, '\n                                                Elementary Polo                                            ', 1, 'Kem', '', NULL, NULL, 150.00, '2025-05-18', 'Complete', NULL, 35, 'Medium', 150.00, 'uploads/1747511258_Elementary Polo (Front).png', 'VMC-20250518-192014-790', 'Cash (Pay at the Counter)'),
(514, '41st  Anniversary Shirt', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Pending', 58, 31, 'Small', 300.00, 'uploads/1747510184_41_s Aniv Shirt (Front).png', '2025051813202958', 'Cash (Pay at the Counter)'),
(515, 'Bondpaper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Cancelled', 58, 32, 'N/A', 10.00, 'uploads/1747510326_Bondpaper.png', '2025051813214958', 'Cash (Pay at the Counter)'),
(516, 'Crayola-8', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 30.00, '2025-05-18', 'Complete', 58, 29, 'N/A', 30.00, 'uploads/1747493920_Crayola-8.png', '2025051814030058', 'Cash (Pay at the Counter)'),
(519, 'Crayola-8', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 30.00, '2025-05-18', 'Pending', 58, 29, 'N/A', 30.00, 'uploads/1747493920_Crayola-8.png', '2025051815482158', 'Cash (Pay at the Counter)'),
(520, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Cancelled', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051817114758', 'Send Online Receipt'),
(522, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Pending', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051816225958', 'Cash (Pay at the Counter)'),
(523, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051817112558', 'Send Online Receipt'),
(525, 'BSBA Polo', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-18', 'Cancelled', 58, 33, 'Large', 600.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051817114758', 'Cash (Pay at the Counter)'),
(526, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-18', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051817120858', 'Cash (Pay at the Counter)'),
(530, 'Senior High School Skirt', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 200.00, '2025-05-19', 'Pending', 58, 48, 'XS', 200.00, 'uploads/1747595363_Senior HS Skirt (Front).png', '2025051821334958', 'Send Online Receipt'),
(531, 'BSED Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 150.00, '2025-05-19', 'Pending', 58, 43, 'XS', 150.00, 'uploads/1747594986_BSED Polo (Front).png', '2025051821362558', 'Send Online Receipt'),
(532, 'BSBA Polo', 1, 'Patrick francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 300.00, '2025-05-19', 'Pending', 12, 33, 'Small', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', NULL, 'Send Online Receipt'),
(533, 'BSBA Polo ', 1, 'Patrick francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 300.00, '2025-05-19', 'Pending', 12, 33, '2XL', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051903253412', 'Cash (Pay at the Counter)'),
(534, 'Elementary Polo', 1, 'Patrick francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 150.00, '2025-05-19', 'Complete', 12, 35, '2XL', 150.00, 'uploads/1747511258_Elementary Polo (Front).png', '2025051903260512', 'Cash (Pay at the Counter)'),
(535, 'Art Paper', 1, 'Patrick francisco', '220787', 'lopanggokem@gmail.com', '01234567899', 10.00, '2025-05-19', 'Pending', 12, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051903311812', 'Send Online Receipt'),
(536, 'Crayola-8', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 30.00, '2025-05-19', 'Complete', 58, 29, 'N/A', 30.00, 'uploads/1747493920_Crayola-8.png', '2025051903324558', 'Send Online Receipt'),
(537, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-19', 'Cancelled', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051905314658', 'Send Online Receipt'),
(538, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-19', 'Complete', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051905320858', 'Cash (Pay at the Counter)'),
(541, 'Art Paper', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 10.00, '2025-05-19', 'Pending', 58, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', '2025051910140058', 'Send Online Receipt'),
(542, '41st  Anniversary Shirt ', 2, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-19', 'Pending', 58, 31, 'Small', 600.00, 'uploads/1747510184_41_s Aniv Shirt (Front).png', '2025051910140058', 'Send Online Receipt'),
(543, 'BSBA Polo', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 300.00, '2025-05-19', 'Complete', 58, 33, 'Large', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', '2025051910321358', 'Send Online Receipt'),
(544, 'Crayola-8', 1, 'Kem Entic', '220734', 'k.lopanggo14@gmail.com', '09123456789', 30.00, '2025-05-19', 'Complete', 58, 29, 'N/A', 30.00, 'uploads/1747493920_Crayola-8.png', '2025051910352358', 'Cash (Pay at the Counter)'),
(545, '\n                                                Art Paper                                            ', 1, 'Kem', '', NULL, NULL, 10.00, '2025-05-19', 'Complete', NULL, 28, 'N/A', 10.00, 'uploads/1747493895_Art Paper.png', 'VMC-20250519-164637-900', 'Cash (Pay at the Counter)'),
(546, '\n                                BSBA Polo \n                            ', 1, 'Kem', '', NULL, NULL, 300.00, '2025-05-19', 'Complete', NULL, 33, 'Medium', 300.00, 'uploads/1747510400_BSBA Polo (Front) (2).png', 'VMC-20250519-164637-900', 'Cash (Pay at the Counter)');

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
  `rating` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `dr_number`, `price`, `type`, `date_modified`, `admin_handled`, `image`, `qr_code`, `rating`) VALUES
(28, 'Art Paper', 'DR1234676', 10.00, 'Supplies', '2025-05-17', '', 'uploads/1747493895_Art Paper.png', NULL, 4.5),
(29, 'Crayola-8', 'DR1234676', 30.00, 'Supplies', '2025-05-17', '', 'uploads/1747493920_Crayola-8.png', NULL, 4),
(31, '41st  Anniversary Shirt ', 'DR1234670', 300.00, 'Uniform', '2025-05-17', '', 'uploads/1747510184_41_s Aniv Shirt (Front).png', NULL, 4),
(32, 'Bondpaper', 'DR1234676', 10.00, 'Supplies', '2025-05-17', '', 'uploads/1747510326_Bondpaper.png', NULL, NULL),
(33, 'BSBA Polo ', 'DR1234670', 300.00, 'Uniform', '2025-05-17', '', 'uploads/1747510400_BSBA Polo (Front) (2).png', NULL, 3.3333),
(34, 'Brown Envelope', 'DR1234676', 10.00, 'Supplies', '2025-05-17', '', 'uploads/1747510446_Brown Envelope.png', NULL, NULL),
(35, 'Elementary Polo', 'DR1234670', 150.00, 'Uniform', '2025-05-17', '', 'uploads/1747511258_Elementary Polo (Front).png', NULL, 4),
(39, 'Clay', 'DR1234676', 40.00, 'Supplies', '2025-05-18', '', 'uploads/1747593456_Clay.png', NULL, NULL),
(41, '1/4 paper', 'DR1234677', 10.00, 'Supplies', '2025-05-18', '', 'uploads/1747594809_1_4paper.png', NULL, NULL),
(42, 'BSBA SKIRT', 'DR1234670', 150.00, 'Uniform', '2025-05-18', '', 'uploads/1747594942_BSBA Skirt (Front).png', NULL, NULL),
(43, 'BSED Polo', 'DR1234670', 150.00, 'Uniform', '2025-05-18', '', 'uploads/1747594986_BSED Polo (Front).png', NULL, NULL),
(44, 'BSIS Blouse', 'DR1234670', 150.00, 'Uniform', '2025-05-18', '', 'uploads/1747595043_BSIS Blouse (Front).png', NULL, NULL),
(45, 'BSTM Blouse', 'DR1234670', 150.00, 'Uniform', '2025-05-18', '', 'uploads/1747595096_BSTM Blouse (Front).png', NULL, NULL),
(46, 'Elementary Blouse', 'DR1234670', 100.00, 'Uniform', '2025-05-18', '', 'uploads/1747595176_Elementary Blouse (Front) (2).png', NULL, NULL),
(47, 'Junior High School Skirt ', 'DR1234670', 200.00, 'Uniform', '2025-05-18', '', 'uploads/1747595309_Junior HS Skirt (Front).png', NULL, NULL),
(48, 'Senior High School Skirt', 'DR1234670', 200.00, 'Uniform', '2025-05-18', '', 'uploads/1747595363_Senior HS Skirt (Front).png', NULL, NULL),
(49, 'Preschool Polo ', 'DR1234670', 150.00, 'Uniform', '2025-05-18', '', 'uploads/1747595418_Preschool Polo (Front).png', NULL, NULL),
(50, 'Senior High School Blouse', 'DR1234670', 200.00, 'Uniform', '2025-05-18', '', 'uploads/1747595499_Senior HS (Front).png', NULL, NULL),
(51, 'Senior High School Polo', 'DR1234670', 150.00, 'Uniform', '2025-05-18', '', 'uploads/1747595580_Senior HS Polo (Front).png', NULL, NULL),
(53, 'VMC LACE', 'DR1234677', 100.00, 'Supplies', '2025-05-18', '', 'uploads/1747595706_VMC Lace.png', NULL, NULL),
(54, 'BSTM POLO', 'DR1234670', 250.00, 'Uniform', '2025-05-18', '', 'uploads/1747595790_BSTM Polo (Front).png', NULL, NULL),
(55, 'BSED Blouse ', 'DR1234670', 200.00, 'Uniform', '2025-05-18', '', 'uploads/1747595862_BSED Blouse (Front).png', NULL, NULL),
(56, 'Basic Education PE Jogging Pants', 'DR1234670', 300.00, 'Uniform', '2025-05-18', '', 'uploads/1747595931_Basic Education PE Jogging Pants (Front).png', NULL, NULL),
(57, 'Ballpen', 'DR1234677', 10.00, 'Supplies', '2025-05-18', '', 'uploads/1747595992_Ballpen.png', NULL, NULL),
(58, 'Cartolina', 'DR1234677', 15.00, 'Supplies', '2025-05-18', '', 'uploads/1747596039_Cartolina.png', NULL, NULL),
(59, 'Colored Folder', 'DR1234677', 12.00, 'Supplies', '2025-05-18', '', 'uploads/1747596069_Colorful Folder.png', NULL, NULL),
(60, 'Construction paper', 'DR1234677', 15.00, 'Supplies', '2025-05-18', '', 'uploads/1747596099_Construction paper.png', NULL, NULL),
(61, 'Correction Tape', 'DR1234677', 25.00, 'Supplies', '2025-05-18', '', 'uploads/1747596143_Correction Tape.png', NULL, NULL),
(62, 'Eraser', 'DR1234677', 10.00, 'Supplies', '2025-05-18', '', 'uploads/1747596168_Eraser.png', NULL, NULL),
(63, 'Illustration Board', 'DR1234677', 15.00, 'Supplies', '2025-05-18', '', 'uploads/1747596192_Illustration Board.png', NULL, NULL),
(64, 'Manila Paper', 'DR1234677', 12.00, 'Supplies', '2025-05-18', '', 'uploads/1747596223_Manila Paper.png', NULL, NULL),
(65, 'Paint Brush', 'DR1234677', 10.00, 'Supplies', '2025-05-18', '', 'uploads/1747596248_paint brush.png', NULL, NULL),
(68, 'Watercolor', 'DR1234677', 10.00, 'Supplies', '2025-05-19', '', 'uploads/1747636040_Watercolor.png', NULL, NULL),
(69, 'Yellow Paper', 'DR1234677', 55.00, 'Supplies', '2025-05-19', '', 'uploads/1747636074_Yellow Paper.png', NULL, NULL),
(70, 'VMC Small Notebook', 'DR1234677', 55.00, 'Supplies', '2025-05-19', '', 'uploads/1747636099_VMC Small Notebook.png', NULL, NULL),
(71, 'VMC Big Notebook', 'DR1234677', 30.00, 'Supplies', '2025-05-19', '', 'uploads/1747636129_VMC Big Notebook.png', NULL, NULL);

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
(20, 452, 31, 12, 5, 'PASADONG CAPSTONE', 1, '2025-05-17 20:15:25'),
(21, 455, 35, 12, 4, 'PASADONG FINALS AMEN', 1, '2025-05-18 02:58:10'),
(22, 454, 33, 12, 1, 'PASADONG FINALS AMEN', 1, '2025-05-18 02:58:26'),
(23, 456, 28, 12, 5, 'PASADONG FINALS AMEN', 1, '2025-05-18 02:59:26'),
(24, 467, 33, 58, 5, 'PASADONG CAPSTONE 1 AMEN', 1, '2025-05-18 03:56:54'),
(30, 464, 31, 58, 3, 'test123', 1, '2025-05-18 15:35:56');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `gender` enum('Male','Female','Unisex') DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `gender`, `stock`) VALUES
(14, 28, '', NULL, 12),
(15, 29, '', NULL, 18),
(19, 31, 'XS', 'Male', 48),
(20, 31, 'Small', 'Male', 20),
(21, 31, 'Medium', 'Male', 28),
(22, 32, '', NULL, 16),
(23, 33, 'XS', 'Male', 10),
(24, 33, 'Small', 'Male', 6),
(25, 33, 'Medium', 'Male', 7),
(26, 33, 'Large', 'Male', 9),
(27, 33, 'XL', 'Male', 10),
(28, 33, '2XL', 'Male', 3),
(29, 34, '', NULL, 7),
(30, 35, 'XS', 'Male', 10),
(31, 35, 'Small', 'Male', 18),
(32, 35, 'Medium', 'Male', 20),
(33, 35, 'Large', 'Male', 20),
(34, 35, 'XL', 'Male', 20),
(35, 35, '2XL', 'Male', 14),
(40, 39, '', NULL, 50),
(42, 41, '', NULL, 50),
(43, 42, 'XS', 'Female', 50),
(44, 42, 'Small', 'Female', 50),
(45, 42, 'Medium', 'Female', 50),
(46, 42, 'Large', 'Female', 50),
(47, 42, 'XL', 'Female', 50),
(48, 42, '2XL', 'Female', 50),
(49, 43, 'XS', 'Male', 50),
(50, 43, 'Small', 'Male', 50),
(51, 43, 'Medium', 'Male', 50),
(52, 43, 'Large', 'Male', 50),
(53, 43, 'XL', 'Male', 50),
(54, 43, '2XL', 'Male', 50),
(55, 44, 'XS', 'Female', 50),
(56, 44, 'Small', 'Female', 50),
(57, 44, 'Medium', 'Female', 50),
(58, 44, 'Large', 'Female', 50),
(59, 44, 'XL', 'Female', 50),
(60, 44, '2XL', 'Female', 50),
(61, 45, 'XS', 'Female', 50),
(62, 45, 'Small', 'Female', 50),
(63, 45, 'Medium', 'Female', 50),
(64, 45, 'Large', 'Female', 50),
(65, 45, 'XL', 'Female', 50),
(66, 45, '2XL', 'Female', 50),
(67, 46, 'XS', 'Female', 50),
(68, 46, 'Small', 'Female', 50),
(69, 46, 'Medium', 'Female', 50),
(70, 46, 'Large', 'Female', 50),
(71, 46, 'XL', 'Female', 50),
(72, 46, '2XL', 'Female', 50),
(73, 47, 'XS', 'Female', 50),
(74, 47, 'Small', 'Female', 50),
(75, 47, 'Medium', 'Female', 50),
(76, 47, 'Large', 'Female', 50),
(77, 47, 'XL', 'Female', 50),
(78, 47, '2XL', 'Female', 50),
(79, 48, 'XS', 'Female', 50),
(80, 48, 'Small', 'Female', 50),
(81, 48, 'Medium', 'Female', 50),
(82, 48, 'Large', 'Female', 50),
(83, 48, 'XL', 'Female', 50),
(84, 48, '2XL', 'Female', 50),
(85, 49, 'XS', 'Male', 50),
(86, 49, 'Small', 'Male', 50),
(87, 49, 'Medium', 'Male', 50),
(88, 49, 'Large', 'Male', 50),
(89, 49, 'XL', 'Male', 50),
(90, 49, '2XL', 'Male', 50),
(91, 50, 'XS', 'Female', 50),
(92, 50, 'Small', 'Female', 50),
(93, 50, 'Medium', 'Female', 50),
(94, 50, 'Large', 'Female', 50),
(95, 50, 'XL', 'Female', 50),
(96, 50, '2XL', 'Female', 50),
(97, 51, 'XS', 'Male', 50),
(98, 51, 'Small', 'Male', 50),
(99, 51, 'Medium', 'Male', 50),
(100, 51, 'Large', 'Male', 50),
(101, 51, 'XL', 'Male', 50),
(102, 51, '2XL', 'Male', 50),
(109, 53, '', NULL, 150),
(110, 54, 'XS', 'Male', 50),
(111, 54, 'Small', 'Male', 50),
(112, 54, 'Medium', 'Male', 50),
(113, 54, 'Large', 'Male', 50),
(114, 54, 'XL', 'Male', 50),
(115, 54, '2XL', 'Male', 50),
(116, 55, 'XS', 'Female', 50),
(117, 55, 'Small', 'Female', 50),
(118, 55, 'Medium', 'Female', 50),
(119, 55, 'Large', 'Female', 50),
(120, 55, 'XL', 'Female', 50),
(121, 55, '2XL', 'Female', 49),
(122, 56, 'XS', 'Unisex', 50),
(123, 56, 'Small', 'Unisex', 50),
(124, 56, 'Medium', 'Unisex', 50),
(125, 56, 'Large', 'Unisex', 50),
(126, 56, 'XL', 'Unisex', 50),
(127, 56, '2XL', 'Unisex', 50),
(128, 57, '', NULL, 150),
(129, 58, '', NULL, 50),
(130, 59, '', NULL, 50),
(131, 60, '', NULL, 50),
(132, 61, '', NULL, 50),
(133, 62, '', NULL, 50),
(134, 63, '', NULL, 50),
(135, 64, '', NULL, 50),
(136, 65, '', NULL, 50),
(139, 68, '', NULL, 1),
(140, 69, '', NULL, 10),
(141, 70, '', NULL, 10),
(142, 71, '', NULL, 99);

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
  `active_status` enum('Active','Inactive') DEFAULT 'Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_no`, `student_pass`, `created_at`, `updated_at`, `student_fname`, `student_lname`, `student_mname`, `email`, `phone_number`, `year_level`, `birthday`, `photo`, `otp`, `otp_expiry`, `last_activity`, `active_status`) VALUES
(12, '220787', '$2y$10$WfY40puGZ7ITNHfMAdK.S.zfRphJHbzCKucaDsXSgkhQlVFIFcMeW', '2025-04-15 03:08:11', '2025-05-19 01:32:18', 'Patrick', 'francisco', 'balderas', 'lopanggokem@gmail.com', '01234567899', 'Bachelor of Science in Criminology', '2003-11-11', '1747080600_Fati\'s Logo.png', '$2y$10$MCtJYJ0/v9UomvaK9lwX1eoaz4viisA/p5N7fvy7/d7yvtMVXPggm', '2025-05-17 13:21:45', '2025-05-19 01:24:04', 'Active'),
(57, '220733', '$2y$10$07gyjD3YynS5iVf5DWkoeeLZmLDy9d08ueD/o/uvGyoCXQx5Iykte', '2025-05-18 03:11:00', '2025-05-19 03:20:56', 'Villagers', 'College', 'Montessori', 'syyxzz315@gmail.com', '09318734292', 'Bachelor of Science in Information System', '0200-06-14', '1747537860_VMS-LOGO-Official-01.png', NULL, NULL, '2025-05-18 03:11:00', 'Inactive'),
(58, '220734', '$2y$10$Hsm8pDgvYbSS.eniafR/D.XuF4HssFTl8hJvm3j2T1ybL/Do7HoKi', '2025-05-18 03:19:20', '2025-05-19 04:14:34', 'Kem', 'Entic', 'L.', 'k.lopanggo14@gmail.com', '09123456789', 'Bachelor of Science in Business Administration', '2003-06-14', '1747543660_VMS-LOGO-Official-01.png', NULL, NULL, '2025-05-19 04:14:34', 'Active'),
(59, '123456', '$2y$10$pNtiWGuqIn1KC5vMhTX4n.CvsRGD8R.46yjHk8h1V4VQ8gOQJrD06', '2025-05-19 06:35:32', '2025-05-19 06:35:32', 'John', 'Doe', 'Michael', 'johndoe@gmail.com', '09123456789', '3', '2002-05-15', 'profile_pic.png', NULL, NULL, '2025-05-19 06:35:32', 'Active'),
(60, '123457', '$2y$10$nqFc.bjp42WF/0VIPA78t.hD09uuIXFSSWB51uGBvr9xd.htdf/Bi', '2025-05-19 06:35:32', '2025-05-19 06:35:32', 'Jane', 'Smith', 'Emily', 'janesmith@gmail.com', '09123456780', '2', '2003-03-22', 'profile_pic.png', NULL, NULL, '2025-05-19 06:35:32', 'Active'),
(61, '123458', '$2y$10$gCf9i72rccHjgmIHP7Q82Orvyr8yu0sh.MgfgYFSWUOfq15axWZWK', '2025-05-19 06:35:32', '2025-05-19 06:35:32', 'Alex', 'Lee', 'Robert', 'alexlee@gmail.com', '09123456781', '1', '2004-11-10', 'profile_pic.png', NULL, NULL, '2025-05-19 06:35:32', 'Active'),
(62, '123459', '$2y$10$VTmxPHZ8pVpRA.Y5V76WeOnzp1NRnJeVnQuWQ2kSaLNFep2m0r2ii', '2025-05-19 06:35:32', '2025-05-19 06:35:32', 'Maria', 'Garcia', 'Anna', 'mariagarcia@gmail.com', '09123456782', '4', '2001-08-25', 'profile_pic.png', NULL, NULL, '2025-05-19 06:35:32', 'Active'),
(63, '123450', '$2y$10$oLpZNwtpib/9bfbNgBfd6OtoeNSVN6m0wctP55/MTHJhVZcR1jMnq', '2025-05-19 06:35:32', '2025-05-19 06:35:32', 'Lucas', 'Brown', 'James', 'lucasbrown@gmail.com', '09123456783', '3', '2002-01-30', 'profile_pic.png', NULL, NULL, '2025-05-19 06:35:32', 'Active'),
(64, '12360', '$2y$10$7qJnZ.zEYQ69tNFcIpJ03uNCK9E1Hl45svhYWGNIkXuBTnueFr84K', '2025-05-19 06:35:32', '2025-05-19 06:35:32', 'Emily', 'White', 'Susan', 'emilywhite@gmail.com', '09123456784', '2', '2003-07-19', 'profile_pic.png', NULL, NULL, '2025-05-19 06:35:32', 'Active');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
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
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

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
