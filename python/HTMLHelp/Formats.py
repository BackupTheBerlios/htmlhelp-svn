"""Generic interface to all formats."""


import DevHelp
import MSHH
import HTB
import CHM


_factories = [
	DevHelp.factory,
	MSHH.factory,
	HTB.factory,
	CHM.factory]

def factory(path):
	for factory in _factories:
		try:
			return factory(path)
		except ValueError:
			pass

	raise ValueError, 'could not find an appropriate factory to open book %s' % path
