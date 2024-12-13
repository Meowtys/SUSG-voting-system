-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2024 at 01:58 PM
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
  `college_id` int(20) DEFAULT NULL,
  `position_id` int(3) DEFAULT NULL,
  `qualified` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `candidate_image` varchar(255) DEFAULT NULL,
  `party_id` int(50) DEFAULT NULL,
  `election_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidate_id`, `candidate_name`, `college_id`, `position_id`, `qualified`, `remarks`, `candidate_image`, `party_id`, `election_id`) VALUES
(36, 'Lewis Hamilton', 1, 1, 1, '', 'candidate_images/2324b4572ec4846adf52009798cec091.jpg', 1, 20),
(38, 'Abstain', 0, 1, 0, NULL, NULL, NULL, 20),
(39, 'Abstain', 0, 2, 0, NULL, NULL, NULL, 20),
(40, 'Abstain', 0, 3, 0, NULL, NULL, NULL, 20);

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
(17, '2024-11-12 21:25:00', '2024-11-30 21:25:00', 'Completed', '2024-11-30 00:29:05', '2024-11-30 00:29:05', 'Election 1', 0),
(19, '2024-12-17 11:09:00', '2024-12-18 11:10:00', 'Scheduled', '2024-12-12 20:38:08', '2024-12-13 03:49:38', '                                        Election 2                                    ', 0),
(20, '2024-12-13 10:00:00', '2024-12-14 10:00:00', 'Ongoing', '2024-12-12 20:53:54', '2024-12-13 08:07:37', 'Election 3', 1);

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `feedback_id` int(11) NOT NULL,
  `student_id` varchar(11) NOT NULL,
  `experience` int(5) NOT NULL,
  `suggestion` varchar(300) NOT NULL,
  `feedback_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `election_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

CREATE TABLE `parties` (
  `party_id` int(11) NOT NULL,
  `party_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parties`
--

INSERT INTO `parties` (`party_id`, `party_name`) VALUES
(1, 'CAUSE'),
(2, 'SURE'),
(3, 'INDEPENDENT');

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
  `has_voted` tinyint(1) NOT NULL DEFAULT 0,
  `election_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `college_id`, `password`, `has_voted`, `election_id`) VALUES
('21-1-01417', 'Westen Dasig', 1, 'west', 1, 20);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(11) NOT NULL,
  `student_id` varchar(11) NOT NULL,
  `candidate_id` int(100) NOT NULL,
  `position_id` int(3) NOT NULL,
  `vote_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `election_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`vote_id`, `student_id`, `candidate_id`, `position_id`, `vote_timestamp`, `election_id`) VALUES
(116, '21-1-01417', 38, 1, '2024-12-13 12:31:14', 20),
(117, '21-1-01417', 39, 2, '2024-12-13 12:31:14', 20),
(118, '21-1-01417', 40, 3, '2024-12-13 12:31:14', 20),
(119, '21-1-01417', 36, 1, '2024-12-13 12:53:15', 20),
(120, '21-1-01417', 39, 2, '2024-12-13 12:53:15', 20),
(121, '21-1-01417', 40, 3, '2024-12-13 12:53:15', 20);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD KEY `college_id` (`college_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `party_id` (`party_id`);

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
  ADD KEY `feedbacks_ibfk_1` (`student_id`),
  ADD KEY `election_id` (`election_id`);

--
-- Indexes for table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`party_id`);

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
  ADD KEY `students_ibfk_1` (`college_id`),
  ADD KEY `students_ibfk_2` (`election_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `student_election_idx` (`student_id`,`election_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
-- AUTO_INCREMENT for table `parties`
--
ALTER TABLE `parties`
  MODIFY `party_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`),
  ADD CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `candidates_ibfk_3` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`),
  ADD CONSTRAINT `candidates_ibfk_4` FOREIGN KEY (`party_id`) REFERENCES `parties` (`party_id`);

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedbacks_ibfk_2` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE SET NULL;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`),
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `votes_ibfk_5` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
