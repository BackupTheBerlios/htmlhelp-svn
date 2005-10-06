#!/bin/sh

set -e 

PERL="$1"
VERSION="$2"
OUTPUT="$3"

HTML="$OUTPUT/book"

for FILE in `grep -l '^=[a-z]' $PERL/README.* | sed -e 's/.*\///'` 
do
	NAME=`echo $FILE | sed -e "s/README\.//"`
	rm -f $PERL/pod/perl$NAME.pod
	ln -s ../$FILE $PERL/pod/perl$NAME.pod
done

perl $PERL/installhtml \
      --podroot=$PERL --podpath=. --recurse \
      --htmldir=$HTML \
      --htmlroot=/ \
      --splithead=pod/perlipc \
      --splititem=pod/perlfunc \
      --libpods=perlfunc:perlguts:perlvar:perlrun:perlop \
      --verbose

# Spec file
exec 1> $OUTPUT/book.devhelp
cd $HTML
echo '<?xml version="1.0" encoding="utf-8" standalone="no"?>'
echo "<book xmlns=\"http://www.devhelp.net/book\" title=\"Perl Documentation\" link=\"pod/perl.html\" name=\"perl\" version=\"$VERSION\">"
echo '<chapters>'
echo "<sub name=\"PODs\">"
find pod -maxdepth 1 -name '*.html' -printf '%p %f\n' | while read LINK NAME
do
	NAME=`echo $NAME | sed -e 's@\.html$@@'`
	echo "<sub name=\"$NAME\" link=\"$LINK\"/>"
done
echo '</sub>'
echo "<sub name=\"Modules\">"
find lib -name '*.html' -printf '%p %P\n' | while read LINK NAME
do
	NAME=`echo $NAME | sed -e 's@/@::@g' -e 's@\.html$@@'`
	echo "<sub name=\"$NAME\" link=\"$LINK\"/>"
done
echo '</sub>'
echo '</chapters>'
echo '<functions>'
find pod -name '*.html' -printf '%p %f\n' | while read LINK NAME
do
	NAME=`echo $NAME | sed -e 's@\.html$@@'`
	echo "<function name=\"$NAME\" link=\"$LINK\"/>"
done
find lib -name '*.html' -printf '%p %P\n' | while read LINK NAME
do
	NAME=`echo $NAME | sed -e 's@/@::@g' -e 's@\.html$@@'`
	echo "<function name=\"$NAME\" link=\"$LINK\"/>"
done
echo '</functions>'
echo '</book>'

exec
