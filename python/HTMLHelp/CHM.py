"""Microsoft Compiled HTML Help (CHM)."""


import struct

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
	
	def _enumerate(self, chm, ui, result):
		result.append(ui.path)
		
		return chmlib.CHM_ENUMERATOR_CONTINUE

	def list(self):
		result = []
		chmlib.chm_enumerate(self.chm, chmlib.CHM_ENUMERATE_NORMAL | chmlib.CHM_ENUMERATE_FILES, self._enumerate, result)
		return result
	
	def open(self, path):
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


class SystemParser:

	def __init__(self, book):
		self.book = book
	
		self.parse(book.archive.open('/#SYSTEM'))
		
	def _read(self, fp, fmt):
		fmt = '<' + fmt
		size = struct.calcsize(fmt)
		data = fp.read(size)
		if not data:
			raise IOError
		return struct.unpack(fmt, data)
	
	def parse(self, fp):
		version, = self._read(fp, 'L')

		try:
			while 1:
				code, length = self._read(fp, 'HH')
				data, = self._read(fp, '%ds' % length)
				self.handle_entry(code, data)
		except IOError:
			pass

	def handle_entry(self, code, data):
		if code == 0:
			parser = MSHH.HHCParser(self)
			parser.parse(archive.open(data))
		elif code == 1:
			parser = MSHH.HHKParser(self)
			parser.parse(archive.open(data))
		elif code == 2:
			self.book.contents.link = data
		elif code == 3:
			self.book.contents.name = data
		

class ChmBook(Book.Book):

	def __init__(self, path):
		archive = ChmArchive(path)

		Book.Book.__init__(self, archive)

		SystemParser(self)
		
		for name in self.archive.list():
			if name.lower().endswith('.hhc') and not len(self.contents):
				parser = MSHH.HHCParser(self)
				parser.parse(archive.open(name))
			elif name.lower().endswith('.hhk') and not len(self.index):
				parser = MSHH.HHKParser(self)
				parser.parse(archive.open(name))

	def list(self):
		result = []
		for name in self.archive.list():
			if name[:1] == '/' and not (name.lower().endswith('.hhc') or name.lower().endswith('.hhk')):
				result.append(name[1:])
		return result

	def resource(self, path):
		return self.archive.open('/' + path)


class ChmFactory(Book.Factory):

	def __call__(self, path):
		if self.extension(path).lower() == 'chm':
			return ChmBook(path)

		raise Book.InvalidBookError

factory = ChmFactory()


#######################################################################
# Test program


def test():
	import sys

	for arg in sys.argv[1:]:
		book = ChmBook(arg)
		print book.list()
		print book.contents
		print book.index
	

if __name__ == '__main__':
	test()
