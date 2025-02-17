

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



CREATE DATABASE IF NOT EXISTS `ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ecommerce`;


CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    stock_quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);



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



ALTER TABLE mpesa_payments
ADD COLUMN order_id INT,
ADD CONSTRAINT fk_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE;

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
  `updated_at` datetime DEFAULT NULL
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



CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DELIMITER $$
CREATE TRIGGER `before_order_items_insert` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
    DECLARE current_total DECIMAL(10, 2);
    
   
    SELECT SUM(price * quantity) INTO current_total
    FROM order_items
    WHERE order_id = NEW.order_id;
    
  
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

CREATE TABLE `saved_items` (
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `site_title` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `order_summaries`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_summaries`  AS SELECT `o`.`id` AS `order_id`, `o`.`customer_id` AS `customer_id`, `c`.`first_name` AS `first_name`, `c`.`last_name` AS `last_name`, `c`.`email` AS `email`, `o`.`total_amount` AS `total_amount`, `o`.`status` AS `status`, `o`.`payment_status` AS `payment_status`, `o`.`payment_method` AS `payment_method`, `o`.`created_at` AS `order_date`, count(`oi`.`id`) AS `total_items`, `o`.`shipping_address` AS `shipping_address`, `o`.`city` AS `city`, `o`.`phone` AS `phone` FROM ((`orders` `o` join `customers` `c` on(`o`.`customer_id` = `c`.`customer_id`)) join `order_items` `oi` on(`o`.`id` = `oi`.`order_id`)) GROUP BY `o`.`id` ;


ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `mpesa_payments`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`);


ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);


ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `saved_items`
  ADD PRIMARY KEY (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);


ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;


ALTER TABLE `mpesa_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;


ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;


ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;


ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;


ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);


ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);


ALTER TABLE `saved_items`
  ADD CONSTRAINT `saved_items_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `saved_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;