"""Archive interfaces.

An archive is a collection of files, identified by their names."""


import os.path
import zipfile
import tarfile

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO


class Archive(object):
	"""Presents a dictionary-like view (so far read-only) of a file archive,
	where the keys are file names and the values are file-like objects."""

	# TODO: Add write support

	def __contains__(self, path):
		"""Whether a member with the given path is in the archive"""

		for _path in self:
			if _path == path:
				return True
		return False

	def __iter__(self):
		"""Iterate over the member file names.
		
		Must be overrided by derived classes."""
		
		return self.iterkeys()

	def __len__(self):
		count = 0
		for path in self:
			count += 1
		return count

	def __getitem__(self, path):
		"""Get a file-like object for a member in the archive.
		
		Must be overrided by derived classes."""

		raise NotImplementedError

	def __str__(self):
		return str(self.keys())
		
	def has_key(self, path):
		"""Whether a member with the given path is in the archive."""

		return path in self.keys()

	def iterkeys(self):
		"""Iterate over the member file names."""
		
		return iter(self.keys())

	def iteritems(self):
		"""Iterate over the member file names."""
		
		for name in self:
			yield name, self[name]
		
		raise StopIteration

	def keys(self):
		"""List archive contents."""
		
		return list(iter(self))
	
	def get(self, path, default = None):
		"""Get a file-like object for a member in the archive."""
		
		try:
			return self[path]
		except KeyError:
			return default


class EmptyArchive(Archive):
	"""Empty archive."""

	def __contains__(self, path):
		return False
		
	def __iter__(self):
		raise StopIteration

	def __getitem__(self, path):
		raise KeyError


class DirArchive(Archive):
	"""Plain directory archive."""

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


class FilterArchive(Archive):
	"""Archive proxy which hides unwanted files from the client and translates
	the paths."""

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
