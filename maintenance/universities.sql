-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- Host: sql
-- Generation Time: Mar 24, 2012 at 04:52 PM
-- Server version: 5.1.59
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `u_fschulenburg`
--

-- --------------------------------------------------------

--
-- Table structure for table `mw_imp_universities`
--

CREATE TABLE IF NOT EXISTS `mw_imp_universities` (
  `university_id` int(11) NOT NULL AUTO_INCREMENT,
  `university_name` text NOT NULL,
  `university_city` text NOT NULL,
  `university_country` text NOT NULL,
  PRIMARY KEY (`university_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=77 ;

--
-- Dumping data for table `mw_imp_universities`
--

INSERT INTO `mw_imp_universities` (`university_id`, `university_name`, `university_city`, `university_country`) VALUES
(1, 'George Washington University', 'Washington, D.C.', 'United States'),
(2, 'Georgetown University', 'Washington, D.C.', 'United States'),
(3, 'Harvard University', 'Boston', 'United States'),
(4, 'Indiana University', 'Bloomington', 'United States'),
(5, 'James Madison University', 'Harrisonburg', 'United States'),
(6, 'Lehigh University', 'Bethlehem', 'United States'),
(7, 'Syracuse University', 'Syracuse', 'United States'),
(8, 'University of California, Berkeley', 'Berkeley', 'United States'),
(9, 'Louisiana State University', 'Baton Rouge', 'United States'),
(10, 'Michigan State University', 'East Lansing', 'United States'),
(11, 'Montana State University - Bozeman', 'Bozeman', 'United States'),
(12, 'New York University', 'New York City', 'United States'),
(13, 'San Francisco State University', 'San Francisco', 'United States'),
(14, 'Santa Clara University', 'Santa Clara', 'United States'),
(15, 'Simmons College (Massachusetts)', 'Boston', 'United States'),
(16, 'Texas Southern University', 'Houston', 'United States'),
(17, 'Troy University', 'Troy', 'United States'),
(18, 'University of Kentucky', 'Lexington', 'United States'),
(19, 'University of San Francisco', 'San Francisco', 'United States'),
(20, 'Virginia Polytechnic Institute and State University', 'Blacksburg', 'United States'),
(21, 'Western Carolina University', 'Cullowhee', 'United States'),
(22, 'Winona State University', 'Winona', 'United States'),
(23, 'Boston University', 'Boston', 'United States'),
(24, 'College of Engineering, Pune', 'Pune', 'India'),
(25, 'Symbiosis School of Economics', 'Pune', 'India'),
(26, 'SNDT Women''s University', 'Pune', 'India'),
(27, 'Nanjing Normal University', 'Nanjing', 'China'),
(28, 'Georgia Gwinnett College', 'Lawrenceville, Georgia', 'United States'),
(29, 'Georgia Southern University', 'Statesboro', 'United States'),
(30, 'Illinois State University', 'Normal, Illinois', 'United States'),
(31, 'Lansing Community College', 'Lansing, Michigan', 'United States'),
(32, 'Mills College', 'Oakland, California', 'United States'),
(33, 'Southern Connecticut State University', 'Connecticut', 'United States'),
(34, 'St. Charles Community College', 'St. Louis, Missouri', 'United States'),
(35, 'University of Massachusetts Amherst', 'Amherst, Massachusetts', 'United States'),
(36, 'University of Pittsburgh', 'Pittsburgh, Pennsylvania', 'United States'),
(37, 'University of Southern Indiana', 'Vanderburgh County, Indiana', 'United States'),
(38, 'University of Wisconsin-Madison', 'Madison, Wisconsin', 'United States'),
(39, 'Davidson College', 'Davidson, North Carolina', 'United States'),
(40, 'College of Staten Island', 'Staten Island, New York', 'United States'),
(41, 'Yale University', 'New Haven, Connecticut', 'United States'),
(42, 'Western Washington University', 'Bellingham, Washington', 'United States'),
(43, 'New Jersey Institute of Technology', 'Newark, New Jersey', 'United States'),
(44, 'University of Toronto', 'Toronto', 'Canada'),
(45, 'University of Toronto Scarborough', 'Toronto', 'Canada'),
(46, 'University of Alberta Augustana Faculty', 'Camrose, Alberta', 'Canada'),
(47, 'Alverno College', 'Milwaukee', 'United States'),
(48, 'Ball State University', 'Muncie, Indiana', 'United States'),
(49, 'Clemson University', 'Clemson, South Carolina', 'United States'),
(50, 'The College of New Jersey', 'Ewing Township, New Jersey', 'United States'),
(51, 'Drake University', 'Des Moines, Iowa', 'United States'),
(52, 'Gonzaga University', 'Spokane, Washington', 'United States'),
(53, 'Peer 2 Peer University', '', 'United States'),
(54, 'University of Alabama', 'Tuscaloosa, Alabama', 'United States'),
(55, 'University of Michigan', 'Ann Arbor, Michigan', 'United States'),
(56, 'University of Washington', 'Seattle', 'United States'),
(57, 'University of Western Ontario', 'London, Ontario', 'Canada'),
(58, 'Mount Allison University', 'Sackville, New Brunswick', 'Canada'),
(59, 'Carleton University', 'Ottawa, Ontario', 'Canada'),
(60, 'Hunter College, City University of New York', 'New York', 'United States'),
(61, 'Gustavus Adolphus College', 'St. Peter, Minnesota', 'United States'),
(62, 'Wake Forest University', 'Winston-Salem, North Carolina', 'United States'),
(63, 'Shenandoah University', 'Winchester, Virginia', 'United States'),
(64, 'Graduate Center, City University of New York', 'New York', 'United States'),
(65, 'University of Arizona', 'Tucson, Arizona', 'United States'),
(66, 'Rice University', 'Houston, Texas', 'United States'),
(67, 'California Maritime Academy', 'Vallejo, California', 'United States'),
(68, 'Ohio University', 'Athens, Ohio', 'United States'),
(69, 'Indiana University - Purdue University Indianapolis', 'Indianapolis', 'United States'),
(70, 'Northwestern University', 'Evanston and Chicago, Illinois', 'United States'),
(71, 'McMaster University', 'Hamilton, Ontario', 'Canada'),
(72, 'Mount Royal University', 'Calgary, Alberta', 'Canada'),
(73, 'University of Utah', 'Salt Lake City, Utah', 'United States'),
(74, 'George Fox University', 'Newberg, Oregon', 'United States'),
(75, 'University of New Haven', 'West Haven, Connecticut', 'United States'),
(76, 'Jackson Community College', 'Jackson County, Michigan', 'United States');
