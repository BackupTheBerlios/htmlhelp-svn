#!/usr/bin/python

"""Dump a HTML Help book into a SQL language file."""


import sys

from htmlhelp.format.generic import read
from htmlhelp.format.mysql import dump


def main():
	for arg in sys.argv[1:]:
		book = read(arg)

		dump(book)


if __name__ == '__main__':
	main()
