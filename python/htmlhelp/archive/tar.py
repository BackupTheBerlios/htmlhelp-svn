"""Tarballs archives."""


import os.path
import tarfile

from htmlhelp.archive.base import Archive


class TarArchive(Archive):
	"""TAR-ball archive."""

	def __init__(self, path):
		self.tar = tarfile.open(path, 'r')

	def __iter__(self):
		for member in self.tar.getmembers():
			if member.isfile():
				yield member.name
		raise StopIteration

	def __getitem__(self, path):
		return self.tar.extractfile(path)

