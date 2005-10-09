DROP TABLE IF EXISTS `lexeme`;
DROP TABLE IF EXISTS `lexeme_link`;
DROP TABLE IF EXISTS `lexeme_page`;
CREATE TABLE `lexeme_page` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `lexeme` varchar(31) binary NOT NULL default '',
  `pages` blob NOT NULL,
  KEY `lexeme` (`book_id`,`lexeme`(6))
) TYPE=MyISAM;
UPDATE `version` SET `minor`=1 WHERE `major`=1 AND `minor`=0 ;
