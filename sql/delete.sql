# Do:
#   
#  $ mysql
#  > SET @book_id=...;
#  > source delete.sql;

DELETE FROM `book` WHERE `id`=@book_id;
DELETE FROM `toc_entry` WHERE `book_id`=@book_id;
DELETE FROM `index_entry` WHERE `book_id`=@book_id;
DELETE FROM `index_link` WHERE `book_id`=@book_id;
DELETE FROM `page` WHERE `book_id`=@book_id;

