# phpMyAdmin SQL Dump
# version 2.5.3
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Oct 30, 2003 at 11:18 PM
# Server version: 4.0.16
# PHP Version: 4.3.3
# 
# Database : `htmlhelp`
# 

# --------------------------------------------------------

#
# Table structure for table `books`
#

CREATE TABLE `books` (
  `id` smallint(11) unsigned NOT NULL auto_increment,
  `title` tinytext NOT NULL,
  `default_path` varchar(255) binary NOT NULL default '',
  `default_anchor` varchar(31) binary NOT NULL default '',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `title` (`title`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `index`
#

CREATE TABLE `index` (
  `id` mediumint(11) unsigned NOT NULL auto_increment,
  `book_id` smallint(10) unsigned NOT NULL default '0',
  `parent_id` mediumint(11) unsigned NOT NULL default '0',
  `term` tinytext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`book_id`,`parent_id`),
  FULLTEXT KEY `term` (`term`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `index_links`
#

CREATE TABLE `index_links` (
  `index_id` mediumint(11) NOT NULL default '0',
  `path` varchar(255) binary NOT NULL default '',
  `anchor` varchar(31) binary NOT NULL default '',
  KEY `index_id` (`index_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `pages`
#

CREATE TABLE `pages` (
  `book_id` smallint(11) NOT NULL default '0',
  `path` varchar(255) binary NOT NULL default '0',
  `content` mediumblob NOT NULL,
  `title` text,
  `plaintext` mediumtext,
  PRIMARY KEY  (`book_id`,`path`),
  FULLTEXT KEY `fulltext` (`title`,`plaintext`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `toc`
#

CREATE TABLE `toc` (
  `book_id` int(11) unsigned NOT NULL default '0',
  `number` smallint(11) unsigned NOT NULL default '0',
  `parent_number` smallint(11) unsigned NOT NULL default '0',
  `name` text NOT NULL,
  `path` varchar(255) binary NOT NULL default '',
  `anchor` varchar(31) binary NOT NULL default '',
  PRIMARY KEY  (`book_id`,`number`),
  KEY `parent_number` (`book_id`,`parent_number`),
  KEY `path` (`book_id`,`path`)
) TYPE=MyISAM;
