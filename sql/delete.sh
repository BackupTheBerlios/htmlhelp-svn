#1/bin/sh
# delete.sh - delete a book from the database
#
# Example:
#
#  delete.sh 1234

set -e

. config.sh

(
	echo "SET @book_id=$1;" 
	cat delete.sql 
) | $MYSQL $DATABASE
