"""Generic interface to all formats."""


import DevHelp
import MSHH
import HTB
import CHM


readers = [
	DevHelp.read,
	MSHH.read,
	HTB.read,
	CHM.read,
]


writers = [
	DevHelp.write,
]


def read(path, *args, **kargs):
	"""Read a book."""

	global readers

	for reader in readers:
		try:
			return reader(path, *args, **kargs)
		except ValueError:
			pass

	raise ValueError, 'could not read book from %s' % path


def write(book, path, *args, **kargs):
	"""Write a book."""

	global writers

	for writer in writers:
		try:
			return writer(book, path, *args, **kargs)
		except ValueError:
			pass

	raise ValueError, 'could not write to book to %s' % path

