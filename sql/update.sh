#1/bin/sh
# update.sh - update a book 
#
# Example:
#
#  update.sh bookdump.sql 

set -e

. config.sh

EXIT=0
for BOOK
do
	sed -f update.sed $BOOK | $MYSQL $DATABASE || EXIT=1
done
exit $EXIT
