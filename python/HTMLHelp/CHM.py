"""Microsoft Compiled HTML Help (CHM)."""


import os.path, struct

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

import chmlib
import Book, Archive, MSHH


class ChmArchive(Archive.Archive):
	"""Compiled HTML Help (CHM) archive."""

	def __init__(self, path):
		self.chm = chmlib.chm_open(path)

	def __del__(self):
		chmlib.chm_close(self.chm)
	
	def __contains__(self, path):
		return path in self.keys()

	def __getitem__(self, path):
		ui = chmlib.chm_resolve_object(self.chm, path)

		fp = StringIO()

		offset = 0L
		remain = ui.length
		while remain:
			buffer = chmlib.chm_retrieve_object(self.chm, ui, offset, 32768)
			if buffer:
				fp.write(buffer)	
				offset += len(buffer)
				remain -= len(buffer)
			else:
				raise IOError, "incomplete file: %s\n" % ui.path

		fp.seek(0)
		return fp

	def __iter__(self):
		return iter(self.keys())
		
	def keys(self):
		result = []
		chmlib.chm_enumerate(self.chm, chmlib.CHM_ENUMERATE_NORMAL | chmlib.CHM_ENUMERATE_FILES, self.enumerate, result)
		return result
	
	def enumerate(self, chm, ui, result):
		result.append(ui.path)
		
		return chmlib.CHM_ENUMERATOR_CONTINUE


class SystemParser:

	def __init__(self, book):
		self.book = book
	
		self.parse(self.book.archive['/#SYSTEM'])
		
	def read(self, fp, fmt):
		fmt = '<' + fmt
		size = struct.calcsize(fmt)
		data = fp.read(size)
		if not data:
			raise IOError
		return struct.unpack(fmt, data)
	
	def parse(self, fp):
		version, = self.read(fp, 'L')

		try:
			while 1:
				code, length = self.read(fp, 'HH')
				data, = self.read(fp, '%ds' % length)
				data = data.rstrip('\0')
				self.handle_entry(code, data)
		except IOError:
			pass

	def handle_entry(self, code, data):
		if code == 0:
			parser = MSHH.HHCParser(self)
			parser.parse(self.book.archive[data])
		elif code == 1:
			parser = MSHH.HHKParser(self)
			parser.parse(self.book.archive[data])
		elif code == 2:
			self.book.contents.link = data
		elif code == 3:
			self.book.contents.name = data
		

class ChmFilterArchive(Archive.Archive):
	"""Archive proxy which hides unwanted files from the client and translates
	the paths."""

	def __init__(self, archive):
		self.archive = archive
		
	def __iter__(self):
		for path in self.archive:
			if path[:1] == '/' and not (path.lower().endswith('.hhc') or path.lower().endswith('.hhk')):
				yield path[1:]
		raise StopIteration

	def __getitem__(self, path):
		return self.archive['/' + path]


class ChmBook(Book.Book):
	"""Microsoft Compiled HTML Help (CHM) book."""

	def __init__(self, path):
		archive = ChmArchive(path)

		Book.Book.__init__(self, archive)

		SystemParser(self)
		
		for name in archive:
			if name.lower().endswith('.hhc') and not len(self.contents):
				parser = MSHH.HHCParser(self)
				parser.parse(archive[name])
			elif name.lower().endswith('.hhk') and not len(self.index):
				parser = MSHH.HHKParser(self)
				parser.parse(archive[name])

		self.archive = ChmFilterArchive(archive)


def factory(path):
	root, ext = os.path.splitext(path)
	if ext.lower() == '.chm':
		return ChmBook(path)
	else:
		raise ValueError, 'not a CHM file'


#######################################################################
# Test program


def test():
	import sys

	for arg in sys.argv[1:]:
		book = ChmBook(arg)
		print book.archive.keys()
		print book.contents
		print book.index
	

if __name__ == '__main__':
	test()
