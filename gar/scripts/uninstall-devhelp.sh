#!/bin/sh

OUTPUT=$HOME/.devhelp

for BOOK
do
	rm -f $OUTPUT/specs/$BOOK.devhelp
	rm -rf $OUTPUT/books/$BOOK
done
