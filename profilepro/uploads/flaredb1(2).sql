-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 13, 2014 at 04:33 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `flaredb1`
--

-- --------------------------------------------------------

--
-- Table structure for table `incidenttype`
--

CREATE TABLE IF NOT EXISTS `incidenttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incidentname` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `incidenttype`
--

INSERT INTO `incidenttype` (`id`, `incidentname`) VALUES
(1, 'Dehyderation'),
(2, 'Campus Alert'),
(3, 'Dorm'),
(4, 'Car');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE IF NOT EXISTS `report` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `incident_id` varchar(255) NOT NULL,
  `report_details` text NOT NULL,
  `report_date` datetime NOT NULL,
  `latitude` varchar(20) NOT NULL,
  `longitude` varchar(20) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`report_id`, `incident_id`, `report_details`, `report_date`, `latitude`, `longitude`, `userid`) VALUES
(1, '1', 'OMG', '2014-09-13 08:22:47', '12.953136', '77.578770', 60);
