#1/bin/sh
# mysql.sh - mysql wrapper

set -e

. `dirname $0`/config.sh

$MYSQL -h $HOSTNAME -u $USERNAME $DATABASE "$@"
