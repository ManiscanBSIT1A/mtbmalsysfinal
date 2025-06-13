-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 10:53 AM
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
-- Database: `mtbmaldb`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `assessmentId` int(6) UNSIGNED NOT NULL,
  `assessmentNo` int(2) UNSIGNED NOT NULL,
  `learningMaterialRefNo` int(10) UNSIGNED NOT NULL,
  `title` varchar(155) NOT NULL,
  `description` text NOT NULL,
  `instruction` text DEFAULT NULL,
  `scoringRubric` text DEFAULT NULL,
  `sample` text DEFAULT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment`
--

INSERT INTO `assessment` (`assessmentId`, `assessmentNo`, `learningMaterialRefNo`, `title`, `description`, `instruction`, `scoringRubric`, `sample`, `schoolIdNo`) VALUES
(1, 97883, 4, 'Mga Lihok', 'Magdula Kita', 'Tabange makita sa inahan ang iyang anak pamaagi sa pag tubak og sakto sa mga pangutana.', 'Itama tanan aron perpek ka', NULL, 555555),
(2, 17959, 5, 'Tulungan si Anna na makauwi ng bahay.', 'Nawawala ang ilang gamit sa bahay! Tulungan silang mahanap sa pamamagitan ng tamang sagot. Bawat tamang sagot ay makakatulong na maibalik ang gamit. Huwag mag-alala kung magkamali- subukan lang muli!', 'Isulat ang tamang sagot!', '6/10', NULL, 555555),
(3, 57381, 7, 'TEST', 'test', 'test', 'test', NULL, 555555);

-- --------------------------------------------------------

--
-- Table structure for table `assessmentitems`
--

CREATE TABLE `assessmentitems` (
  `gameTempNo` int(3) UNSIGNED NOT NULL,
  `tempRefNo` int(2) UNSIGNED NOT NULL,
  `assessmentId` int(6) UNSIGNED NOT NULL,
  `useby` int(10) UNSIGNED DEFAULT NULL,
  `assessmentNo` int(2) UNSIGNED NOT NULL,
  `itemNo` int(2) NOT NULL,
  `iQuestion` text NOT NULL,
  `correctAnswer` varchar(255) NOT NULL,
  `uploadDateTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessmentitems`
--

INSERT INTO `assessmentitems` (`gameTempNo`, `tempRefNo`, `assessmentId`, `useby`, `assessmentNo`, `itemNo`, `iQuestion`, `correctAnswer`, `uploadDateTime`) VALUES
(1, 1, 1, 5, 97883, 1, '1+1=', '2', '2025-06-08 19:51:13'),
(2, 1, 1, 5, 97883, 2, '2+2=', '4', '2025-06-08 19:51:13'),
(3, 1, 1, 5, 97883, 3, '3+3=', '9', '2025-06-08 19:51:13'),
(4, 1, 1, 5, 97883, 4, '4+4=', '8', '2025-06-08 19:51:13'),
(5, 1, 1, 5, 97883, 5, '5+5=', '10', '2025-06-08 19:51:13'),
(6, 1, 1, 5, 97883, 6, '11+12=', '23', '2025-06-08 19:51:13'),
(7, 1, 1, 5, 97883, 7, 'Nagbabasa ka ba?', 'Oo', '2025-06-08 19:51:13'),
(8, 1, 1, 5, 97883, 8, 'Talaga ba?', 'Opo', '2025-06-08 19:51:13'),
(9, 1, 1, 5, 97883, 9, 'Marunong ka math?', 'Baka hindi po', '2025-06-08 19:51:13'),
(10, 1, 1, 5, 97883, 10, 'Ah ganon?', 'Opo', '2025-06-08 19:51:13'),
(11, 1, 2, 5, 17959, 1, 'Saan tayo naghuhugas ng pinggan?', 'lababo', '2025-06-09 01:04:51'),
(12, 1, 2, 5, 17959, 2, 'Anong gamit ang inuupuan sa sala?', 'sofa', '2025-06-09 01:04:51'),
(13, 1, 2, 5, 17959, 3, 'Saan tayo natutulog sa gabi?', 'kama', '2025-06-09 01:04:51'),
(14, 1, 2, 5, 17959, 4, 'Anong gamit ang panghigop ng sabaw?', 'kutsara', '2025-06-09 01:04:51'),
(15, 1, 2, 5, 17959, 5, 'Saan tayo nagluluto ng pagkain?', 'kalan', '2025-06-09 01:04:51'),
(16, 1, 2, 5, 17959, 6, 'Anong gamit ang pampunas ng kamay?', 'tuwalya', '2025-06-09 01:04:51'),
(17, 1, 2, 5, 17959, 7, 'Saan natin nilalagay ang malamig na pagkain?', 'pridyeder', '2025-06-09 01:04:51'),
(18, 1, 2, 5, 17959, 8, 'Anong gamit ang ginagamit sa pagwalis?', 'walis', '2025-06-09 01:04:51'),
(19, 1, 2, 5, 17959, 9, 'Anong gamit sa pagsipilyo ng ngipin?', 'sipilyo', '2025-06-09 01:04:51'),
(20, 1, 2, 5, 17959, 10, 'Anong gamit sa pag-inom ng tubig?', 'baso', '2025-06-09 01:04:51'),
(21, 1, 3, 5, 57381, 1, 'test', 'test', '2025-06-09 02:09:08'),
(22, 1, 3, 5, 57381, 2, 'test', 'jjj', '2025-06-09 02:09:08'),
(23, 1, 3, 5, 57381, 3, 'test', 'jjjj', '2025-06-09 02:09:08'),
(24, 1, 3, 5, 57381, 4, 'kkkk', 'jjjkkk', '2025-06-09 02:09:08'),
(25, 1, 3, 5, 57381, 5, 'kkk', 'kkk', '2025-06-09 02:09:08'),
(26, 1, 3, 5, 57381, 6, 'kkk', 'kkk', '2025-06-09 02:09:08'),
(27, 1, 3, 5, 57381, 7, 'kkk', 'kkk', '2025-06-09 02:09:08'),
(28, 1, 3, 5, 57381, 8, 'kkk', 'kkk', '2025-06-09 02:09:08'),
(29, 1, 3, 5, 57381, 9, 'kkk', 'kkk', '2025-06-09 02:09:08'),
(30, 1, 3, 5, 57381, 10, 'kkk', 'kkk', '2025-06-09 02:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `educator`
--

CREATE TABLE `educator` (
  `educatorNo` int(6) UNSIGNED NOT NULL,
  `accRefNo` int(10) UNSIGNED NOT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL,
  `fullName` varchar(70) NOT NULL,
  `edEmpIdNo` int(7) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `educator`
--

INSERT INTO `educator` (`educatorNo`, `accRefNo`, `schoolIdNo`, `fullName`, `edEmpIdNo`) VALUES
(2, 5, 555555, 'Kimberly Shayne Tanting', 5555553),
(4, 8, 555555, 'Kristine Anne Alingig', 5555554),
(5, 17, 555555, 'Dreevan Jay Miller', 5555556);

-- --------------------------------------------------------

--
-- Table structure for table `enrolled_students`
--

CREATE TABLE `enrolled_students` (
  `enrollmentNo` int(10) UNSIGNED NOT NULL,
  `subjectRefNo` int(10) UNSIGNED NOT NULL,
  `assignedEducator` int(10) UNSIGNED NOT NULL,
  `studentAccRefNo` int(10) UNSIGNED NOT NULL,
  `status` enum('Enrolled','Removed') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrolled_students`
--

INSERT INTO `enrolled_students` (`enrollmentNo`, `subjectRefNo`, `assignedEducator`, `studentAccRefNo`, `status`) VALUES
(1, 1, 5, 11, 'Enrolled'),
(2, 1, 5, 15, 'Enrolled'),
(3, 1, 5, 14, 'Enrolled'),
(4, 3, 5, 14, 'Enrolled'),
(5, 3, 5, 9, 'Enrolled'),
(6, 3, 5, 11, 'Enrolled'),
(7, 4, 5, 10, 'Enrolled'),
(8, 4, 5, 12, 'Enrolled'),
(9, 4, 5, 11, 'Enrolled'),
(10, 7, 5, 10, 'Enrolled'),
(11, 7, 5, 11, 'Enrolled'),
(12, 1, 5, 13, 'Enrolled'),
(13, 1, 5, 12, 'Enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `learningcontent`
--

CREATE TABLE `learningcontent` (
  `modTempNo` int(3) UNSIGNED NOT NULL,
  `tempRefNo` int(2) UNSIGNED NOT NULL,
  `topicNo` int(2) NOT NULL,
  `useby` int(10) UNSIGNED DEFAULT NULL,
  `topic` varchar(155) NOT NULL,
  `contentText` text NOT NULL,
  `moduleId` int(6) UNSIGNED NOT NULL,
  `lessonNo` int(2) UNSIGNED NOT NULL,
  `uploadDateTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learningcontent`
--

INSERT INTO `learningcontent` (`modTempNo`, `tempRefNo`, `topicNo`, `useby`, `topic`, `contentText`, `moduleId`, `lessonNo`, `uploadDateTime`) VALUES
(1, 3, 1, 5, 'Mga Lihok', 'Kini nga modyul maggiya sa mga estudyante sa pag-ila ug paggamit sa mga pulong sa lihok. Hisgotan dinhi kon unsa ang pulong sa lihok ug unsaon kini paggamit sa husto aron makaporma og klaro nga mga hugpulong.', 1, 1, '2025-06-08 18:18:40'),
(2, 3, 2, 5, 'test', 'test', 2, 2, '2025-06-09 02:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `learningmaterial`
--

CREATE TABLE `learningmaterial` (
  `learningMaterialRefNo` int(10) UNSIGNED NOT NULL,
  `subjectRefNo` int(10) UNSIGNED NOT NULL,
  `chapterNo` int(2) UNSIGNED NOT NULL,
  `title` varchar(155) NOT NULL,
  `description` text NOT NULL,
  `lmNo` int(3) UNSIGNED NOT NULL,
  `lmType` enum('Learning Module','Learning Assessment') DEFAULT NULL,
  `diffLevel` enum('Easy','Intermediate','Hard') DEFAULT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL,
  `sourceRef` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learningmaterial`
--

INSERT INTO `learningmaterial` (`learningMaterialRefNo`, `subjectRefNo`, `chapterNo`, `title`, `description`, `lmNo`, `lmType`, `diffLevel`, `schoolIdNo`, `sourceRef`) VALUES
(3, 1, 1, 'Pag-ila ug Paggamit sa Pulong sa Lihok', 'Kini nga modyul maggiya sa mga estudyante sa pag-ila ug paggamit sa mga pulong sa lihok. Hisgotan dinhi kon unsa ang pulong sa lihok ug unsaon kini paggamit sa husto aron makaporma og klaro nga mga hugpulong.', 1, 'Learning Module', 'Easy', 555555, NULL),
(4, 1, 1, 'Mga Lihok', 'Magdula Kita', 1, 'Learning Assessment', 'Easy', 555555, NULL),
(5, 1, 1, 'Tulungan si Anna na makauwi ng bahay.', 'Nawawala ang ilang gamit sa bahay! Tulungan silang mahanap sa pamamagitan ng tamang sagot. Bawat tamang sagot ay makakatulong na maibalik ang gamit. Huwag mag-alala kung magkamali- subukan lang muli!', 1, 'Learning Assessment', 'Easy', 555555, NULL),
(6, 1, 2, 'test', 'test', 2, 'Learning Module', 'Easy', 555555, NULL),
(7, 1, 1, 'TEST', 'test', 1, 'Learning Assessment', 'Easy', 555555, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE `module` (
  `moduleId` int(6) UNSIGNED NOT NULL,
  `lessonNo` int(2) UNSIGNED NOT NULL,
  `learningMaterialRefNo` int(10) UNSIGNED NOT NULL,
  `title` varchar(155) NOT NULL,
  `description` text NOT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `module`
--

INSERT INTO `module` (`moduleId`, `lessonNo`, `learningMaterialRefNo`, `title`, `description`, `schoolIdNo`) VALUES
(1, 1, 3, 'Pag-ila ug Paggamit sa Pulong sa Lihok', 'Kini nga modyul maggiya sa mga estudyante sa pag-ila ug paggamit sa mga pulong sa lihok. Hisgotan dinhi kon unsa ang pulong sa lihok ug unsaon kini paggamit sa husto aron makaporma og klaro nga mga hugpulong.', 555555),
(2, 2, 6, 'test', 'test', 555555);

-- --------------------------------------------------------

--
-- Table structure for table `mtbmalusers`
--

CREATE TABLE `mtbmalusers` (
  `accRefNo` int(10) UNSIGNED NOT NULL,
  `schoolRefNo` int(10) UNSIGNED NOT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL,
  `firstName` varchar(30) NOT NULL,
  `lastName` varchar(30) NOT NULL,
  `dob` date NOT NULL,
  `emailAddress` varchar(50) NOT NULL,
  `contactNo` varchar(12) NOT NULL,
  `username` varchar(15) NOT NULL,
  `password` varchar(60) NOT NULL,
  `accCreator` int(10) UNSIGNED NOT NULL,
  `accType` enum('School Administrator','Educator','Student') DEFAULT NULL,
  `regDateTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mtbmalusers`
--

INSERT INTO `mtbmalusers` (`accRefNo`, `schoolRefNo`, `schoolIdNo`, `firstName`, `lastName`, `dob`, `emailAddress`, `contactNo`, `username`, `password`, `accCreator`, `accType`, `regDateTime`) VALUES
(1, 1, 555555, 'Francis', 'Madanlo', '1994-06-09', 'fmadanlo@llsh.edu.ph', '09000000002', 'admin0', '$2y$10$MYdh.eq1CgIABWjmIhB2N.XbomB3hAw1YBE7M.//Vz4gikSo7dlZy', 0, 'School Administrator', '2025-06-07 06:30:46'),
(2, 2, 555556, 'Karel', 'Ambe', '1993-05-09', 'kambe@fama.edu.ph', '09000000004', 'admin1', '$2y$10$o3VKtaI1ZRlV5kc.iz2E..HeEzjnA93sm8Ia.kipZtqM654WFf6QO', 0, 'School Administrator', '2025-06-07 06:33:42'),
(3, 1, 555555, 'Karel', 'Ambe', '1993-05-09', 'kambe@llsh.edu.ph', '09000000005', 'admin01', '$2y$10$d8xJgAi7.Yu5ucumBfdIe.ntb3jXu9kXSOxYL32wkNQTM9KlMmSgy', 1, 'School Administrator', '2025-06-07 06:35:39'),
(5, 1, 555555, 'Kimberly Shayne', 'Tanting', '2000-04-11', 'kstanting@llsh.edu.ph', '09000000007', 'educator1', '$2y$10$9cmbVIeF8PK3f1HwdgMsjO6SuMn4kOPBQJOj6IBxO/Ct.WTvKvDhW', 1, 'Educator', '2025-06-07 06:40:13'),
(8, 1, 555555, 'Kristine Anne', 'Alingig', '1999-05-09', 'kaalingig@llsh.edu.ph', '09000000008', 'educator0', '$2y$10$UnWc5VvlkeRxyfIiHUzObOYMrlP0uqz7NR6IDeAhBG03xmKMc6.66', 1, 'Educator', '2025-06-07 06:45:03'),
(9, 1, 555555, 'Shile Mariz', 'Ala-an', '2020-07-26', 'smala-an@llsh.edu.ph', '09000000009', 'student0', '$2y$10$17c9LHRT4EsF.cundTqbk.K6VWwM8jVqiJfbWELXWtVja3F7LEc9u', 1, 'Student', '2025-06-07 06:48:35'),
(10, 1, 555555, 'Zaira Julia', 'Ala-an', '2020-07-01', 'zaala-an@llsh.edu.ph', '09000000010', 'student1', '$2y$10$iNEYL565qrAFdvhVyRhCa.f6Q6u2lfIwS7K7wKqIKuKJJepmKum/m', 1, 'Student', '2025-06-07 06:52:46'),
(11, 1, 555555, 'Allen', 'Vallejos', '2020-09-08', 'avallejos@llsh.edu.ph', '09000000012', 'student2', '$2y$10$C9yNLc31cf/YyCE8KpVmmuAgfHD0Pm3woY2RcZPNYZKE0QwStozC6', 1, 'Student', '2025-06-07 06:58:17'),
(12, 1, 555555, 'Sudakl', 'Ginawanu', '2020-09-08', 'sginawanu@llsh.edu.ph', '09000000014', 'student3', '$2y$10$gfDPhI3qiJCyTSd78A/cqOOkvg6/bcN5bdSquLc00WFl8jFRF6jqi', 1, 'Student', '2025-06-07 07:03:38'),
(13, 1, 555555, 'Nami', 'Go', '2020-12-17', 'ngo@llsh.edu.ph', '09000000016', 'student4', '$2y$10$Vjiol/TXlPaXHI1DJcn8MuzKqG0L6j59NCQN2UVhTWtLk5kKP04Pe', 1, 'Student', '2025-06-07 07:06:31'),
(14, 1, 555555, 'Myla', 'Ala-ir', '2020-08-09', 'mala-ir@llsh.edu.ph', '09000000018', 'student5', '$2y$10$oN/sHLd1q0kdVkCcxqoLOON./bMaXHQbpmJbFdDOdBgFRehILHRUe', 1, 'Student', '2025-06-07 07:09:03'),
(15, 1, 555555, 'Jetro', 'Abequibel', '2020-06-08', 'jabequibel@llsh.edu.ph', '09000000020', 'student6', '$2y$10$ieIWEDe./YsM6juOlCqgUeL7ZjrsX1uk8nX395Rx.6pHMtEEHzbIe', 1, 'Student', '2025-06-07 07:12:02'),
(17, 1, 555555, 'Dreevan Jay', 'Miller', '2020-02-22', 'djmiller@llsh.edu.ph', '09000000022', 'educator2', '$2y$10$Qj8lWbfS/Zag1xnR60N.vutQiHjApzL7rJ8FF1QrIhz7qL/Im1Voi', 1, 'Educator', '2025-06-07 07:14:38');

-- --------------------------------------------------------

--
-- Table structure for table `school`
--

CREATE TABLE `school` (
  `schoolRefNo` int(10) UNSIGNED NOT NULL,
  `schoolName` tinytext NOT NULL,
  `shortName` varchar(155) NOT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL,
  `emailAddress` varchar(50) NOT NULL,
  `schoolType` enum('Public Integrated School','Private Integrated School','Private Elementary School','Public Elementary School') DEFAULT NULL,
  `contactNo` varchar(12) NOT NULL,
  `locAddress` varchar(70) NOT NULL,
  `region` varchar(30) NOT NULL,
  `adminUserName` varchar(15) NOT NULL,
  `regDateTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school`
--

INSERT INTO `school` (`schoolRefNo`, `schoolName`, `shortName`, `schoolIdNo`, `emailAddress`, `schoolType`, `contactNo`, `locAddress`, `region`, `adminUserName`, `regDateTime`) VALUES
(1, 'Little Lamb School House', 'LLSH', 555555, 'llsh@edu.ph', 'Private Elementary School', '09000000001', 'Bacaca Road, Brgy. 19-B, Bajada, Davao City', 'Region XI', 'admin0', '2025-06-07 06:28:50'),
(2, 'Fil. Asian Mission Academy', 'FAMA', 555556, 'fama@edu.ph', 'Private Integrated School', '09000000003', 'Bacaca Road, Brgy. 19-B, Bajada, Davao City', 'Region XI', 'admin1', '2025-06-07 06:32:17');

-- --------------------------------------------------------

--
-- Table structure for table `schooladministrator`
--

CREATE TABLE `schooladministrator` (
  `adminNo` int(6) UNSIGNED NOT NULL,
  `accRefNo` int(10) UNSIGNED NOT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL,
  `fullName` varchar(70) NOT NULL,
  `adEmpIdNo` int(7) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schooladministrator`
--

INSERT INTO `schooladministrator` (`adminNo`, `accRefNo`, `schoolIdNo`, `fullName`, `adEmpIdNo`) VALUES
(1, 1, 555555, 'Francis Madanlo', 5555551),
(2, 2, 555556, 'Karel Ambe', 5555561),
(3, 3, 555555, 'Karel Ambe', 5555552);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentNo` int(6) UNSIGNED NOT NULL,
  `accRefNo` int(10) UNSIGNED NOT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL,
  `lrn` varchar(12) NOT NULL,
  `fullName` varchar(70) NOT NULL,
  `parentGuardianName` varchar(70) NOT NULL,
  `pgRStoStudent` enum('Father','Mother','Guardian','Sibling','Close Relative') DEFAULT NULL,
  `pgDOB` date NOT NULL,
  `pgMaritalStatus` varchar(15) NOT NULL,
  `pgEmailAdd` varchar(30) NOT NULL,
  `pgContactNo` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentNo`, `accRefNo`, `schoolIdNo`, `lrn`, `fullName`, `parentGuardianName`, `pgRStoStudent`, `pgDOB`, `pgMaritalStatus`, `pgEmailAdd`, `pgContactNo`) VALUES
(1, 9, 555555, '123123123123', 'Shile Mariz Ala-an', 'Maria Leonora Teresa Ala-an', 'Mother', '1974-09-08', 'Widowed', 'mlala-an@usep.edu.ph', '09000000010'),
(2, 10, 555555, '123412341234', 'Zaira Julia Ala-an', 'John Oscar Ala-an', 'Sibling', '0199-06-09', 'Single', 'jovala-a@usep.edu.ph', '09000000011'),
(3, 11, 555555, '321321654987', 'Allen Vallejos', 'Matan Ñam Vallejos', 'Father', '1999-11-05', 'Married', 'mvallejos@gmail.com', '09000000013'),
(4, 12, 555555, '321665487896', 'Sudakl Ginawanu', 'Aakneko Ginawanu', 'Close Relative', '1999-09-08', 'Single', 'aginawnu@gmail.com', '09000000015'),
(5, 13, 555555, '987789987789', 'Nami Go', 'Christian Go', 'Sibling', '2001-06-05', 'Single', 'chrzgo@gmail.com', '09000000017'),
(6, 14, 555555, '654456654456', 'Myla Ala-ir', 'Therese Ng Alair', 'Mother', '2002-06-08', 'Married', 'therese@gmail.com', '09000000019'),
(7, 15, 555555, '123311234654', 'Jetro Abequibel', 'Joshua Abequibel', 'Father', '2000-09-08', 'Single', 'jabequibel@gmail.com', '09000000021');

-- --------------------------------------------------------

--
-- Table structure for table `student_assessment_answers`
--

CREATE TABLE `student_assessment_answers` (
  `answerId` int(10) UNSIGNED NOT NULL,
  `stLRN` varchar(12) NOT NULL,
  `assessmentId` int(6) UNSIGNED NOT NULL,
  `assessmentItemId` int(3) UNSIGNED NOT NULL,
  `studentAnswer` varchar(255) NOT NULL,
  `isCorrect` tinyint(1) DEFAULT NULL,
  `reviewedBy` int(10) UNSIGNED DEFAULT NULL,
  `reviewDateTime` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_assessment_result`
--

CREATE TABLE `student_assessment_result` (
  `resultId` int(10) UNSIGNED NOT NULL,
  `stLRN` varchar(12) NOT NULL,
  `assessmentId` int(6) UNSIGNED NOT NULL,
  `totalItems` int(10) UNSIGNED NOT NULL,
  `correctAnswers` int(10) UNSIGNED NOT NULL,
  `rawScore` decimal(5,2) GENERATED ALWAYS AS (`correctAnswers` / `totalItems` * 100) STORED,
  `remarks` text DEFAULT NULL,
  `finalizedBy` int(10) UNSIGNED DEFAULT NULL,
  `finalDateTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subjectRefNo` int(10) UNSIGNED NOT NULL,
  `subjectIdNo` int(7) UNSIGNED NOT NULL,
  `subjTitle` varchar(155) NOT NULL,
  `subjDescription` text NOT NULL,
  `mtLanguage` varchar(30) NOT NULL,
  `adminCreator` int(6) UNSIGNED NOT NULL,
  `assignedEducator` int(10) UNSIGNED DEFAULT NULL,
  `schoolIdNo` int(6) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subjectRefNo`, `subjectIdNo`, `subjTitle`, `subjDescription`, `mtLanguage`, `adminCreator`, `assignedEducator`, `schoolIdNo`) VALUES
(1, 1111111, 'K2-Pulong sa Lihok', 'Pagtudlo sa mga bata kung unsa ang pulong sa lihok ug giunsa kini paggamit sa hugpong sa mga pulong.', 'Cebuano', 1, 5, 555555),
(3, 1111113, 'Gr1-Matematika', 'Pagtudlo sa mga estudyante sa yano nga konsepto sa pagdugang gamit ang simbolo nga \"+\" ug \"=\", ug pagsulbad sa yano nga problema sa pagdugang.', 'Cebuano', 1, 5, 555555),
(4, 1111114, 'K1-Filipino', 'Naglalaman ang dokumento ng mga gabay para sa wastong pagbabaybay ng mga salita. Binibigyang-diin nito ang kahalagahan ng pag-aaral ng tuntunin sa pagbabaybay upang matiyak ang wastong pagsulat ng mga salita. Nagbibigay ito ng mga gawain para masanay ang mambabasa sa pagtukoy at pagsulat ng mga salita nang may wastong baybay.', 'Tagalog', 1, 8, 555555),
(5, 1111115, 'Gr2-Filipino', 'Ang dokumento ay tungkol sa paunang salita para sa kagamitan ng mag-aaral sa Filipino 2. Naglalaman ito ng pagpapakilala sa mga seksyon ng kagamitan at ang layunin ng bawat bahagi upang matuto ang mga mag-aaral.', 'Tagalog', 1, 8, 555555),
(7, 4564563, 'K1 - Tagalog', 'Ang modyul na ito ay gagabay sa mga mag-aaral sa pagtukoy at paggamit ng mga salitang kilos. Tatalakayin nito kung ano ang mga salitang kilos at kung paano gamitin ang mga ito nang tama upang makabuo ng malinaw na mga pangungusap.', 'Tagalog', 1, 5, 555555);

-- --------------------------------------------------------

--
-- Table structure for table `supportingmedia`
--

CREATE TABLE `supportingmedia` (
  `fileNo` int(4) UNSIGNED NOT NULL,
  `learningMaterialRefNo` int(10) UNSIGNED NOT NULL,
  `gameTempNo` int(3) UNSIGNED DEFAULT NULL,
  `modTempNo` int(3) UNSIGNED DEFAULT NULL,
  `accRefNo` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `filedata` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supportingmedia`
--

INSERT INTO `supportingmedia` (`fileNo`, `learningMaterialRefNo`, `gameTempNo`, `modTempNo`, `accRefNo`, `filename`, `filedata`) VALUES
(2, 3, NULL, 1, 5, '13.png', NULL),
(3, 3, NULL, 1, 5, '14.png', NULL),
(4, 4, 1, NULL, 5, 'bunny.gif', NULL),
(5, 4, 10, NULL, 5, 'bunny_card-1.png', NULL),
(6, 4, 1, NULL, 5, 'character-2.gif', NULL),
(7, 5, 11, NULL, 5, 'character-1.gif', NULL),
(8, 5, 20, NULL, 5, 'character-2.gif', NULL),
(9, 5, 11, NULL, 5, 'market.jpg', NULL),
(10, 6, NULL, 2, 5, '13.png', NULL),
(11, 6, NULL, 2, 5, '13.png', NULL),
(12, 7, 21, NULL, 5, 'character-1.gif', NULL),
(13, 7, 30, NULL, 5, 'character-2.gif', NULL),
(14, 7, 21, NULL, 5, 'market.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `template`
--

CREATE TABLE `template` (
  `tempRefNo` int(2) UNSIGNED NOT NULL,
  `tempName` varchar(70) NOT NULL,
  `tempDescription` text NOT NULL,
  `tempType` enum('Learning Module','Learning Assessment') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `template`
--

INSERT INTO `template` (`tempRefNo`, `tempName`, `tempDescription`, `tempType`) VALUES
(1, 'Quest to Learn', 'Gamified, story-driven questions for knowledge checks.', 'Learning Assessment'),
(2, 'Flip and Match', 'Pair up images, perfect for vocabulary/identification.', 'Learning Assessment'),
(3, 'Text and Media', 'Add up to 4 image files and text for each lesson.', 'Learning Module'),
(4, 'Image Only', 'Upload an image file only.', 'Learning Module'),
(5, 'Upload a PDF File', 'A single PDF for the lesson.', 'Learning Module');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`assessmentId`),
  ADD KEY `schoolIdNo` (`schoolIdNo`),
  ADD KEY `learningMaterialRefNo` (`learningMaterialRefNo`);

--
-- Indexes for table `assessmentitems`
--
ALTER TABLE `assessmentitems`
  ADD PRIMARY KEY (`gameTempNo`),
  ADD KEY `tempRefNo` (`tempRefNo`),
  ADD KEY `assessmentId` (`assessmentId`),
  ADD KEY `useby` (`useby`);

--
-- Indexes for table `educator`
--
ALTER TABLE `educator`
  ADD PRIMARY KEY (`educatorNo`),
  ADD UNIQUE KEY `edEmpIdNo` (`edEmpIdNo`),
  ADD KEY `accRefNo` (`accRefNo`),
  ADD KEY `schoolIdNo` (`schoolIdNo`);

--
-- Indexes for table `enrolled_students`
--
ALTER TABLE `enrolled_students`
  ADD PRIMARY KEY (`enrollmentNo`),
  ADD KEY `subjectRefNo` (`subjectRefNo`),
  ADD KEY `assignedEducator` (`assignedEducator`),
  ADD KEY `studentAccRefNo` (`studentAccRefNo`);

--
-- Indexes for table `learningcontent`
--
ALTER TABLE `learningcontent`
  ADD PRIMARY KEY (`modTempNo`),
  ADD KEY `tempRefNo` (`tempRefNo`),
  ADD KEY `moduleId` (`moduleId`),
  ADD KEY `useby` (`useby`);

--
-- Indexes for table `learningmaterial`
--
ALTER TABLE `learningmaterial`
  ADD PRIMARY KEY (`learningMaterialRefNo`),
  ADD KEY `schoolIdNo` (`schoolIdNo`),
  ADD KEY `subjectRefNo` (`subjectRefNo`);

--
-- Indexes for table `module`
--
ALTER TABLE `module`
  ADD PRIMARY KEY (`moduleId`),
  ADD KEY `schoolIdNo` (`schoolIdNo`),
  ADD KEY `learningMaterialRefNo` (`learningMaterialRefNo`);

--
-- Indexes for table `mtbmalusers`
--
ALTER TABLE `mtbmalusers`
  ADD PRIMARY KEY (`accRefNo`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`),
  ADD UNIQUE KEY `contactNo` (`contactNo`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `schoolRefNo` (`schoolRefNo`),
  ADD KEY `schoolIdNo` (`schoolIdNo`);

--
-- Indexes for table `school`
--
ALTER TABLE `school`
  ADD PRIMARY KEY (`schoolRefNo`),
  ADD UNIQUE KEY `schoolIdNo` (`schoolIdNo`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`),
  ADD UNIQUE KEY `contactNo` (`contactNo`),
  ADD UNIQUE KEY `adminUserName` (`adminUserName`);

--
-- Indexes for table `schooladministrator`
--
ALTER TABLE `schooladministrator`
  ADD PRIMARY KEY (`adminNo`),
  ADD UNIQUE KEY `adEmpIdNo` (`adEmpIdNo`),
  ADD KEY `accRefNo` (`accRefNo`),
  ADD KEY `schoolIdNo` (`schoolIdNo`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentNo`),
  ADD UNIQUE KEY `lrn` (`lrn`),
  ADD KEY `accRefNo` (`accRefNo`),
  ADD KEY `schoolIdNo` (`schoolIdNo`);

--
-- Indexes for table `student_assessment_answers`
--
ALTER TABLE `student_assessment_answers`
  ADD PRIMARY KEY (`answerId`),
  ADD KEY `stLRN` (`stLRN`),
  ADD KEY `assessmentId` (`assessmentId`),
  ADD KEY `assessmentItemId` (`assessmentItemId`),
  ADD KEY `reviewedBy` (`reviewedBy`);

--
-- Indexes for table `student_assessment_result`
--
ALTER TABLE `student_assessment_result`
  ADD PRIMARY KEY (`resultId`),
  ADD KEY `stLRN` (`stLRN`),
  ADD KEY `assessmentId` (`assessmentId`),
  ADD KEY `finalizedBy` (`finalizedBy`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subjectRefNo`),
  ADD KEY `adminCreator` (`adminCreator`),
  ADD KEY `assignedEducator` (`assignedEducator`),
  ADD KEY `schoolIdNo` (`schoolIdNo`);

--
-- Indexes for table `supportingmedia`
--
ALTER TABLE `supportingmedia`
  ADD PRIMARY KEY (`fileNo`),
  ADD KEY `learningMaterialRefNo` (`learningMaterialRefNo`),
  ADD KEY `gameTempNo` (`gameTempNo`),
  ADD KEY `modTempNo` (`modTempNo`),
  ADD KEY `accRefNo` (`accRefNo`);

--
-- Indexes for table `template`
--
ALTER TABLE `template`
  ADD PRIMARY KEY (`tempRefNo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `assessmentId` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assessmentitems`
--
ALTER TABLE `assessmentitems`
  MODIFY `gameTempNo` int(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `educator`
--
ALTER TABLE `educator`
  MODIFY `educatorNo` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrolled_students`
--
ALTER TABLE `enrolled_students`
  MODIFY `enrollmentNo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `learningcontent`
--
ALTER TABLE `learningcontent`
  MODIFY `modTempNo` int(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `learningmaterial`
--
ALTER TABLE `learningmaterial`
  MODIFY `learningMaterialRefNo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `module`
--
ALTER TABLE `module`
  MODIFY `moduleId` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mtbmalusers`
--
ALTER TABLE `mtbmalusers`
  MODIFY `accRefNo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `school`
--
ALTER TABLE `school`
  MODIFY `schoolRefNo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `schooladministrator`
--
ALTER TABLE `schooladministrator`
  MODIFY `adminNo` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentNo` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_assessment_answers`
--
ALTER TABLE `student_assessment_answers`
  MODIFY `answerId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_assessment_result`
--
ALTER TABLE `student_assessment_result`
  MODIFY `resultId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subjectRefNo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `supportingmedia`
--
ALTER TABLE `supportingmedia`
  MODIFY `fileNo` int(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `template`
--
ALTER TABLE `template`
  MODIFY `tempRefNo` int(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessment`
--
ALTER TABLE `assessment`
  ADD CONSTRAINT `assessment_ibfk_1` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_ibfk_2` FOREIGN KEY (`learningMaterialRefNo`) REFERENCES `learningmaterial` (`learningMaterialRefNo`) ON DELETE CASCADE;

--
-- Constraints for table `assessmentitems`
--
ALTER TABLE `assessmentitems`
  ADD CONSTRAINT `assessmentitems_ibfk_1` FOREIGN KEY (`tempRefNo`) REFERENCES `template` (`tempRefNo`),
  ADD CONSTRAINT `assessmentitems_ibfk_2` FOREIGN KEY (`assessmentId`) REFERENCES `assessment` (`assessmentId`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessmentitems_ibfk_3` FOREIGN KEY (`useby`) REFERENCES `mtbmalusers` (`accRefNo`) ON DELETE SET NULL;

--
-- Constraints for table `educator`
--
ALTER TABLE `educator`
  ADD CONSTRAINT `educator_ibfk_1` FOREIGN KEY (`accRefNo`) REFERENCES `mtbmalusers` (`accRefNo`),
  ADD CONSTRAINT `educator_ibfk_2` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE;

--
-- Constraints for table `enrolled_students`
--
ALTER TABLE `enrolled_students`
  ADD CONSTRAINT `enrolled_students_ibfk_1` FOREIGN KEY (`subjectRefNo`) REFERENCES `subject` (`subjectRefNo`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrolled_students_ibfk_2` FOREIGN KEY (`assignedEducator`) REFERENCES `mtbmalusers` (`accRefNo`),
  ADD CONSTRAINT `enrolled_students_ibfk_3` FOREIGN KEY (`studentAccRefNo`) REFERENCES `mtbmalusers` (`accRefNo`);

--
-- Constraints for table `learningcontent`
--
ALTER TABLE `learningcontent`
  ADD CONSTRAINT `learningcontent_ibfk_1` FOREIGN KEY (`tempRefNo`) REFERENCES `template` (`tempRefNo`),
  ADD CONSTRAINT `learningcontent_ibfk_2` FOREIGN KEY (`moduleId`) REFERENCES `module` (`moduleId`) ON DELETE CASCADE,
  ADD CONSTRAINT `learningcontent_ibfk_3` FOREIGN KEY (`useby`) REFERENCES `mtbmalusers` (`accRefNo`) ON DELETE SET NULL;

--
-- Constraints for table `learningmaterial`
--
ALTER TABLE `learningmaterial`
  ADD CONSTRAINT `learningmaterial_ibfk_1` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE,
  ADD CONSTRAINT `learningmaterial_ibfk_2` FOREIGN KEY (`subjectRefNo`) REFERENCES `subject` (`subjectRefNo`) ON DELETE CASCADE;

--
-- Constraints for table `module`
--
ALTER TABLE `module`
  ADD CONSTRAINT `module_ibfk_1` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE,
  ADD CONSTRAINT `module_ibfk_2` FOREIGN KEY (`learningMaterialRefNo`) REFERENCES `learningmaterial` (`learningMaterialRefNo`) ON DELETE CASCADE;

--
-- Constraints for table `mtbmalusers`
--
ALTER TABLE `mtbmalusers`
  ADD CONSTRAINT `mtbmalusers_ibfk_1` FOREIGN KEY (`schoolRefNo`) REFERENCES `school` (`schoolRefNo`) ON DELETE CASCADE,
  ADD CONSTRAINT `mtbmalusers_ibfk_2` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE;

--
-- Constraints for table `schooladministrator`
--
ALTER TABLE `schooladministrator`
  ADD CONSTRAINT `schooladministrator_ibfk_1` FOREIGN KEY (`accRefNo`) REFERENCES `mtbmalusers` (`accRefNo`),
  ADD CONSTRAINT `schooladministrator_ibfk_2` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`accRefNo`) REFERENCES `mtbmalusers` (`accRefNo`),
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE;

--
-- Constraints for table `student_assessment_answers`
--
ALTER TABLE `student_assessment_answers`
  ADD CONSTRAINT `student_assessment_answers_ibfk_1` FOREIGN KEY (`stLRN`) REFERENCES `student` (`lrn`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessment_answers_ibfk_2` FOREIGN KEY (`assessmentId`) REFERENCES `assessment` (`assessmentId`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessment_answers_ibfk_3` FOREIGN KEY (`assessmentItemId`) REFERENCES `assessmentitems` (`gameTempNo`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessment_answers_ibfk_4` FOREIGN KEY (`reviewedBy`) REFERENCES `mtbmalusers` (`accRefNo`) ON DELETE SET NULL;

--
-- Constraints for table `student_assessment_result`
--
ALTER TABLE `student_assessment_result`
  ADD CONSTRAINT `student_assessment_result_ibfk_1` FOREIGN KEY (`stLRN`) REFERENCES `student` (`lrn`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessment_result_ibfk_2` FOREIGN KEY (`assessmentId`) REFERENCES `assessment` (`assessmentId`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessment_result_ibfk_3` FOREIGN KEY (`finalizedBy`) REFERENCES `mtbmalusers` (`accRefNo`) ON DELETE SET NULL;

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`adminCreator`) REFERENCES `mtbmalusers` (`accRefNo`),
  ADD CONSTRAINT `subject_ibfk_2` FOREIGN KEY (`assignedEducator`) REFERENCES `mtbmalusers` (`accRefNo`) ON DELETE SET NULL,
  ADD CONSTRAINT `subject_ibfk_3` FOREIGN KEY (`schoolIdNo`) REFERENCES `school` (`schoolIdNo`) ON DELETE CASCADE;

--
-- Constraints for table `supportingmedia`
--
ALTER TABLE `supportingmedia`
  ADD CONSTRAINT `supportingmedia_ibfk_1` FOREIGN KEY (`learningMaterialRefNo`) REFERENCES `learningmaterial` (`learningMaterialRefNo`),
  ADD CONSTRAINT `supportingmedia_ibfk_2` FOREIGN KEY (`gameTempNo`) REFERENCES `assessmentitems` (`gameTempNo`) ON DELETE SET NULL,
  ADD CONSTRAINT `supportingmedia_ibfk_3` FOREIGN KEY (`modTempNo`) REFERENCES `learningcontent` (`modTempNo`) ON DELETE SET NULL,
  ADD CONSTRAINT `supportingmedia_ibfk_4` FOREIGN KEY (`accRefNo`) REFERENCES `mtbmalusers` (`accRefNo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
