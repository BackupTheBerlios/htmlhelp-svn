"""Generic interface to all formats."""


from htmlhelp.format import devhelp, mshh, htb, chm


readers = [
	devhelp.read,
	mshh.read,
	htb.read,
	chm.read,
]


writers = [
	devhelp.write,
	htb.write,
	chm.write,
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

