#!/usr/bin/python


import warnings
warnings.filterwarnings("ignore", "tmpnam", RuntimeWarning, __name__)


def parse(book):
	import xml.dom.minidom

	dom = xml.dom.minidom.parse(book)

	book = dom.getElementsByTagName('book')[0]

	name = book.attributes['name'].value

	if 'version' in book.attributes.keys():
		name = name + '-' + book.attributes['version'].value

	return name


def install(book, output):
	import os, shutil

	tmpdir = os.tmpnam()

	os.mkdir(tmpdir)
	try:
		os.spawnvp(os.P_WAIT, 'tar', ['tar', '-xzf', book, '-C', tmpdir])

		name = parse(os.path.join(tmpdir, 'book.devhelp'))

		bookdir = os.path.join(output, 'books', name)
		spec = os.path.join(bookdir, name + '.devhelp')

		if os.path.exists(bookdir):
			shutil.rmtree(bookdir, 1)
		
		if os.path.exists(spec):
			os.unlink(spec)
		
		shutil.copytree(os.path.join(tmpdir, 'book'), bookdir)
		shutil.copy2(os.path.join(tmpdir, 'book.devhelp'), spec)
	finally:
		shutil.rmtree(tmpdir)


def usage():
	pass


def main():
	import getopt, glob, os, os.path, sys

	try:
		opts, args = getopt.getopt(sys.argv[1:], "o:", ["output="])
	except getopt.GetoptError:
		usage()
		sys.exit(2)

	output = os.path.join(os.getenv('HOME'), '.devhelp')

	for opt, arg in opts:
		if opt in ("-o", "--output"):
			output = arg

	books = []
	for arg in args:
		for book in glob.glob(arg):
			install(book, output)


if __name__ == "__main__":
	main()

