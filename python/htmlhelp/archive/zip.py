"""Zip archives."""


import os.path
import zipfile

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

from htmlhelp.archive.base import Archive


class ZipArchive(Archive):
	"""ZIP archive."""

	def __init__(self, path):
		try:
			self.zip = zipfile.ZipFile(path, "r")
		except zipfile.BadZipfile, msg:
			raise ValueError, msg
		except IOError, msg:
			raise ValueError, msg

	def __contains__(self, path):
		return bool(self.zip.getinfo(path))

	def __iter__(self):
		return iter(self.keys())
	
	def __getitem__(self, path):
		return StringIO(self.zip.read(path))

	def keys(self):
		return self.zip.namelist()


