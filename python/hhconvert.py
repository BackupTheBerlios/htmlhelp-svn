#!/usr/bin/python

"""Export a HTML Help book into another format."""


import sys
import os.path

from htmlhelp.format.generic import read, write


def main():
	input = sys.argv[1]
	output = sys.argv[2]
	
	book = read(input)

	basedir, basename = os.path.split(os.path.abspath(input))
	name, ext = os.path.splitext(basename)
	
	write(book, output, name)


if __name__ == '__main__':
	main()