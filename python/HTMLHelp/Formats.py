"""Generic interface to all formats."""


import Book, DevHelp, MSHH, HTB, CHM


_factories = [
	DevHelp.factory,
	MSHH.factory,
	HTB.factory,
	CHM.factory]

def factory(path):
	for factory in _factories:
		try:
			return factory(path)
		except Book.InvalidBookError:
			pass

	raise Book.InvalidBookError('could not find an appropriate factory to open book %s' % path)
