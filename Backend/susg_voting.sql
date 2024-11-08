-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2024 at 05:16 PM
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
-- Database: `susg_voting`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `candidate_ID` int(5) NOT NULL,
  `userIDC` int(10) NOT NULL,
  `campaignStatement` varchar(2000) NOT NULL,
  `deanCert` varchar(100) NOT NULL,
  `residencyProof` varchar(100) NOT NULL,
  `role` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate`
--

INSERT INTO `candidate` (`candidate_ID`, `userIDC`, `campaignStatement`, `deanCert`, `residencyProof`, `role`) VALUES
(1, 53643, 'Together, we can code a brighter future! As your candidate for the College of Computer Studies Presidency, I’m committed to fostering innovation, collaboration, and student success. Let’s enhance our learning experience, empower student voices, and create a community where your ideas matter. Vote Rynz Daval for a presidency that’s built for the students, by the students!', 'dfgdfgfdgdg', 'dfgfdgdfgd', 'President'),
(2, 67324, '\"Empowering your ideas, driving our future! As your candidate for the College of Computer Studies Presidency, I believe in a student government that listens, leads, and acts. Let’s build a more connected and supportive environment where every student’s potential can thrive. Vote Ray Daniel Cal for a leadership that’s focused on your growth, your voice, and our shared success!\"', 'fthydfhfg', 'yuret6urty', 'President'),
(3, 89432, '\r\n\"Leading with passion, driven by purpose! As your candidate for the College of Computer Studies Presidency, I’m dedicated to creating a more inclusive, innovative, and dynamic environment for all. Together, we’ll amplify student voices, strengthen opportunities, and turn challenges into achievements. Vote James Teves for a presidency that puts progress and students first!\"', 'jiuoiho', 'hnjyher', 'President');

-- --------------------------------------------------------

--
-- Table structure for table `commitee`
--

CREATE TABLE `commitee` (
  `memberID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `roleDesc` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commitee`
--

INSERT INTO `commitee` (`memberID`, `username`, `email`, `password`, `roleDesc`) VALUES
(1, 'ComelecUser1', 'ccscomelec@su.edu.ph', '12345', 'CommHead');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `userID` int(10) NOT NULL,
  `username` varchar(20) NOT NULL,
  `stud_ID` varchar(20) NOT NULL,
  `full_name` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `enrollStat` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`userID`, `username`, `stud_ID`, `full_name`, `email`, `password`, `enrollStat`) VALUES
(35243, 'Ren_Ayangco', '20-1-01009', 'Ren Joseph E. Ayangco', 'reneayangco@gmail.com', '12345', 'Enrolled'),
(53643, 'Rynz_Daval', '21-1-02004', 'Rynz Daval', 'rynzdaval@gmail.com', '12345', 'Enrolled'),
(67324, 'RD_Cal', '20-1-03004', 'Daniel Ray Cal', 'rdcal@gmail.com', '12345', 'Enrolled'),
(89432, 'James_Teves', '21-1-04005', 'James Ald Teves', 'jamesteves@gmail.com', '12345', 'Enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `vote`
--

CREATE TABLE `vote` (
  `voteID` int(11) NOT NULL,
  `voter_ID` int(11) NOT NULL,
  `candidate_IDV` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidate`
--
ALTER TABLE `candidate`
  ADD PRIMARY KEY (`candidate_ID`),
  ADD KEY `userIDC` (`userIDC`);

--
-- Indexes for table `commitee`
--
ALTER TABLE `commitee`
  ADD PRIMARY KEY (`memberID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `stud_ID` (`stud_ID`);

--
-- Indexes for table `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`voteID`),
  ADD KEY `voter_ID` (`voter_ID`),
  ADD KEY `candidate_IDV` (`candidate_IDV`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `candidate_ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `commitee`
--
ALTER TABLE `commitee`
  MODIFY `memberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `userID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89433;

--
-- AUTO_INCREMENT for table `vote`
--
ALTER TABLE `vote`
  MODIFY `voteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidate`
--
ALTER TABLE `candidate`
  ADD CONSTRAINT `candidate_ibfk_1` FOREIGN KEY (`userIDC`) REFERENCES `student` (`userID`);

--
-- Constraints for table `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`voter_ID`) REFERENCES `student` (`userID`),
  ADD CONSTRAINT `vote_ibfk_2` FOREIGN KEY (`candidate_IDV`) REFERENCES `candidate` (`candidate_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
