# phpMyAdmin SQL Dump
# version 2.5.4
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Nov 16, 2003 at 12:19 AM
# Server version: 4.0.16
# PHP Version: 4.3.3
# 
# Database : `htmlhelp`
# 

# --------------------------------------------------------

#
# Table structure for table `book`
#

CREATE TABLE `book` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `default_path` varchar(255) binary NOT NULL default '',
  `default_anchor` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`(7))
) TYPE=MyISAM AUTO_INCREMENT=3 ;

# --------------------------------------------------------

#
# Table structure for table `index_entry`
#

CREATE TABLE `index_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `term` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`book_id`,`no`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `index_link`
#

CREATE TABLE `index_link` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `path` varchar(255) binary NOT NULL default '',
  `anchor` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`book_id`,`no`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `page`
#

CREATE TABLE `page` (
  `book_id` smallint(5) NOT NULL default '0',
  `path` varchar(255) binary NOT NULL default '0',
  `content` mediumblob NOT NULL,
  `title` text,
  `body` mediumtext,
  PRIMARY KEY  (`book_id`,`path`),
  FULLTEXT KEY `fulltext` (`title`,`body`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `toc_entry`
#

CREATE TABLE `toc_entry` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `parent_no` smallint(5) unsigned NOT NULL default '0',
  `no` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `path` varchar(255) binary NOT NULL default '',
  `anchor` varchar(255) binary NOT NULL default '',
  PRIMARY KEY  (`book_id`,`parent_no`,`no`),
  KEY `link` (`book_id`,`path`(31),`anchor`(7))
) TYPE=MyISAM;
