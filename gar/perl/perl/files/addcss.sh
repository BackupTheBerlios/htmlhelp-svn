#!/bin/sh
# addcss.sh - add CSS links to a HTML tree
#
#   addcss.sh css/style.css html/
#

walkdir () {
	local DIR CSS

	CSS="$1"
	DIR="$2"

	find "$DIR" -type f -mindepth 1 -maxdepth 1 -name '*.html' | xargs -r sed -i -e "s:</head:<link rel=\"stylesheet\" type=\"text/css\" href=\"$CSS\">&:i"

	find "$DIR" -type d -mindepth 1 -maxdepth 1 | while read SUBDIR
	do
		walkdir "../$CSS" "$SUBDIR"
	done
	
}

walkdir "$@"
