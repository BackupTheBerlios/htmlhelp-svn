#1/bin/sh
# update.sh - update a book 
#
# Example:
#
#  update.sh bookdump.sql 

EXIT=0
for BOOK
do
	sed -f `dirname $0`/update.sed $BOOK | `dirname $0`/mysql.sh || EXIT=1
done
exit $EXIT
