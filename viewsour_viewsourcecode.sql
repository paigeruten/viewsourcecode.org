-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 20, 2010 at 03:01 AM
-- Server version: 5.0.89
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `viewsour_viewsourcecode`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` mediumint(9) NOT NULL auto_increment,
  `author` varchar(255) NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `ip` varchar(15) NOT NULL default '',
  `content` text NOT NULL,
  `timestamp` int(11) NOT NULL default '0',
  `parent` mediumint(9) NOT NULL default '0',
  `article` varchar(10) NOT NULL default '',
  `username_link` varchar(255) NOT NULL default '',
  `is_admin` tinyint(4) NOT NULL,
  `published` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1872 ;

-- --------------------------------------------------------

--
-- Table structure for table `homebrew`
--

CREATE TABLE IF NOT EXISTS `homebrew` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url_name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `version` varchar(255) NOT NULL default '',
  `preview_image` varchar(255) NOT NULL default '',
  `downloads` int(11) NOT NULL default '0',
  `downloads_src` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `homebrew_features`
--

CREATE TABLE IF NOT EXISTS `homebrew_features` (
  `id` int(11) NOT NULL auto_increment,
  `homebrew_id` int(11) NOT NULL default '0',
  `feature` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=67 ;

-- --------------------------------------------------------

--
-- Table structure for table `homebrew_screenshots`
--

CREATE TABLE IF NOT EXISTS `homebrew_screenshots` (
  `id` int(11) NOT NULL auto_increment,
  `homebrew_id` int(11) NOT NULL default '0',
  `image_filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `content_markdown` text,
  `timestamp` int(11) NOT NULL,
  `url_title` varchar(255) NOT NULL,
  `publish` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `whale`
--

CREATE TABLE IF NOT EXISTS `whale` (
  `id` int(11) NOT NULL auto_increment,
  `text` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;
