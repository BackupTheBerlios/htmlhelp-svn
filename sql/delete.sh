#1/bin/sh
# delete.sh - delete a book from the database
#
# Example:
#
#  delete.sh 1234

set -e

(
	for BOOK_ID
	do
		echo "SET @book_id=$BOOK_ID;" 
		cat `dirname $0`/delete.sql 
	done
) | `dirname $0`/mysql.sh
