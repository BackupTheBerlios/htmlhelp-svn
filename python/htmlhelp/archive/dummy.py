"""Dummy archives."""


from htmlhelp.archive.base import Archive


class DummyArchive(Archive):
	"""Dummy empty archive."""

	def __contains__(self, path):
		return False
		
	def __iter__(self):
		raise StopIteration

	def __getitem__(self, path):
		raise KeyError


