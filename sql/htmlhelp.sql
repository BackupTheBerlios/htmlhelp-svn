# phpMyAdmin SQL Dump
# version 2.5.3-rc3
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Oct 01, 2003 at 05:37 PM
# Server version: 4.0.14
# PHP Version: 4.3.3RC3
# 
# Database : `htmlhelp`
# 

# --------------------------------------------------------

#
# Table structure for table `books`
#

CREATE TABLE `books` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` text NOT NULL,
  `default_link` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `books`
#


# --------------------------------------------------------

#
# Table structure for table `index`
#

CREATE TABLE `index` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `book_id` int(10) unsigned NOT NULL default '0',
  `parent_id` int(11) unsigned NOT NULL default '0',
  `number` int(10) unsigned NOT NULL default '0',
  `term` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`book_id`,`parent_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `index`
#


# --------------------------------------------------------

#
# Table structure for table `index_links`
#

CREATE TABLE `index_links` (
  `index_id` int(11) NOT NULL default '0',
  `link` text NOT NULL
) TYPE=MyISAM;

#
# Dumping data for table `index_links`
#


# --------------------------------------------------------

#
# Table structure for table `pages`
#

CREATE TABLE `pages` (
  `id` int(11) NOT NULL auto_increment,
  `book_id` int(11) NOT NULL default '0',
  `path` varchar(255) binary NOT NULL default '0',
  `content` mediumblob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `path` (`book_id`,`path`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `pages`
#


# --------------------------------------------------------

#
# Table structure for table `toc`
#

CREATE TABLE `toc` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `book_id` int(10) unsigned NOT NULL default '0',
  `parent_id` int(11) unsigned NOT NULL default '0',
  `number` int(10) unsigned NOT NULL default '0',
  `name` text NOT NULL,
  `link` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`book_id`,`parent_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `toc`
#

