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

-- Inserting business owner (admin) and 5 staff members
INSERT INTO `USERS` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('rashni.devi', 'admin123', 'Rashni Devi', 'rashni.devi@sweetcreations.com', 'Admin'),
('amit.sharma', 'staff123', 'Amit Sharma', 'amit.sharma@sweetcreations.com', 'Staff'),
('nisha.patel', 'staff123', 'Nisha Patel', 'nisha.patel@sweetcreations.com', 'Staff'),
('kevin.wong', 'staff123', 'Kevin Wong Ah Sui', 'kevin.wong@sweetcreations.com', 'Staff'),
('anita.gopal', 'staff123', 'Anita Gopal', 'anita.gopal@sweetcreations.com', 'Staff'),
('yusuf.kader', 'staff123', 'Yusuf Kader', 'yusuf.kader@sweetcreations.com', 'Staff');

-- Inserting 15 customers with authentic Mauritian names
INSERT INTO `CUSTOMERS` (`full_name`, `phone_number`, `email`, `address`, `notes`) VALUES
('Ravin Ramgoolam', '+230 5123 4567', 'ravin.ramgoolam@gmail.com', '12 Royal Road, Port Louis', 'Regular customer, prefers vanilla cakes.'),
('Priya Seetaram', '+230 5987 6543', 'priya.seetaram@yahoo.com', '45 Beach Avenue, Grand Baie', 'Frequent orders for family celebrations.'),
('Arjun Boolell', '+230 5444 5555', 'arjun.boolell@outlook.com', '78 Curepipe Road, Curepipe', 'Allergic to nuts - always specify.'),
('Kavita Jankee', '+230 5234 7890', 'kavita.jankee@hotmail.com', '23 SSR Street, Quatre Bornes', 'Loves chocolate decorations.'),
('Deepak Ramdin', '+230 5678 9012', 'deepak.ramdin@gmail.com', '56 Independence Street, Rose Hill', 'Usually orders for office events.'),
('Shanti Bhojoo', '+230 5345 6789', 'shanti.bhojoo@gmail.com', '89 La Louise, Vacoas', 'Prefers traditional designs.'),
('Raj Ramessur', '+230 5456 7890', 'raj.ramessur@yahoo.com', '34 Trunk Road, Mahebourg', 'Regular customer since 2020.'),
('Meera Dhuny', '+230 5567 8901', 'meera.dhuny@outlook.com', '67 Camp Levieux Street, Port Louis', 'Special dietary requirements - sugar-free options.'),
('Vikash Gokool', '+230 5678 9012', 'vikash.gokool@gmail.com', '12 St Jean Road, Quatre Bornes', 'Large family orders for festivals.'),
('Sunita Ramlall', '+230 5789 0123', 'sunita.ramlall@hotmail.com', '45 Pope Hennessy Street, Port Louis', 'Prefers fruit-based cakes.'),
('Ashwin Soobrah', '+230 5890 1234', 'ashwin.soobrah@gmail.com', '78 Royal Road, Beau Bassin', 'Wedding cake specialist client.'),
('Kavitha Ramdhony', '+230 5901 2345', 'kavitha.ramdhony@yahoo.com', '23 Boundary Road, Vacoas', 'Regular birthday cake orders.'),
('Devesh Seegoolam', '+230 5012 3456', 'devesh.seegoolam@outlook.com', '56 Main Road, Goodlands', 'Prefers red velvet and chocolate combinations.'),
('Reshma Jhugroo', '+230 5123 4567', 'reshma.jhugroo@gmail.com', '89 Cemetery Road, Pamplemousses', 'Always orders in advance for special occasions.'),
('Anil Pursun', '+230 5234 5678', 'anil.pursun@hotmail.com', '34 Flacq Road, Centre de Flacq', 'Enjoys custom-designed celebration cakes.');

-- Inserting 15 cake products suitable for a Mauritian cake shop
INSERT INTO `PRODUCTS` (`cake_name`, `base_price`, `category`, `description`, `custom_available`, `size_options`) VALUES
('Classic Chocolate Cake', 1200.00, 'Chocolate', 'Rich moist chocolate cake with fudge frosting.', 1, 'Small,Medium,Large'),
('Vanilla Bean Dream', 1100.00, 'Vanilla', 'Fluffy vanilla cake with buttercream icing.', 1, 'Small,Medium,Large'),
('Red Velvet Delight', 1350.00, 'Specialty', 'Classic red velvet cake with cream cheese frosting.', 1, 'Small,Medium,Large'),
('Fruit Fantasy Tart', 950.00, 'Tart', 'Shortcrust pastry filled with custard and fresh fruits.', 1, 'Small,Medium,Large'),
('Black Forest Gateau', 1450.00, 'Chocolate', 'Chocolate sponge with cherries and whipped cream.', 1, 'Small,Medium,Large'),
('Lemon Drizzle Cake', 1050.00, 'Citrus', 'Moist lemon cake with tangy lemon glaze.', 1, 'Small,Medium,Large'),
('Carrot Cake Supreme', 1250.00, 'Specialty', 'Spiced carrot cake with cream cheese frosting and walnuts.', 1, 'Small,Medium,Large'),
('Coconut Paradise', 1150.00, 'Tropical', 'Coconut sponge cake with coconut cream and toasted coconut.', 1, 'Small,Medium,Large'),
('Strawberry Shortcake', 1300.00, 'Fruit', 'Light sponge with fresh strawberries and whipped cream.', 1, 'Small,Medium,Large'),
('Banana Bread Cake', 1000.00, 'Fruit', 'Moist banana cake with cinnamon and brown butter frosting.', 1, 'Small,Medium,Large'),
('Coffee Mocha Cake', 1400.00, 'Coffee', 'Espresso-flavored cake with chocolate mocha buttercream.', 1, 'Small,Medium,Large'),
('Pineapple Upside Down', 1200.00, 'Tropical', 'Traditional upside-down cake with caramelized pineapple.', 1, 'Small,Medium,Large'),
('Cheese Cake Classic', 1500.00, 'Cheesecake', 'Rich New York style cheesecake with berry compote.', 1, 'Small,Medium,Large'),
('Chocolate Mud Cake', 1350.00, 'Chocolate', 'Dense chocolate cake with rich chocolate ganache.', 1, 'Small,Medium,Large'),
('Mango Mousse Cake', 1250.00, 'Tropical', 'Light mango mousse with sponge base - perfect for Mauritius!', 1, 'Small,Medium,Large');

-- Inserting sample orders with different staff members creating them
INSERT INTO `ORDERS` (`customer_id`, `user_id`, `delivery_date`, `delivery_address`, `order_status`, `total_amount`, `is_paid`, `special_requirements`) VALUES
(1, 1, '2025-07-21', '12 Royal Road, Port Louis', 'New', 1200.00, 0, 'Happy Birthday Ravin!'),
(2, 2, '2025-07-20', '45 Beach Avenue, Grand Baie', 'New', 2700.00, 0, 'Family celebration cake'),
(3, 3, '2025-04-08', '78 Curepipe Road, Curepipe', 'New', 1100.00, 0, 'No nuts - customer has allergies'),
(4, 4, '2025-07-21', '23 SSR Street, Quatre Bornes', 'In Progress', 1350.00, 0, 'Chocolate decorations requested'),
(5, 5, '2025-04-10', '89 La Louise, Vacoas', 'In Progress', 1250.00, 0, 'Traditional design for grandmother'),
(6, 6, '2025-07-20', '56 Independence Street, Rose Hill', 'Ready', 1450.00, 0, 'Office celebration cake'),
(7, 2, '2025-07-20', '34 Trunk Road, Mahebourg', 'Ready', 1050.00, 0, 'Simple lemon cake for tea party'),
(8, 3, '2025-03-15', '67 Camp Levieux Street, Port Louis', 'Delivered', 1300.00, 1, 'Strawberry cake for anniversary'),
(9, 1, '2025-03-20', '12 Royal Road, Port Louis', 'Delivered', 1500.00, 1, 'Wedding anniversary celebration'),
(10, 4, '2025-03-25', '23 SSR Street, Quatre Bornes', 'Delivered', 1000.00, 1, 'Birthday cake for child'),
(11, 6, '2025-02-14', '89 Cemetery Road, Pamplemousses', 'Delivered', 1400.00, 1, 'Valentine special cake'),
(12, 5, '2025-01-30', '56 Independence Street, Rose Hill', 'Delivered', 1150.00, 1, 'Chinese New Year celebration'),
(13, 1, '2025-04-05', '12 Royal Road, Port Louis', 'Cancelled', 1200.00, 0, 'Customer changed mind'),
(14, 1, '2025-04-06', '78 Royal Road, Beau Bassin', 'Cancelled', 1350.00, 0, 'Event postponed');

-- Inserting sample order items
-- Order 1 (Ravin Ramgoolam - Vanilla) - Created by Rashni (Admin)
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(1, 2, 1, 1200.00, 'Medium', 'Happy Birthday Ravin in blue icing');

-- Order 2 (Priya Seetaram - Red Velvet) - Created by Amit
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(2, 3, 2, 1350.00, 'Standard', 'Family celebration theme');

-- Order 3 (Arjun Boolell - Vanilla, no nuts) - Created by Nisha
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(3, 2, 1, 1100.00, 'Medium', 'Vanilla only - no nuts anywhere');

-- Order 4 (Kavita Jankee - Chocolate Mud Cake) - Created by Kevin
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(4, 14, 1, 1350.00, 'Medium', 'Extra chocolate decorations');

-- Order 5 (Shanti Bhojoo - Coconut Paradise) - Created by Anita
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(5, 8, 1, 1250.00, 'Medium', 'Traditional Mauritian style decoration');

-- Order 6 (Deepak Ramdin - Black Forest) - Created by Yusuf
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(6, 5, 1, 1450.00, 'Medium', 'Office celebration - formal design');

-- Order 7 (Raj Ramessur - Lemon Drizzle) - Created by Amit
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(7, 6, 1, 1050.00, 'Small', 'Simple decoration for tea party');

-- Order 8 (Meera Dhuny - Strawberry Shortcake) - Delivered
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(8, 9, 1, 1300.00, 'Medium', 'Anniversary design with sugar flowers');

-- Order 9 (Ravin Ramgoolam - Cheese Cake) - Delivered
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(9, 13, 1, 1500.00, 'Large', 'Elegant wedding anniversary design');

-- Order 10 (Kavita Jankee - Banana Bread Cake) - Delivered
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(10, 10, 1, 1000.00, 'Small', 'Fun cartoon characters for child');

-- Order 11 (Reshma Jhugroo - Coffee Mocha Cake) - Delivered
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(11, 11, 1, 1400.00, 'Medium', 'Valentine hearts and roses design');

-- Order 12 (Deepak Ramdin - Coconut Paradise) - Delivered
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(12, 8, 1, 1150.00, 'Medium', 'Chinese New Year gold decorations');

-- Order 13 (Ravin Ramgoolam - Vanilla) - Cancelled
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(13, 2, 1, 1200.00, 'Medium', 'Simple birthday design');

-- Order 14 (Ashwin Soobrah - Red Velvet) - Cancelled
INSERT INTO `ORDER_ITEMS` (`order_id`, `product_id`, `quantity`, `price`, `size`, `customization`) VALUES
(14, 3, 1, 1350.00, 'Medium', 'Elegant event design');

-- End of script 