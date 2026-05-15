-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 06:55 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bc_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `categoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `categoryName`) VALUES
(1, 'Electronics'),
(2, 'Bag'),
(3, 'Wallet'),
(4, 'ID'),
(5, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `dropoffpoint`
--

CREATE TABLE `dropoffpoint` (
  `dropOff_id` int(11) NOT NULL,
  `dropOffPointName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dropoffpoint`
--

INSERT INTO `dropoffpoint` (`dropOff_id`, `dropOffPointName`) VALUES
(1, 'Sa SM Seaside'),
(2, 'Sa backgate lang ta mag meet'),
(3, 'Skina pardo');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL,
  `locationName` varchar(255) NOT NULL,
  `zone` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`location_id`, `locationName`, `zone`) VALUES
(1, 'GLE', '1st Floor'),
(2, 'GLE', '2nd Floor'),
(3, 'RTL', '1st Floor'),
(4, 'ACAD', '1st Floor');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `dropOff_id` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `publicDescription` varchar(255) DEFAULT NULL,
  `privateDetails` varchar(255) DEFAULT NULL,
  `currentStatus` varchar(20) DEFAULT 'PENDING',
  `dateReported` datetime DEFAULT current_timestamp(),
  `expirationDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `location_id`, `category_id`, `dropOff_id`, `type`, `publicDescription`, `privateDetails`, `currentStatus`, `dateReported`, `expirationDate`) VALUES
(1, 3, 1, 1, 2, 'FOUND', 'kinsay kitag cellphone', 'none', 'Waiting', '2026-05-15 12:02:38', NULL),
(2, 6, 1, 1, 2, 'LOST', 'nawala akong cellphone', 'none', 'Searching', '2026-05-15 12:02:38', NULL),
(3, 7, 2, 3, 2, 'LOST', 'kinsay kakitag charger', 'none', 'Searching', '2026-05-15 12:02:38', NULL),
(4, 5, 3, 3, 2, 'FOUND', 'naay nawad an ug charger sa laptop??', 'none', 'Meeting up', '2026-05-15 12:02:38', NULL),
(5, 4, 4, 4, 3, 'LOST', 'please ko kinsay nakakitag charger sa akong laptop nga dell', 'none', 'Searching', '2026-05-15 12:02:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `institutionalEmail` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `institutionalEmail`, `password`, `is_admin`) VALUES
(1, NULL, 'venzblanz@gmail.com', '$2y$10$JIkLsKPwx25s2CiQiRxn/e8WU94pcjSvsqxl/8P/1bZf98BtPVSou', 0),
(2, NULL, 'junercaimor@cit.edu', '$2y$10$TdcqQ1bfZ2NjQUuOyIYH0u2fU1pEEuLFh5/4AmMKY5H/qms/VT1ja', 0),
(3, 'Venz Virni T. Blanza', 'venzblanza@gmail.com', '$2y$10$T1D2dK.xroaXURGv.8FVjeKWBIKR9oEAzq8ZoVN1zGNvWo3E1WSjS', 0),
(4, 'Juner John V. Caimor', 'juner@cit.edu', '$2y$10$dH6fX4Y162mZ2dClMmbNWOPEQJFm7Qq.cfT3Q.855gLiQNyph/3H6', 0),
(5, 'Sage Luther Cui', 'sageluther.cui@gmail.com', 'temporaryPass', 0),
(6, 'Michael Steven Causing', 'michaelsteven.causing@gmail.com', 'temporaryPass', 0),
(7, 'Hannah Jane Javier', 'hannahjane.javier@gmail.com', 'temporaryPass', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `dropoffpoint`
--
ALTER TABLE `dropoffpoint`
  ADD PRIMARY KEY (`dropOff_id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_location_id` (`location_id`),
  ADD KEY `fk_category_id` (`category_id`),
  ADD KEY `fk_dropOff_id` (`dropOff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `i_email` (`institutionalEmail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dropoffpoint`
--
ALTER TABLE `dropoffpoint`
  MODIFY `dropOff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  ADD CONSTRAINT `fk_dropOff_id` FOREIGN KEY (`dropOff_id`) REFERENCES `dropoffpoint` (`dropOff_id`),
  ADD CONSTRAINT `fk_location_id` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
