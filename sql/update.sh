#1/bin/sh
# update.sh - update a book 
#
# Example:
#
#  update.sh bookdump.sql 

set -e

. `dirname $0`/config.sh

EXIT=0
for BOOK
do
	sed -f `dirname $0`/update.sed $BOOK | $MYSQL $DATABASE || EXIT=1
done
exit $EXIT
