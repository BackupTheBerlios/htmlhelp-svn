#!/bin/sh

./coverage.py -e
./coverage.py -x runtests.py
echo
./coverage.py -r -m `find ../htmlhelp -iname '*.py'`
