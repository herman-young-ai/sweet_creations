-- Sweet Creations Database Setup
--

DROP DATABASE IF EXISTS `sweet_creations`;
CREATE DATABASE `sweet_creations` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sweet_creations`;

-- --------------------------------------------------------
-- Table structure for table `USERS`
-- --------------------------------------------------------
CREATE TABLE `USERS` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, /* Storing plain text password */
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Staff', /* 'Admin'/'Staff' */
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `CUSTOMERS`
-- --------------------------------------------------------
CREATE TABLE `CUSTOMERS` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL, /* Format: +230 XXXX XXXX */
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `PRODUCTS`
-- --------------------------------------------------------
CREATE TABLE `PRODUCTS` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `cake_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `custom_available` tinyint(1) NOT NULL DEFAULT '0',
  `size_options` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL, /* e.g., 'Small,Medium,Large' or JSON */
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `ORDERS`
-- --------------------------------------------------------
CREATE TABLE `ORDERS` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL, /* User who placed the order */
  `order_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivery_date` date NOT NULL,
  `delivery_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL, /* HH:MM format */
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `order_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'New', /* New, In Progress, Ready, Delivered, Cancelled */
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `special_requirements` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `CUSTOMERS` (`customer_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `USERS` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `ORDER_ITEMS`
-- --------------------------------------------------------
CREATE TABLE `ORDER_ITEMS` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL, /* Price per item at time of order */
  `size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customization` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ORDERS` (`order_id`) ON DELETE CASCADE, /* Cascade delete items if order is deleted */
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `PRODUCTS` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Initial Data
-- --------------------------------------------------------

-- Inserting default admin user (password: admin123)
INSERT INTO `USERS` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('admin', 'admin123', 'Administrator', 'admin@sweetcreations.com', 'Admin');

-- Inserting sample customers
INSERT INTO `CUSTOMERS` (`full_name`, `phone_number`, `email`, `address`, `notes`) VALUES
('John Doe', '+230 5123 4567', 'john.doe@email.com', '123 Main St, Port Louis', 'Regular customer, likes chocolate.'),
('Jane Smith', '+230 5987 6543', 'jane.smith@email.com', '456 Beach Ave, Grand Baie', NULL),
('Alice Brown', '+230 5444 5555', 'alice.b@server.net', '789 Curepipe Rd, Curepipe', 'Allergic to nuts.');

-- Inserting sample products
INSERT INTO `PRODUCTS` (`cake_name`, `base_price`, `category`, `description`, `custom_available`, `size_options`) VALUES
('Classic Chocolate Cake', 1200.00, 'Chocolate', 'Rich moist chocolate cake with fudge frosting.', 1, 'Small,Medium,Large'),
('Vanilla Bean Dream', 1100.00, 'Vanilla', 'Fluffy vanilla cake with buttercream icing.', 1, 'Medium,Large'),
('Red Velvet Delight', 1350.00, 'Specialty', 'Classic red velvet cake with cream cheese frosting.', 0, 'Standard'),
('Fruit Fantasy Tart', 950.00, 'Tart', 'Shortcrust pastry filled with custard and fresh fruits.', 0, 'One Size');

-- Inserting sample orders
INSERT INTO `ORDERS` (`customer_id`, `user_id`, `delivery_date`, `delivery_address`, `order_status`, `total_amount`, `special_requirements`) VALUES
(1, 1, '2025-04-07', '123 Main St, Port Louis', 'New', 1200.00, 'Happy Birthday John!'),
(2, 1, '2025-04-07', '456 Beach Ave, Grand Baie', 'New', 2700.00, NULL);

-- Inserting sample order items
-- Order 1 (ID will likely be 1)
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(1, 1, 1, 1200.00, 'Medium', 'Add extra chocolate curls');

-- Order 2 (ID will likely be 2)
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(2, 3, 2, 1350.00, 'Standard', NULL);

-- End of script 