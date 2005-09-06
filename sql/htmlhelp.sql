-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Sep 06, 2005 at 05:49 PM
-- Server version: 4.1.13
-- PHP Version: 5.0.4-3
-- 
-- Database: `htmlhelp`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `book`
-- 

CREATE TABLE `book` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(255) collate utf8_general_ci NOT NULL default '',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) collate utf8_bin NOT NULL default '',
  `catalog_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`(7))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `index_entry`
-- 

CREATE TABLE `index_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `term` varchar(255) collate utf8_general_ci NOT NULL default '',
  PRIMARY KEY  (`book_id`,`no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `index_link`
-- 

CREATE TABLE `index_link` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) collate utf8_bin NOT NULL default '',
  KEY `index` (`book_id`,`no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `lexeme`
-- 

CREATE TABLE `lexeme` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `string` varchar(32) collate utf8_general_ci NOT NULL default '',
  PRIMARY KEY  (`book_id`,`string`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `lexeme_link`
-- 

CREATE TABLE `lexeme_link` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `lexeme_no` smallint(5) unsigned NOT NULL default '0',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `count` tinyint(3) unsigned NOT NULL default '0',
  KEY `book_id` (`book_id`,`lexeme_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `metadata`
-- 

CREATE TABLE `metadata` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `name` varchar(31) collate utf8_bin NOT NULL default '',
  `value` varchar(255) collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`book_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `page`
-- 

CREATE TABLE `page` (
  `book_id` smallint(5) NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `path` varchar(255) collate utf8_bin NOT NULL default '',
  `compressed` tinyint(1) unsigned NOT NULL default '0',
  `content` mediumblob NOT NULL,
  `title` text collate utf8_general_ci,
  PRIMARY KEY  (`book_id`,`no`),
  UNIQUE KEY `path` (`book_id`,`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `tag`
-- 

CREATE TABLE `tag` (
  `tag` varchar(31) NOT NULL default '',
  `book_name` varchar(31) NOT NULL default '',
  KEY `tag` (`tag`,`book_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `toc_entry`
-- 

CREATE TABLE `toc_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `parent_no` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(255) collate utf8_general_ci NOT NULL default '',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`book_id`,`parent_no`,`no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
