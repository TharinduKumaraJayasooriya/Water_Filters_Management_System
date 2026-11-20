-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2026 at 03:48 PM
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
-- Database: `my_web_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddNewItem` (IN `p_name` VARCHAR(100), IN `p_desc` TEXT, IN `p_price` DECIMAL(10,2), IN `p_user_id` INT)   BEGIN
    INSERT INTO items (name, description, price, user_id) 
    VALUES (p_name, p_desc, p_price, p_user_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddFilterProduct` (IN `p_name` VARCHAR(150), IN `p_category` VARCHAR(50), IN `p_price` DECIMAL(10,2), IN `p_stock` VARCHAR(100))   BEGIN
    INSERT INTO items (name, category, price, stock) 
    VALUES (p_name, p_category, p_price, p_stock);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_RegisterUser` (IN `p_name` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_password` VARCHAR(255), IN `p_role` VARCHAR(20))   BEGIN
    INSERT INTO users (name, email, password, role) 
    VALUES (p_name, p_email, p_password, p_role);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` enum('Water Filter','Replacement Part','Repair Service') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT 'default_water.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `price`, `stock`, `created_at`, `image`) VALUES
(2, 'Under Water Filter', 'Water Filter', 78000.00, 'Available', '2026-11-03 14:29:23', 'uploads/item_6ae9efc30e59e.jpg'),
(3, 'RO Filter Unit', 'Water Filter', 45000.00, 'Available', '2026-11-03 14:29:50', 'uploads/item_6ae9efde89e34.jpg'),
(4, 'Tap-mounted / Basic Faucet Filter', 'Water Filter', 2500.00, 'Available', '2026-11-03 18:16:33', 'uploads/item_6aea25011ef02.jpg'),
(5, 'Gravity / Ceramic Candle Filters (16L', 'Water Filter', 12000.00, 'Available', '2026-11-03 18:18:18', 'uploads/item_6aea256a57c1d.jpg'),
(6, 'UV (Ultraviolet) Water Purifier', 'Water Filter', 48000.00, 'Available', '2026-11-03 18:19:51', 'uploads/item_6aea25c7b09e1.jpg'),
(7, 'Main Line / Whole House Filter', 'Water Filter', 40000.00, 'Available', '2026-11-03 18:21:25', 'uploads/item_6aea2625cf132.jpg'),
(8, 'Tap Filter Replacement Cartridges', 'Replacement Part', 2000.00, 'Available', '2026-11-03 18:25:08', 'uploads/item_6aea27040c6af.jpg'),
(9, 'UF (Ultrafiltration) Membrane Cartridge', 'Replacement Part', 4850.00, 'Available', '2026-11-03 18:26:25', 'uploads/item_6aea27515709f.jpg'),
(10, 'RO Membrane', 'Replacement Part', 7850.00, 'Available', '2026-11-03 18:28:17', 'uploads/item_6aea27c10022d.jpg'),
(11, 'RO Pressure Tank', 'Replacement Part', 13750.00, 'Available', '2026-11-03 18:29:29', 'uploads/item_6aea2809ee2aa.jpg'),
(12, 'UV Quartz Glass Sleeve', 'Replacement Part', 4950.00, 'Available', '2026-11-03 18:31:05', 'uploads/item_6aea28698f402.jpg'),
(13, 'Media (Activated Carbon / Manganese Greensand / Resin per bag)', 'Replacement Part', 17600.00, 'Available', '2026-11-03 18:34:04', 'uploads/item_6aea291cd93c4.jpg'),
(14, 'Technician Visit & Initial Inspection2250', 'Repair Service', 2250.00, 'Available', '2026-11-03 18:36:43', 'uploads/item_6aea29bb5e9f3.jpg'),
(15, 'Routine Servicing & General Cleaning', 'Repair Service', 3000.00, 'Available', '2026-11-03 18:38:22', 'uploads/item_6aea2a1e84469.jpg'),
(16, 'RO Booster Pump Service / Replacement Labor', 'Repair Service', 4000.00, 'Available', '2026-11-03 18:39:53', 'uploads/item_6aea2a7999468.jpg');

--
-- Triggers `items`
--
DELIMITER $$
CREATE TRIGGER `after_item_delete` AFTER DELETE ON `items` FOR EACH ROW BEGIN
    INSERT INTO item_logs (action_type, item_id, details) 
    VALUES ('DELETE', OLD.id, CONCAT('Deleted item: ', OLD.name));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_item_insert` AFTER INSERT ON `items` FOR EACH ROW BEGIN
    INSERT INTO item_logs (action_type, item_id, details) 
    VALUES ('INSERT', NEW.id, CONCAT('Added item: ', NEW.name));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `item_logs`
--

CREATE TABLE `item_logs` (
  `log_id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_logs`
--

INSERT INTO `item_logs` (`log_id`, `action_type`, `item_id`, `details`, `log_time`) VALUES
(1, 'INSERT', 1, 'Added item: RO Filter Unit', '2026-11-03 13:41:44'),
(2, 'INSERT', 2, 'Added item: Under Water Filter', '2026-11-03 14:29:23'),
(3, 'DELETE', 1, 'Deleted item: RO Filter Unit', '2026-11-03 14:29:29'),
(4, 'INSERT', 3, 'Added item: RO Filter Unit', '2026-11-03 14:29:50'),
(5, 'INSERT', 4, 'Added item: Tap-mounted / Basic Faucet Filter', '2026-11-03 18:16:33'),
(6, 'INSERT', 5, 'Added item: Gravity / Ceramic Candle Filters (16L', '2026-11-03 18:18:18'),
(7, 'INSERT', 6, 'Added item: UV (Ultraviolet) Water Purifier', '2026-11-03 18:19:51'),
(8, 'INSERT', 7, 'Added item: Main Line / Whole House Filter', '2026-11-03 18:21:25'),
(9, 'INSERT', 8, 'Added item: Tap Filter Replacement Cartridges', '2026-11-03 18:25:08'),
(10, 'INSERT', 9, 'Added item: UF (Ultrafiltration) Membrane Cartridge', '2026-11-03 18:26:25'),
(11, 'INSERT', 10, 'Added item: RO Membrane', '2026-11-03 18:28:17'),
(12, 'INSERT', 11, 'Added item: RO Pressure Tank', '2026-11-03 18:29:29'),
(13, 'INSERT', 12, 'Added item: UV Quartz Glass Sleeve', '2026-11-03 18:31:05'),
(14, 'INSERT', 13, 'Added item: Media (Activated Carbon / Manganese Greensand / Resin per bag)', '2026-11-03 18:34:04'),
(15, 'INSERT', 14, 'Added item: Technician Visit & Initial Inspection2250', '2026-11-03 18:36:43'),
(16, 'INSERT', 15, 'Added item: Routine Servicing & General Cleaning', '2026-11-03 18:38:22'),
(17, 'INSERT', 16, 'Added item: RO Booster Pump Service / Replacement Labor', '2026-11-03 18:39:53');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 1, 45000.00, 'Accepted / Completed', '2026-11-03 14:03:55');

-- --------------------------------------------------------

--
-- Table structure for table `repair_bookings`
--

CREATE TABLE `repair_bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `filter_model` varchar(150) DEFAULT NULL,
  `issue_description` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_bookings`
--

INSERT INTO `repair_bookings` (`booking_id`, `user_id`, `filter_model`, `issue_description`, `preferred_date`, `status`, `created_at`) VALUES
(1, 1, 'RO Filter', 'This water filter has small leak', '2020-03-12', 'In Progress / Resolved', '2026-11-03 14:33:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'user',
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`, `last_login`) VALUES
(1, 'sandaru', 'sandaru@gmail.com', '$2y$10$lspgV8rZaUl0XRrmSO1Miu2gVot9Rnd.ZYry0mNKgLsz9OvC7TRzS', '2026-11-03 13:33:30', 'user', '2026-11-10 16:08:29'),
(2, 'Tharindu', 'tharindu@gmail.com', '$2y$10$vTNa2EI9hcX6kxIdy7cngutmp7DUnAvMRfyr9tSfHB3UZ8GLfDjAS', '2026-11-03 13:40:05', 'admin', '2026-11-10 16:08:29'),
(3, 'Dimuthu', 'dimuthu@gmail.com', '$2y$10$RcalhO29/KAR88Ck0F/Uj.Zw.FEOtibDaYRhms1CS2YViqeUfZcBi', '2026-11-03 16:23:41', 'user', '2026-11-10 16:08:29'),
(4, 'Tharindu', 'tharindukumaraj@gmail.com', '$2y$10$L8yQnwptV4iZAV51vMXWLO8QipiX46N9wS/NcqIkf9ZIAjVlSVUnG', '2026-11-03 17:56:08', 'user', '2026-11-10 16:08:29'),
(5, 'Nimal', 'nimal@gmail.com', '$2y$10$sOPyIpcQMBbXbLZmd/UANelZPFVluGRS5MrYyACxyTPlVG8gMq5gK', '2026-11-04 05:28:31', 'user', '2026-11-10 16:08:29');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_user_login_log` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.last_login != OLD.last_login THEN
        INSERT INTO user_login_logs (user_id, username, action_type)
        VALUES (NEW.id, NEW.name, 'LOGIN');
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_login_logs`
--

CREATE TABLE `user_login_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `action_type` enum('LOGIN','LOGOUT') NOT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_active_repair_orders`
-- (See below for the actual view)
--
CREATE TABLE `vw_active_repair_orders` (
`id` int(11)
,`name` varchar(150)
,`category` enum('Water Filter','Replacement Part','Repair Service')
,`price` decimal(10,2)
,`stock` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_active_repair_orders`
--
DROP TABLE IF EXISTS `vw_active_repair_orders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_active_repair_orders`  AS SELECT `items`.`id` AS `id`, `items`.`name` AS `name`, `items`.`category` AS `category`, `items`.`price` AS `price`, `items`.`stock` AS `stock` FROM `items` WHERE `items`.`category` = 'Repair Service' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_logs`
--
ALTER TABLE `item_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `repair_bookings`
--
ALTER TABLE `repair_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_login_logs`
--
ALTER TABLE `user_login_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `item_logs`
--
ALTER TABLE `item_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `repair_bookings`
--
ALTER TABLE `repair_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_login_logs`
--
ALTER TABLE `user_login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `repair_bookings`
--
ALTER TABLE `repair_bookings`
  ADD CONSTRAINT `repair_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
