# update.sed - modify a SQL book dump to update a book instead of inserting

s/^INSERT INTO `book` (`alias`, `title`, `default_path`, `default_anchor`) VALUES (\('[^']*'\), \('[^']*'\), \('[^']*'\), \('[^']*'\));$/SELECT @book_id:=`id` from `book` WHERE `alias`=\1 LIMIT 1;\
UPDATE `book` SET `default_path`=\2, `default_anchor`=\3 WHERE `id`=@book_id;\
DELETE FROM `toc_entry` WHERE `book_id`=@book_id;\
DELETE FROM `index_entry` WHERE `book_id`=@book_id;\
DELETE FROM `index_link` WHERE `book_id`=@book_id;\
DELETE FROM `page` WHERE `book_id`=@book_id;/

/^SET @book_id = LAST_INSERT_ID();$/d

