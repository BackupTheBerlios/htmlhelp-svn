"""Archive filtering."""


import os.path

from htmlhelp.archive.base import Archive


class FilterArchive(Archive):
	"""Archive decorator which hides unwanted files from the client and/or
	translates the paths."""

	def __init__(self, archive):
		self.archive = archive
		
	def __iter__(self):
		for path in self.archive:
			path = self.filter(path)
			if path is not None:
				yield path
		raise StopIteration

	def __getitem__(self, path):
		path = self.translate(path)
		if path is None:
			raise KeyError, 'member file access denied'
		return self.archive[path]

	def filter(self, path):
		"""It should return name under which this file should be seen by the
		client, or None if should be hidden."""

		return path

	def translate(self, path):
		"""It should return the real of the file, or None if access should be
		denied."""
		
		return path
