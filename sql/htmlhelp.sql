-- phpMyAdmin SQL Dump
-- version 2.6.1-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Apr 02, 2005 at 09:21 PM
-- Server version: 4.0.24
-- PHP Version: 4.3.10-10
-- 
-- Database: `htmlhelp`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `book`
-- 

CREATE TABLE `book` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `alias` varchar(31) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) binary NOT NULL default '',
  `catalog_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `title` (`title`(7))
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `catalog`
-- 

CREATE TABLE `catalog` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `alias` varchar(31) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `name` (`name`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `index_entry`
-- 

CREATE TABLE `index_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `term` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`book_id`,`no`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `index_link`
-- 

CREATE TABLE `index_link` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) binary NOT NULL default '',
  KEY `index` (`book_id`,`no`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `lexeme`
-- 

CREATE TABLE `lexeme` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `string` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`book_id`,`string`)
) TYPE=MyISAM;

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
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `metadata`
-- 

CREATE TABLE `metadata` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `name` varchar(31) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`book_id`,`name`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `page`
-- 

CREATE TABLE `page` (
  `book_id` smallint(5) NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `path` varchar(255) binary NOT NULL default '',
  `compressed` tinyint(1) unsigned NOT NULL default '0',
  `content` mediumblob NOT NULL,
  `title` text,
  PRIMARY KEY  (`book_id`,`no`),
  UNIQUE KEY `path` (`book_id`,`path`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `toc_entry`
-- 

CREATE TABLE `toc_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `parent_no` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`book_id`,`parent_no`,`no`)
) TYPE=MyISAM;
