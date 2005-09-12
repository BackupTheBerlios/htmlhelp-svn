-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Sep 12, 2005 at 11:47 AM
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
  `title` varchar(255) NOT NULL default '',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`(7))
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `book_alias`
-- 

CREATE TABLE `book_alias` (
  `alias` varchar(31) NOT NULL default '',
  `book_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`alias`),
  KEY `book_id` (`book_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `book_tag`
-- 

CREATE TABLE `book_tag` (
  `tag_id` tinyint(3) unsigned NOT NULL default '0',
  `book_name` varchar(31) NOT NULL default '',
  KEY `tag_id` (`tag_id`)
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
  KEY `index_id` (`book_id`,`no`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `lexeme`
-- 

CREATE TABLE `lexeme` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `lexeme` varchar(31) NOT NULL default '',
  PRIMARY KEY  (`book_id`,`lexeme`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `lexeme_link`
-- 

CREATE TABLE `lexeme_link` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `count` tinyint(3) unsigned NOT NULL default '0',
  KEY `lexeme_id` (`book_id`,`no`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `metadata`
-- 

CREATE TABLE `metadata` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `name` enum('name','version','language','date','author','url') NOT NULL default 'name',
  `value` varchar(255) binary NOT NULL default '',
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
-- Table structure for table `tag`
-- 

CREATE TABLE `tag` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `tag` varchar(31) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag` (`tag`)
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
