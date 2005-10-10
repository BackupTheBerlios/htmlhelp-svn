CREATE TABLE IF NOT EXISTS `lexeme_page` (
  `book_id` smallint(5) unsigned NOT NULL default '0',
  `lexeme` varchar(31) binary NOT NULL default '',
  `pages` blob NOT NULL,
  KEY `lexeme` (`book_id`, `lexeme`(6))
) TYPE=MyISAM;

DROP TABLE IF EXISTS `lexeme`;
DROP TABLE IF EXISTS `lexeme_link`;

UPDATE `version` 
SET `minor`=1 
WHERE `major`=1 AND `minor`=0;

--
--
--

ALTER TABLE `metadata` 
DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `book_id` , `name` );

ALTER TABLE `metadata` 
DROP INDEX `name`,
ADD INDEX `name` (`name`, `value`(5));

DELETE
FROM book_alias
WHERE alias = book_id;

DELETE
FROM `book_tag`
WHERE book_name IS NOT NULL;

ALTER TABLE `book_tag` 
DROP INDEX `book_name`;

ALTER TABLE `book_tag` 
CHANGE `book_name` `book_id` SMALLINT(5) UNSIGNED NOT NULL;

ALTER TABLE `book_tag` 
DROP INDEX `tag_id` ,
ADD PRIMARY KEY ( `tag_id` , `book_id` );

UPDATE `version` 
SET `minor`=2 
WHERE `major`=1 AND `minor`=1;
