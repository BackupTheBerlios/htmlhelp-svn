"""Archive interface."""


import os, os.path, zipfile, tarfile
import Error


try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO


class ArchiveError(Error.Error):
	"""Base archive exception class."""

	pass


class InvalidArchiveError(ArchiveError):
	"""Attempt to open an invalid archive."""
	
	pass


class MissingMemberError(ArchiveError):
	"""Attempt to get a non-existing member,"""

	pass


class Archive(object):
	"""A dictionary-like view of a file archive."""

	def __iter__(self):
		return iter(self.list)

	def __getitem__(self, path):
		return self.open(path)

	def list(self):
		"""List archive contents."""
		
		raise NotImplementedError
		
	def open(self, path):
		"""Get a file-like object for a member in the archive."""

		raise NotImplementedError


class DirArchive(Archive):
	"""Plain directory."""

	def __init__(self, path):
		self.dir = os.path.abspath(path)

	def _walkdir(self, head = ''):
		result = []
		abshead = os.path.join(self.dir, head)
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
		path = os.path.join(self.dir, path)
		try:
			return open(path)
		except IOError:
			raise MissingMemberError, 'could not open \'%s\'' % path


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

