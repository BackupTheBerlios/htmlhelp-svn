#!/bin/sh

HHC='"C:\Program Files\HTML Help Workshop\hhc.exe"'
OUTPUT=batch-hhc.cmd

cd ~/projects/devhelp/books/gnu

echo '@echo off' > $OUTPUT

find * -name '*.hhp' -maxdepth 1 | while read FILE
do
	echo $FILE

	echo cd `dirname $FILE` >> $OUTPUT
	echo $HHC `basename $FILE` >> $OUTPUT
	echo cd .. >> $OUTPUT
done

