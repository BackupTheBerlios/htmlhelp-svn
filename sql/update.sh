#1/bin/sh
# update.sh - update a book 
#
# Example:
#
#  update.sh 1234 bookdump.sql 

set -e

. config.sh

(
	echo "SET @book_id=$1;" 
	cat delete.sql
	sed -f update.sed $2
) | $MYSQL $DATABASE
