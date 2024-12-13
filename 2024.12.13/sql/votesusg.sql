-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2024 at 09:11 AM
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
-- Database: `votesusg`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(200) NOT NULL,
  `candidate_name` varchar(50) NOT NULL,
  `college_id` int(20) NOT NULL,
  `position_id` int(3) NOT NULL,
  `qualified` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `candidate_image` varchar(255) DEFAULT NULL,
  `candidate_party` varchar(50) NOT NULL DEFAULT 'Independent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidate_id`, `candidate_name`, `college_id`, `position_id`, `qualified`, `remarks`, `candidate_image`, `candidate_party`) VALUES
(0, 'Abstain', 0, 1, 0, NULL, 'candidate_images/abstain.png', ''),
(30, 'Westen Dasig', 1, 1, 1, '', 'candidate_images/e5856f869da279842602d67aa7226912.png', 'CAUSE'),
(31, 'George Russell', 6, 1, 1, '', 'candidate_images/c8e03540375b1029edaa12a7be22b75e.jpg', 'Mercedes'),
(32, 'Max Verstappen', 3, 2, 1, '', 'candidate_images/b0b2275ec0af41d01d8f9992a539be03.jpg', 'Redbull'),
(33, 'Lewis Hamilton', 7, 2, 1, '', 'candidate_images/cce6f1466f9bdf5921fa249bfba3a627.jpg', 'Mercedes'),
(34, 'Carlos Sainz', 8, 3, 1, '', 'candidate_images/f942d3bdcd52b9fe6ab24b846e86bb14.jpg', 'Ferrari'),
(35, 'John Vergara', 1, 3, 1, '', 'candidate_images/d6e360dd346bb2b3ae9cffebcd316f0b.jpg', 'Ambot');

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `college_id` int(20) NOT NULL,
  `college_name` varchar(50) NOT NULL,
  `max_representatives` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`college_id`, `college_name`, `max_representatives`) VALUES
(0, 'Abstain', 0),
(1, 'CCS', 2),
(2, 'AGRI', 2),
(3, 'CAS', 7),
(4, 'CBA', 6),
(5, 'EDUC', 2),
(6, 'CED', 5),
(7, 'LAW', 2),
(8, 'CMC', 2),
(9, 'CON', 2),
(10, 'COPVA', 2),
(11, 'ICLS', 3),
(12, 'IEMS', 2),
(13, 'IRS', 2),
(14, 'JHS', 5),
(15, 'MEDICAL SCHOOL', 2),
(16, 'SPAG', 2),
(17, 'SHS', 8);

-- --------------------------------------------------------

--
-- Table structure for table `comelec`
--

CREATE TABLE `comelec` (
  `comelec_id` int(11) NOT NULL,
  `comelec_name` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comelec`
--

INSERT INTO `comelec` (`comelec_id`, `comelec_name`, `password`) VALUES
(1, 'comelec2025', 'westengwapo'),
(2, 'hello', 'hello');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `election_id` int(50) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('Scheduled','Ongoing','Completed','') NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `election_name` varchar(100) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`election_id`, `start_datetime`, `end_datetime`, `status`, `created_at`, `updated_at`, `election_name`, `is_current`) VALUES
(17, '2024-11-12 21:25:00', '2024-11-30 21:25:00', 'Completed', '2024-11-30 00:29:05', '2024-11-30 00:29:05', 'Election 1', 1),
(19, '2024-12-17 11:09:00', '2024-12-18 11:10:00', 'Scheduled', '2024-12-12 20:38:08', '2024-12-13 03:49:38', '                                        Election 2                                    ', 0),
(20, '2024-12-13 10:00:00', '2024-12-14 10:00:00', 'Ongoing', '2024-12-12 20:53:54', '2024-12-13 08:07:37', 'Election 3', 0);

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `feedback_id` int(11) NOT NULL,
  `student_id` varchar(11) NOT NULL,
  `experience` int(5) NOT NULL,
  `suggestion` varchar(300) NOT NULL,
  `feedback_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`feedback_id`, `student_id`, `experience`, `suggestion`, `feedback_timestamp`) VALUES
(25, '21-1-01417', 5, 'The system is fine, it works good!', '2024-12-09 22:25:13'),
(26, '21-1-01417', 1, 'System is down hell bad!', '2024-12-09 22:25:27'),
(27, '21-1-01417', 4, 'System is nice, you can easily access the important section of the site.', '2024-12-09 22:25:52'),
(28, '21-1-01417', 3, 'HELLO', '2024-12-09 22:33:36'),
(29, '21-1-01417', 5, 'System is very good!', '2024-12-09 22:38:32'),
(30, '21-1-01417', 1, 'The system is very fast, responsive and very good. I recommend this to my classmates. the development team really did a good job', '2024-12-09 22:58:52'),
(31, '21-1-01417', 5, 'The system is slow! who even created this thing it is atrocious!', '2024-12-09 23:07:42'),
(34, '21-1-01417', 5, 'The system is just so good, it serves really well to the point that I think this beats the national elections system.', '2024-12-12 20:23:46'),
(35, '21-1-01417', 5, 'SO GOOD! THIS IS THE BEST EVER', '2024-12-12 20:24:20'),
(37, '21-1-01417', 2, 'Nearly bad, unusable at first.', '2024-12-13 02:26:20'),
(38, '21-1-01417', 4, 'The system looks so nice, it\'s very modern and stylish.', '2024-12-13 05:51:55'),
(40, '21-1-01417', 1, 'The system is very good, it works so nice, the best!', '2024-12-13 07:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(3) NOT NULL,
  `position_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_name`) VALUES
(1, 'President'),
(2, 'Vice President'),
(3, 'Representative');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(50) NOT NULL,
  `college_id` int(20) NOT NULL,
  `password` varchar(50) NOT NULL,
  `has_voted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `college_id`, `password`, `has_voted`) VALUES
('21-0-01223', 'Jojo', 3, '$2y$10$JXYKhtT9sAiIIIh7VHw2du/cWZO/Zxp5ZJuT6bxwd4Y', 0),
('21-1-01417', 'John Westen Rey Dasig', 1, 'westengwapo', 1);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(11) NOT NULL,
  `student_id` varchar(11) NOT NULL,
  `candidate_id` int(100) NOT NULL,
  `position_id` int(3) NOT NULL,
  `vote_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`vote_id`, `student_id`, `candidate_id`, `position_id`, `vote_timestamp`) VALUES
(79, '21-1-01417', 31, 1, '2024-12-13 04:48:07'),
(80, '21-1-01417', 33, 2, '2024-12-13 04:48:07'),
(81, '21-1-01417', 34, 3, '2024-12-13 04:48:07'),
(82, '21-1-01417', 31, 1, '2024-12-13 05:29:44'),
(83, '21-1-01417', 32, 2, '2024-12-13 05:29:44'),
(84, '21-1-01417', 34, 3, '2024-12-13 05:29:44'),
(85, '21-1-01417', 30, 1, '2024-12-13 05:34:51'),
(86, '21-1-01417', 33, 2, '2024-12-13 05:34:51'),
(87, '21-1-01417', 35, 3, '2024-12-13 05:34:51'),
(88, '21-1-01417', 31, 1, '2024-12-13 05:39:09'),
(89, '21-1-01417', 33, 2, '2024-12-13 05:39:09'),
(90, '21-1-01417', 35, 3, '2024-12-13 05:39:09'),
(91, '21-1-01417', 31, 1, '2024-12-13 05:41:51'),
(92, '21-1-01417', 33, 2, '2024-12-13 05:41:51'),
(93, '21-1-01417', 35, 3, '2024-12-13 05:41:51'),
(94, '21-1-01417', 31, 1, '2024-12-13 05:45:54'),
(95, '21-1-01417', 33, 2, '2024-12-13 05:45:54'),
(96, '21-1-01417', 35, 3, '2024-12-13 05:45:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD KEY `college_id` (`college_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`college_id`);

--
-- Indexes for table `comelec`
--
ALTER TABLE `comelec`
  ADD PRIMARY KEY (`comelec_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`election_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `comelec`
--
ALTER TABLE `comelec`
  MODIFY `comelec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `election_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`),
  ADD CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`);

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`),
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
