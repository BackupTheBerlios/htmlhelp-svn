# update.sed - modify a SQL book dump to update a book instead of inserting

/^INSERT INTO `book` / {
	s/(/&`id`, /
	s/ VALUES (/&@book_id, /
}

/^SET @book_id = /d
