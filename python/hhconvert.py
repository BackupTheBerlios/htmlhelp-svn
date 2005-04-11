#!/usr/bin/python

"""Export a HTML Help book into another format."""


import sys
import os.path

from htmlhelp.format.generic import GenericFormat


def main():
	format = GenericFormat()

	input = sys.argv[1]
	output = sys.argv[2]
	
	book = format.read(input)

	format.write(book, output)


if __name__ == '__main__':
	main()
