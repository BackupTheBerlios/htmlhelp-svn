"""Archive interface."""


import os, os.path, zipfile


class Archive(object):

	def __init__(self, path):
		self.path = path

	def list(self):
		raise NotImplementedError
		
	def open(self, path):
		raise NotImplementedError


class DirArchive(Archive):

	def list(self):
		# FIXME: implement this
		
		return os.listdir(self.path)

	def open(self, path):
		return open(os.path.join(self.path, path))


class ZipArchive(Archive):

	def __init__(self, path):
		Archive.__init__(self, path)
		
		self.zip = zipfile.ZipFile(path, "r")

	def list(self):
		return self.zip.namelist()

	def open(self, path):
		fp = StringIO()
		fp.write(self.zip.read(path))
		fp.seek(0)
		return fp
	

