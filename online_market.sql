-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 28, 2016 at 12:26 PM
-- Server version: 5.7.15
-- PHP Version: 5.6.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_market`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `addOrder` (IN `userID` INT)  BEGIN
DECLARE orderID INT;
DECLARE totalCost INT DEFAULT 0;
DECLARE delivermanID INT DEFAULT 0;
DECLARE deliverymenCount INT DEFAULT 0;
DECLARE n INT DEFAULT 0;
DECLARE i INT DEFAULT 0;

Select SUM(quantity * price) into totalCost FROM products, cart_items WHERE products._id = cart_items.product_id AND cart_items.user_id = userID LIMIT 1;

INSERT INTO orders (buyer_id, cost) values ( userID, totalCost);
SET orderID =  LAST_INSERT_ID();

SELECT COUNT(*) FROM cart_items WHERE user_id = userID INTO n;

SET i=0;
WHILE i<n DO 
  INSERT INTO order_items (order_id, product_id, quantity, producttotalcost ) SELECT orderID as 'order_id', ct.product_id, ct.quantity, ct.producttotalprice FROM cart_items ct WHERE user_id = userID  ORDER BY _id DESC LIMIT 1 OFFSET i;
  SET i = i + 1;
END WHILE;

DELETE FROM cart_items WHERE user_id = userID;

SELECT deliveryman_id into delivermanID FROM deliveryrequests GROUP BY deliveryman_id ORDER BY Count(*) ASC LIMIT 1;

SELECT Count(DISTINCT(deliveryman_id)) INTO deliverymenCount FROM deliveryrequests;
IF(deliverymenCount = 1) THEN
	SELECT user_id into delivermanID from deliverymen WHERE user_id != delivermanID LIMIT 1;
END IF;
IF (delivermanID = 0) THEN
   SELECT user_id into delivermanID from deliverymen LIMIT 1;
END IF;
  
INSERT INTO deliveryrequests SET order_id = orderID, deliveryman_id = delivermanID, duedate = (SELECT CURDATE() + INTERVAL 3 DAY);
select (orderID);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteDeliveryMan` (IN `userID` INT)  BEGIN
DECLARE delivermanID INT DEFAULT 0;
DECLARE deliverymenCount INT DEFAULT 0;

SELECT deliveryman_id into delivermanID FROM deliveryrequests WHERE deliveryman_id != userID GROUP BY deliveryman_id ORDER BY Count(*) ASC LIMIT 1;

SELECT Count(DISTINCT(deliveryman_id)) INTO deliverymenCount FROM deliveryrequests WHERE deliveryman_id != userID;
IF(deliverymenCount = 1) THEN
	SELECT user_id into delivermanID from deliverymen WHERE user_id != delivermanID AND user_id != userID LIMIT 1;
END IF;
IF (delivermanID = 0) THEN
    SELECT user_id into delivermanID from deliverymen WHERE user_id != userID LIMIT 1;
END IF;
UPDATE deliveryrequests SET deliveryman_id = delivermanID WHERE deliveryman_id = userID;
DELETE FROM `users` WHERE `_id` = userID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getUserData` (IN `user_id` INT)  BEGIN
DECLARE userTypeID INT;
DECLARE userTypeTableName TEXT;
    SELECT user_type into userTypeID FROM users WHERE users._id = user_id LIMIT 1;
	SELECT name into userTypeTableName FROM user_type WHERE user_type.type_id = userTypeID LIMIT 1;
	SET @sql_text = concat('select user.*, sp_user.* from users user JOIN ',userTypeTableName,' sp_user ON user._id = sp_user.user_id WHERE user._id = ', user_id);
	PREPARE stmt FROM @sql_text;
	EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `accountants`
--

CREATE TABLE `accountants` (
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `accountants`
--

INSERT INTO `accountants` (`user_id`) VALUES
(75),
(118);

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`user_id`) VALUES
(76);

-- --------------------------------------------------------

--
-- Table structure for table `availability_status`
--

CREATE TABLE `availability_status` (
  `_id` int(11) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `availability_status`
--

INSERT INTO `availability_status` (`_id`, `status`) VALUES
(1, 'available'),
(2, 'unavailable'),
(3, 'deleted');

-- --------------------------------------------------------

--
-- Table structure for table `buyers`
--

CREATE TABLE `buyers` (
  `user_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `creditcard` varchar(25) NOT NULL,
  `cc_ccv` int(3) NOT NULL,
  `cc_month` int(2) NOT NULL,
  `cc_year` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `buyers`
--

INSERT INTO `buyers` (`user_id`, `address`, `creditcard`, `cc_ccv`, `cc_month`, `cc_year`) VALUES
(72, 'b Address22bbbb123', '4444444444423423', 312, 12, 2017),
(82, '6B, Pyramids Gardens', '763421389103', 866, 10, 2019),
(109, 'asd', '123', 312, 21, 1231),
(116, 'Abdo addressooo', '12345678900', 120, 1, 2019),
(123, '123', '1234567891234567', 678, 12, 2017);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `producttotalprice` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`_id`, `name`) VALUES
(25, 'asddd'),
(27, 'Books'),
(1, 'C1'),
(3, 'Cate1'),
(28, 'Computer Parts'),
(24, 'fff'),
(26, 'gggg123'),
(23, 'Mobile Phone'),
(29, 'Monitors'),
(22, 'vvvvvv1');

-- --------------------------------------------------------

--
-- Table structure for table `categories_spec`
--

CREATE TABLE `categories_spec` (
  `_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `categories_spec`
--

INSERT INTO `categories_spec` (`_id`, `category_id`, `name`) VALUES
(6, 1, 'Spec3'),
(10, 1, 'Spec5'),
(11, 1, 'Spec6'),
(12, 1, 'Spec9'),
(17, 1, 'V'),
(7, 3, 'Spec4'),
(13, 22, 'Spec123'),
(14, 22, 'Spec456'),
(16, 26, 'Spec123'),
(19, 27, 'NoOfPages'),
(18, 29, 'LCD AND LED');

-- --------------------------------------------------------

--
-- Table structure for table `deliverymen`
--

CREATE TABLE `deliverymen` (
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `deliverymen`
--

INSERT INTO `deliverymen` (`user_id`) VALUES
(77),
(113);

-- --------------------------------------------------------

--
-- Table structure for table `deliveryrequests`
--

CREATE TABLE `deliveryrequests` (
  `_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `deliveryman_id` int(11) NOT NULL,
  `duedate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `cost` int(11) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT '1',
  `issuedate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`_id`, `buyer_id`, `cost`, `status_id`, `issuedate`) VALUES
(115, 123, 113, 4, '2016-12-21 17:22:26'),
(116, 72, 113, 4, '2016-12-21 17:26:57'),
(117, 72, 2139, 3, '2016-12-22 11:41:27'),
(118, 72, 299, 5, '2016-12-22 15:34:06'),
(119, 72, 1837, 4, '2016-12-22 15:36:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `producttotalcost` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `producttotalcost`) VALUES
(117, 26, 4, 1800),
(117, 27, 3, 339),
(118, 21, 1, 299),
(119, 21, 2, 598),
(119, 26, 2, 900),
(119, 27, 3, 339);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `_id` int(11) NOT NULL,
  `status` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`_id`, `status`) VALUES
(5, 'deleted'),
(4, 'delivered'),
(1, 'pending'),
(2, 'picked'),
(3, 'shipped');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `size` varchar(20) NOT NULL,
  `weight` decimal(10,0) NOT NULL,
  `availability_id` int(1) NOT NULL DEFAULT '1',
  `available_quantity` int(10) NOT NULL,
  `origin` varchar(25) NOT NULL,
  `provider` varchar(25) NOT NULL,
  `image` text NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `solditems` int(11) NOT NULL DEFAULT '0',
  `earnings` int(11) NOT NULL DEFAULT '0',
  `description` longtext NOT NULL,
  `rate` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`_id`, `name`, `price`, `size`, `weight`, `availability_id`, `available_quantity`, `origin`, `provider`, `image`, `seller_id`, `category_id`, `solditems`, `earnings`, `description`, `rate`) VALUES
(21, 'Note 4123', '299', '5*10*20123', '180123', 3, 300122, 'Sam123', 'Obikan123', 'http://cdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-note-3-1.jpg', 73, 25, 64, 299721, 'description123', 0.5),
(26, 'Xperia Z', '450', '12x13x15', '235', 1, 182, 'China', 'Sony', 'https://api.sonymobile.com/files/xperia-hero-z-black-1240x840-f535888737995291dfe31cae40a6d99f.jpg', 73, 23, 7, 3150, 'Manufacture Year: 2014', 0),
(27, 'Introduction to OpenGl', '113', '12x12x15', '200', 1, 11212, 'Jamaica', 'Jamaica Pub.', 'https://www.opengl.org/img/opengl_logo.png', 73, 27, 10, 1130, 'I am Dr.Bassem, I have no idea about Graphics', 1.75);

-- --------------------------------------------------------

--
-- Stand-in structure for view `products_view`
-- (See below for the actual view)
--
CREATE TABLE `products_view` (
`_id` int(11)
,`name` varchar(50)
,`price` decimal(10,0)
,`size` varchar(20)
,`weight` decimal(10,0)
,`availability_id` int(1)
,`available_quantity` int(10)
,`origin` varchar(25)
,`provider` varchar(25)
,`image` text
,`seller_id` int(11)
,`category_id` int(11)
,`solditems` int(11)
,`earnings` int(11)
,`description` longtext
,`rate` float
,`PSID` int(11)
,`CSNAME` varchar(50)
,`PSVALUE` text
,`seller_name` varchar(50)
,`category_name` varchar(50)
,`availability_status` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `product_spec`
--

CREATE TABLE `product_spec` (
  `_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `categories_spec_id` int(11) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rate` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `rates`
--

INSERT INTO `rates` (`_id`, `user_id`, `product_id`, `rate`) VALUES
(53, 123, 27, 1.5),
(58, 72, 21, 0),
(59, 72, 26, 0),
(60, 72, 27, 1.5);

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `user_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `bankaccount` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`user_id`, `address`, `bankaccount`) VALUES
(73, 's Adderss Seller Address123', 'sssBankAccountasdasdfasdfsdafsadf'),
(117, 'iar addressiar', 'iar bank acount iar2');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pass` text NOT NULL,
  `tel` varchar(25) NOT NULL,
  `user_type` int(11) NOT NULL,
  `user_status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`_id`, `name`, `email`, `pass`, `tel`, `user_type`, `user_status`) VALUES
(72, 'bbbbbssssa', 'b@b.b', '$2y$11$mPmONxYIB9zhzyGDwNhsUelgremhZ7eHwTHTabjFChGtMQW4tcdMq', '22222222', 1, 1),
(73, 'sssADSSS123', 's@s.s', '$2y$11$zKBsU9BxoYJKQOc3BfMUuezpfIwPtOxdArIY3FRFmrURTx6kdFALq', '33333333123', 2, 2),
(75, 'aaa', 'a@a.a', '$2y$11$PlVXywCfqAVuC5ihTyMRJexkcyW8sokldF191GX.1URPBHvHIbYA6', '111', 3, 1),
(76, 'ddd234', 'd@d.d', '$2y$11$9SeeU0gj9fbZ.8K6nXFCSuDxp/yYccrFIauevtmw19q/Si4dEcnPK', '44444', 4, 1),
(77, 'mmm233', 'm@m.m', '$2y$11$GkVg9lwTAQpDBar4OhmoTOfbt73fBmZV4hjC64EENLRr1gf9f4Xki', '33213124123123aa', 5, 1),
(82, 'Ibrahim Radwn', 'i.radwan1996@gmail.com', '$2y$11$tCijn/Sl2p9QSeuZ6/QojOIdlfpa3xi4bnlfm4GhkP4NgN3fAN2Wq', '00201097799856', 1, 1),
(109, 'Emp', 'sfd@asd.asd', '$2y$11$Gw6A6FvNJr/2tYic3LOOHuny6OPkk53sIRxXeeTNQwL/ZuPbaMSDy', '0', 1, 1),
(113, 'Emp', 'sfkdjghskjg@asd.asd', '$2y$11$eIZQwDobHb1zHLWjwIQl9.XAs2y4EUoUxUXG2P2eDJnbv00h02a0K', '0', 5, 1),
(116, 'abdoioooo', 'abdo@abdo.abdo', '$2y$11$5CjJDbEe7b71LbMQFCWCTeJMUAajVvOWEZdYHYwTkiD0ABVLek7eK', '0020190000000000', 1, 1),
(117, 'iarrrr', 'iar@iar.iar', '$2y$11$LcWs8fpozmF.H0OWEvEpH.jKuObj1A64e3FpSns8nTLjrhDvZk2/2', '00201090000000', 2, 1),
(118, 'Emp', 'C@a.a', '$2y$11$CEMDpJljf9blOWtettUjKuH8INhOdQRs.LIM3MSIuT/n0DHBCWcUW', '0', 3, 1),
(123, 'Samra', 'b2@b2.b2', '$2y$11$/b8PRjoqkrHwmjtxwML4NeH8lFhaGDFSlBzXNFYvYQEMHzN9jIyz.', '123', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`status_id`, `status_name`) VALUES
(1, 'active'),
(2, 'banned');

-- --------------------------------------------------------

--
-- Table structure for table `user_type`
--

CREATE TABLE `user_type` (
  `type_id` int(11) NOT NULL,
  `name` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `user_type`
--

INSERT INTO `user_type` (`type_id`, `name`) VALUES
(3, 'accountants'),
(4, 'admins'),
(1, 'buyers'),
(5, 'deliverymen'),
(2, 'sellers');

-- --------------------------------------------------------

--
-- Structure for view `products_view`
--
DROP TABLE IF EXISTS `products_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `products_view`  AS  select `p`.`_id` AS `_id`,`p`.`name` AS `name`,`p`.`price` AS `price`,`p`.`size` AS `size`,`p`.`weight` AS `weight`,`p`.`availability_id` AS `availability_id`,`p`.`available_quantity` AS `available_quantity`,`p`.`origin` AS `origin`,`p`.`provider` AS `provider`,`p`.`image` AS `image`,`p`.`seller_id` AS `seller_id`,`p`.`category_id` AS `category_id`,`p`.`solditems` AS `solditems`,`p`.`earnings` AS `earnings`,`p`.`description` AS `description`,`p`.`rate` AS `rate`,`ps`.`_id` AS `PSID`,`cs`.`name` AS `CSNAME`,`ps`.`value` AS `PSVALUE`,`u`.`name` AS `seller_name`,`c`.`name` AS `category_name`,`a`.`status` AS `availability_status` from (((((`products` `p` left join `product_spec` `ps` on((`ps`.`product_id` = `p`.`_id`))) left join `categories_spec` `cs` on(((`cs`.`category_id` = `p`.`category_id`) and (`ps`.`categories_spec_id` = `cs`.`_id`)))) join `users` `u` on((`u`.`_id` = `p`.`seller_id`))) join `categories` `c` on((`c`.`_id` = `p`.`category_id`))) join `availability_status` `a` on((`a`.`_id` = `p`.`availability_id`))) order by `p`.`_id` desc ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accountants`
--
ALTER TABLE `accountants`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `availability_status`
--
ALTER TABLE `availability_status`
  ADD PRIMARY KEY (`_id`);

--
-- Indexes for table `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`_id`),
  ADD UNIQUE KEY `user_id_2` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `categories_spec`
--
ALTER TABLE `categories_spec`
  ADD PRIMARY KEY (`_id`,`category_id`,`name`),
  ADD UNIQUE KEY `category_id_2` (`category_id`,`name`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `deliverymen`
--
ALTER TABLE `deliverymen`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deliveryrequests`
--
ALTER TABLE `deliveryrequests`
  ADD PRIMARY KEY (`_id`,`order_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `_deliverymanid` (`deliveryman_id`),
  ADD KEY `order id binding` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`_id`),
  ADD KEY `_buyerid` (`buyer_id`),
  ADD KEY `_statusid` (`status_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_id`,`product_id`),
  ADD KEY `order_id` (`order_id`,`product_id`),
  ADD KEY `bind product_id` (`product_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`_id`),
  ADD UNIQUE KEY `status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`_id`),
  ADD UNIQUE KEY `_id` (`_id`),
  ADD KEY `availability_id` (`availability_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `seller product binding` (`seller_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `product_spec`
--
ALTER TABLE `product_spec`
  ADD PRIMARY KEY (`_id`,`product_id`,`categories_spec_id`),
  ADD UNIQUE KEY `product_id_2` (`product_id`,`categories_spec_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `cate-spec_id` (`categories_spec_id`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `buyer_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id_2` (`user_id`),
  ADD KEY `_adminid` (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`_id`),
  ADD KEY `user_type` (`user_type`),
  ADD KEY `user_status` (`user_status`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `user_type`
--
ALTER TABLE `user_type`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `availability_status`
--
ALTER TABLE `availability_status`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `categories_spec`
--
ALTER TABLE `categories_spec`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `deliveryrequests`
--
ALTER TABLE `deliveryrequests`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;
--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `product_spec`
--
ALTER TABLE `product_spec`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rates`
--
ALTER TABLE `rates`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;
--
-- AUTO_INCREMENT for table `user_status`
--
ALTER TABLE `user_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user_type`
--
ALTER TABLE `user_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `accountants`
--
ALTER TABLE `accountants`
  ADD CONSTRAINT `Bind accountant id with user id` FOREIGN KEY (`user_id`) REFERENCES `users` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `Bind admin id with user id` FOREIGN KEY (`user_id`) REFERENCES `users` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `buyers`
--
ALTER TABLE `buyers`
  ADD CONSTRAINT `Bind user id` FOREIGN KEY (`user_id`) REFERENCES `users` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `Bind cart item to user id` FOREIGN KEY (`user_id`) REFERENCES `buyers` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Bind product id to products` FOREIGN KEY (`product_id`) REFERENCES `products` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories_spec`
--
ALTER TABLE `categories_spec`
  ADD CONSTRAINT `Binding categories_specs with categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`_id`);

--
-- Constraints for table `deliverymen`
--
ALTER TABLE `deliverymen`
  ADD CONSTRAINT `Bind user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `deliveryrequests`
--
ALTER TABLE `deliveryrequests`
  ADD CONSTRAINT `Bind order id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery man foreign key` FOREIGN KEY (`deliveryman_id`) REFERENCES `deliverymen` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `buyer binding` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`user_id`),
  ADD CONSTRAINT `order status binding` FOREIGN KEY (`status_id`) REFERENCES `order_status` (`_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `bind order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bind product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `availability status binding` FOREIGN KEY (`availability_id`) REFERENCES `availability_status` (`_id`),
  ADD CONSTRAINT `categories product binding` FOREIGN KEY (`category_id`) REFERENCES `categories` (`_id`),
  ADD CONSTRAINT `seller id ` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_spec`
--
ALTER TABLE `product_spec`
  ADD CONSTRAINT `Binding  product_specs with cate_specs` FOREIGN KEY (`categories_spec_id`) REFERENCES `categories_spec` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Binding  product_specs with product` FOREIGN KEY (`product_id`) REFERENCES `products` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rates`
--
ALTER TABLE `rates`
  ADD CONSTRAINT `Binding product id` FOREIGN KEY (`product_id`) REFERENCES `products` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Binding user_id` FOREIGN KEY (`user_id`) REFERENCES `buyers` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sellers`
--
ALTER TABLE `sellers`
  ADD CONSTRAINT `Binding user id ` FOREIGN KEY (`user_id`) REFERENCES `users` (`_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `bind user_status` FOREIGN KEY (`user_status`) REFERENCES `user_status` (`status_id`),
  ADD CONSTRAINT `bind user_type` FOREIGN KEY (`user_type`) REFERENCES `user_type` (`type_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
