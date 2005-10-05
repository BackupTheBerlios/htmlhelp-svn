-- MySQL dump 10.9
--
-- Host: localhost    Database: htmlhelp
-- ------------------------------------------------------
-- Server version	4.1.14-Debian_6

--
-- Table structure for table `book`
--

DROP TABLE IF EXISTS `book`;
CREATE TABLE `book` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`(7))
) TYPE=MyISAM;

--
-- Table structure for table `book_alias`
--

DROP TABLE IF EXISTS `book_alias`;
CREATE TABLE `book_alias` (
  `alias` varchar(31) NOT NULL default '',
  `book_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`alias`),
  KEY `book_id` (`book_id`)
) TYPE=MyISAM;

--
-- Table structure for table `book_tag`
--

DROP TABLE IF EXISTS `book_tag`;
CREATE TABLE `book_tag` (
  `tag_id` tinyint(3) unsigned NOT NULL default '0',
  `book_name` varchar(31) binary NOT NULL default '',
  KEY `tag_id` (`tag_id`),
  KEY `book_name` (`book_name`)
) TYPE=MyISAM;

--
-- Table structure for table `index_entry`
--

DROP TABLE IF EXISTS `index_entry`;
CREATE TABLE `index_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `term` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`book_id`,`no`)
) TYPE=MyISAM;

--
-- Table structure for table `index_link`
--

DROP TABLE IF EXISTS `index_link`;
CREATE TABLE `index_link` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) binary NOT NULL default '',
  KEY `index_id` (`book_id`,`no`)
) TYPE=MyISAM;

--
-- Table structure for table `lexeme`
--

DROP TABLE IF EXISTS `lexeme`;
CREATE TABLE `lexeme` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `lexeme` varchar(31) NOT NULL default '',
  PRIMARY KEY  (`book_id`,`lexeme`)
) TYPE=MyISAM;

--
-- Table structure for table `lexeme_link`
--

DROP TABLE IF EXISTS `lexeme_link`;
CREATE TABLE `lexeme_link` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `count` tinyint(3) unsigned NOT NULL default '0',
  KEY `lexeme_id` (`book_id`,`no`)
) TYPE=MyISAM;

--
-- Table structure for table `metadata`
--

DROP TABLE IF EXISTS `metadata`;
CREATE TABLE `metadata` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `name` enum('name','version','language','date','author','url') NOT NULL default 'name',
  `value` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`name`,`book_id`),
  KEY `name` (`name`,`value`)
) TYPE=MyISAM;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE `page` (
  `book_id` smallint(5) NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `path` varchar(255) binary NOT NULL default '',
  `compressed` tinyint(1) NOT NULL default '0',
  `content` mediumblob NOT NULL,
  `title` text,
  PRIMARY KEY  (`book_id`,`no`),
  UNIQUE KEY `path` (`book_id`,`path`)
) TYPE=MyISAM;

--
-- Table structure for table `stop_word`
--

DROP TABLE IF EXISTS `stop_word`;
CREATE TABLE `stop_word` (
  `lexeme` varchar(31) NOT NULL default '',
  PRIMARY KEY  (`lexeme`)
) TYPE=MyISAM;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `tag` varchar(31) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag` (`tag`)
) TYPE=MyISAM;

--
-- Table structure for table `toc_entry`
--

DROP TABLE IF EXISTS `toc_entry`;
CREATE TABLE `toc_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `parent_no` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `page_no` smallint(5) unsigned NOT NULL default '0',
  `anchor` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`book_id`,`parent_no`,`no`)
) TYPE=MyISAM;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
CREATE TABLE `version` (
  `major` tinyint(3) unsigned NOT NULL default '0',
  `minor` tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;


