#1/bin/sh
# delete.sh - delete a book from the database
#
# Example:
#
#  delete.sh 1234

set -e

. config.sh

(
	for BOOK_ID
	do
		echo "SET @book_id=$BOOK_ID;" 
		cat delete.sql 
	done
) | $MYSQL $DATABASE
