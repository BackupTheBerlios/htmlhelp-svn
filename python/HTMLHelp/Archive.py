"""Archive interface."""


import os, os.path, zipfile, tarfile

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO


class Archive(object):
	"""Base archive class."""

	def list(self):
		"""List archive contents."""
		
		raise NotImplementedError
		
	def open(self, path):
		"""Open file in archive."""

		raise NotImplementedError


class DirArchive(Archive):
	"""Plain directory."""

	def __init__(self, path):
		self.path = os.path.abspath(path)

	def _walkdir(self, head = ''):
		result = []
		abshead = os.path.join(self.path, head)
		for tail in os.listdir(abshead):
			path = os.path.join(head, tail)
			abspath = os.path.join(abshead,tail)
			if os.path.isdir(abspath):
				result.extend(self._walkdir(path))
			elif os.path.isfile(abspath):
				result.append(path)
		return result
		
	def list(self):
		return self._walkdir()

	def open(self, path):
		return open(os.path.join(self.path, path))


class ZipArchive(Archive):
	"""ZIP archive."""

	def __init__(self, path):
		self.zip = zipfile.ZipFile(path, "r")

	def list(self):
		return self.zip.namelist()

	def open(self, path):
		fp = StringIO()
		fp.write(self.zip.read(path))
		fp.seek(0)
		return fp
	

class TarArchive(Archive):
	"""TAR archive."""

	def __init__(self, path):
		self.tar = tarfile.open(path, 'r')

	def list(self):
		result = []
		for member in self.tar.getmembers():
			if member.isfile():
				result.append(member.name)
		return result

	def open(self, path):
		return self.tar.extractfile(path)

