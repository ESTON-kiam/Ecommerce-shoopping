

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";




CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `admins` (`id`, `username`, `password`, `phone`, `email`, `created_at`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'estonkiam', '$2y$10$LhPH2oOjs86DP6Zav39Ga.5wT9Rvu45.fByLgMRTUFj5YqoXOAlN6', '254757196660', 'engestonbrandonkiama@gmail.com', '2024-11-11 11:29:48', NULL, NULL);



CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `created_at`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'Milton', 'Otieno', 'estonnmose@gmail.com', '$2y$10$D7ugX4e4nFCZoa701ZxxDOZtRknOUCpH7QmUrdNY7l6WNJmXleEMi', '0757196660', 'Temple', 'Kindaruma', 'Nairobi', '0100', '272002', 'Kenya', '2024-11-18 07:49:41', NULL, NULL),
(3, 'Eston', '', 'kiamaeston0@gmail.com', '$2y$10$l0.MtEeTWjV.SMCWgrkOHOU0Yx79WzB2w6V06N6Gh1TLaeJc9K/Ru', '0757196660', 'Topplaza building', 'Kindaruma road', 'Mombasa', '01000', '272002', 'Kenya', '2024-11-18 07:52:11', NULL, NULL),
(4, 'Alex', 'James', 'engestonbrandon@gmail.com', '$2y$10$htLZQwQIi/mk6ectRTs4z.lgcsBij36aLmMMmZtOjn5R0SURhm9M6', '0757196660', 'Kinoo', 'Kinoo stage', 'Kinoo town', '0100', '272002', 'Kenya', '2024-11-27 09:50:57', '11aa2b3e3ffcc6aad46d50e3d16f8ffde54d94f07c2036dd4434ec6896398a05', '2025-02-17 14:20:09'),
(5, 'smith', 'tonyroy', 'Royt5908@gmail.com', '$2y$10$oTf3E/pOY.nkRq44B.qE7..ZTj1KWe/KSXzTgdvHYAKMf2A5RAuc.', '0765554444', 'kayole', 'kayole', 'Nairobi', '0100', '24814-00502', 'Kenya', '2024-12-11 07:13:10', NULL, NULL),
(6, 'Prestone', 'ongoro', 'prestone@gmail.com', '$2y$10$X6ZuuIiuQA2DNAlSaffoKO1ME9RCJ35ygN3.niGZQfe5hrY.qySuS', '35476476476', 'gataka', 'destiny', 'Nairobi', '0100', '24814-00502', 'Kenya', '2024-12-23 04:59:46', NULL, NULL);



CREATE TABLE `mpesa_payments` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `checkout_request_id` varchar(50) NOT NULL,
  `status` enum('COMPLETED','FAILED','CANCELLED','TIMEOUT','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mpesa_receipt` varchar(50) DEFAULT NULL,
  `result_code` varchar(10) DEFAULT NULL,
  `result_desc` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `payment_method` enum('mpesa','card','cash') NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `product_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



INSERT INTO `orders` (`id`, `customer_id`, `total_amount`, `shipping_address`, `city`, `phone`, `payment_method`, `payment_status`, `status`, `transaction_id`, `created_at`, `updated_at`, `product_details`) VALUES
(95, 1, 16049.89, 'Temple', 'Nairobi', '0757196660', 'card', 'pending', 'pending', NULL, '2025-02-18 07:37:16', '2025-02-18 07:37:16', '[{\"id\":4,\"name\":\" Coffee Table\",\"price\":6750,\"quantity\":1,\"item_total\":6750,\"stock_quantity\":10,\"image\":\"6731f5142a191.jpg\"},{\"id\":18,\"name\":\"Ramtons RM\\/582\",\"price\":9299.89,\"quantity\":1,\"item_total\":9299.89,\"stock_quantity\":10,\"image\":\"673dc6610f148.jpg\"}]');



CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `before_order_items_insert` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
    DECLARE current_total DECIMAL(10, 2);
    
    -- Recalculate the total amount based on all items in the order
    SELECT SUM(price * quantity) INTO current_total
    FROM order_items
    WHERE order_id = NEW.order_id;
    
    -- Update total_amount in the orders table
    UPDATE orders 
    SET total_amount = current_total
    WHERE id = NEW.order_id;
END
$$
DELIMITER ;


CREATE TABLE `order_summaries` (
`order_id` int(11)
,`customer_id` int(11)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`email` varchar(255)
,`total_amount` decimal(10,2)
,`status` enum('pending','processing','shipped','delivered','cancelled')
,`payment_status` enum('pending','paid','failed')
,`payment_method` enum('mpesa','card','cash')
,`order_date` timestamp
,`total_items` bigint(21)
,`shipping_address` varchar(255)
,`city` varchar(100)
,`phone` varchar(20)
);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `category` enum('Official Stores','Phones & Tablets','TVs & Audio','Appliances','Health & Beauty','Home & Office','Fashion','Computing','Supermarket','Baby Products','Sporting Goods') NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `sku`, `category`, `description`, `price`, `image`, `stock_quantity`, `status`, `created_at`, `updated_at`) VALUES
(4, ' Coffee Table', 'KAFFFITABLE68745', 'Appliances', 'Julz Modern Design Coffee Table With Storage', 6750.00, '6731f5142a191.jpg', 9, 'active', '2024-11-11 12:14:12', '2025-02-18 07:37:16'),
(8, 'TV', 'WRTY008877', 'TVs & Audio', 'Lyons LES4304W 43” 2K FHD Smart Android Frameless TV With Bluetooth - Black (1YR WRTY)', 16999.99, '6731ffa25810e.jpg', 22, 'active', '2024-11-11 12:59:14', '2024-11-18 13:33:43'),
(11, 'Vitron 2.1CH SUB WOOFER ', 'Vitron21gfyhgbujr', 'TVs & Audio', 'Vitron 2.1CH SUB WOOFER SYSTEM WITH QUALITY BASS', 3599.00, '673206a8a56d5.jpg', 10, 'active', '2024-11-11 13:29:12', '2025-02-18 07:28:18'),
(12, 'Gotv Decoder', 'Dec6565685', 'Appliances', 'Gotv Decoder', 999.99, '673208862c588.jpg', 14, 'active', '2024-11-11 13:37:10', '2024-11-19 12:44:40'),
(13, 'Oraimo Earpods', 'ORM123454', 'Official Stores', 'Oraimo Earpods,True -Wireless Free Pods, Superior Sound air f9 pro +', 1049.99, '673208fda41cb.jpg', 35, 'active', '2024-11-11 13:39:09', '2024-11-19 12:50:33'),
(14, 'Single Door Refrigerator', 'Roch 126564158', 'Home & Office', 'Roch RFR-120S-I Single Door Refrigerator - 90 Litres - Silver (1Y WRTY)', 16999.00, '673af3ce59c54.jpg', 10, 'active', '2024-11-18 07:59:10', '2024-11-20 11:17:21'),
(15, 'Wireless Bluetooth Mouse', 'Mouse 125354863547h', 'Computing', 'Wireless Bluetooth Mouse Rechargeable Mute - Black', 350.00, '673c50b174130.jpg', 48, 'active', '2024-11-19 08:47:45', '2024-11-27 10:15:33'),
(16, 'Power King Extension Cable ', 'Power2457867h', 'Appliances', 'Power King Extension Cable - 5 Way - White.', 420.00, '673d9ac192625.jpg', 14, 'active', '2024-11-20 08:16:01', '2025-02-17 10:49:25'),
(17, 'Oraimo Boom', 'Boom5487358hy', 'TVs & Audio', 'Oraimo Boom Powerful Portable Wiress Speaker- Black', 6420.00, '673dc4acb826f.jpg', 10, 'active', '2024-11-20 11:14:52', '2024-11-22 11:38:34'),
(18, 'Ramtons RM/582', 'Ramtons1254rt', 'Appliances', 'Ramtons RM/582-Electric Pressure Cooker- Black (1YR WRTY)', 9299.89, '673dc6610f148.jpg', 9, 'active', '2024-11-20 11:22:09', '2025-02-18 07:37:16');

-- --------------------------------------------------------

--
-- Table structure for table `saved_items`
--

CREATE TABLE `saved_items` (
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `site_title` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_title`, `admin_email`, `currency`, `maintenance_mode`, `created_at`, `updated_at`) VALUES
(1, 'ModernCart', 'moderncart@gmail.com', 'KSH', 0, '2024-11-19 11:04:24', '2024-11-22 11:43:07');

-- --------------------------------------------------------

--
-- Structure for view `order_summaries`
--
DROP TABLE IF EXISTS `order_summaries`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_summaries`  AS SELECT `o`.`id` AS `order_id`, `o`.`customer_id` AS `customer_id`, `c`.`first_name` AS `first_name`, `c`.`last_name` AS `last_name`, `c`.`email` AS `email`, `o`.`total_amount` AS `total_amount`, `o`.`status` AS `status`, `o`.`payment_status` AS `payment_status`, `o`.`payment_method` AS `payment_method`, `o`.`created_at` AS `order_date`, count(`oi`.`id`) AS `total_items`, `o`.`shipping_address` AS `shipping_address`, `o`.`city` AS `city`, `o`.`phone` AS `phone` FROM ((`orders` `o` join `customers` `c` on(`o`.`customer_id` = `c`.`customer_id`)) join `order_items` `oi` on(`o`.`id` = `oi`.`order_id`)) GROUP BY `o`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_items`
--
ALTER TABLE `saved_items`
  ADD PRIMARY KEY (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  ADD CONSTRAINT `fk_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `saved_items`
--
ALTER TABLE `saved_items`
  ADD CONSTRAINT `saved_items_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `saved_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;


