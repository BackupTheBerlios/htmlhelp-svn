#!/usr/bin/python

"""Dump a HTML Help book into a SQL language file."""


import sys

from htmlhelp.format.generic import GenericFormat
from htmlhelp.format.mysql import dump


def main():
	format = GenericFormat()

	for arg in sys.argv[1:]:
		book = format.read(arg)

		dump(book)


if __name__ == '__main__':
	main()
