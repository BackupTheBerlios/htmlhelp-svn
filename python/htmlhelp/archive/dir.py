"""Plain directory archives support."""


import os.path

from htmlhelp.archive.base import Archive


class DirArchive(Archive):
	"""Treat directories as an archive."""

	def __init__(self, path):
		if not os.path.isdir(path):
			raise ValueError, '\'%s\' is not a directory' % path

		self.dir = os.path.abspath(path)

	def __contains__(self, path):
		return os.path.isfile(os.path.join(self.dir, path))
		
	def __iter__(self):
		return self.iterdir()

	def __getitem__(self, path):
		path = os.path.join(self.dir, path)
		try:
			return file(path, 'rb')
		except IOError:
			raise KeyError, 'could not open \'%s\'' % path
	
	def iterdir(self, head = ''):
		abshead = os.path.join(self.dir, head)
		for tail in os.listdir(abshead):
			path = os.path.join(head, tail)
			abspath = os.path.join(abshead,tail)
			if os.path.isdir(abspath):
				for path in self.iterdir(path):
					yield path
			elif os.path.isfile(abspath):
				yield path
		raise StopIteration


