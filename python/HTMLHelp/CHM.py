"""Microsoft Compiled HTML Help (CHM)."""


import sys
import os.path
import struct

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

import Archive
import Book
import MSHH



#######################################################################
# CHM archive


if sys.platform.startswith('win'):
	import pythoncom
	from win32com import storagecon

	class ChmArchive(Archive.Archive):
		"""Compiled HTML Help (CHM) archive."""

		def __init__(self, path):
			# FIXME: implement the ChmArchive using istorage
			# See:
			#  http://bonedaddy.net/pabs3/code/#istorage
			#  http://www.oreilly.com/catalog/pythonwin32/chapter/ch12.html
			raise NotImplementedError

			flags = storagecon.STGM_READ | storagecon.STGM_SHARE_EXCLUSIVE
			#flags = storagecon.STGM_READ | storagecon.STGM_SHARE_DENY_WRITE
			self.stg = pythoncom.StgOpenStorage(path, None, flags)

		def __del__(self):
			pass

		def __iter__(self):
			return iter(self.keys())
			
		def keys(self):
			result = []
			enum = self.stg.EnumElements(0, None, 0)
			while enum is not None:

				enum = enum.Next()
			return result


else:
	import chmlib

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
			assert ui.path.find('\0') == -1

			result.append(ui.path)
			
			return chmlib.CHM_ENUMERATOR_CONTINUE


#######################################################################
# Parsing


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
		

#######################################################################
# Archive filters


class ChmFilterArchive(Archive.FilterArchive):

	def filter(self, path):
		if path[:1] == '/' and not (path.lower().endswith('.hhc') or path.lower().endswith('.hhk')):
			return path[1:]
		else:
			return None

	def translate(self, path):
		return '/' + path


#######################################################################
# Readers


def read_chm(path):
	archive = ChmArchive(path)

	book = Book.Book(archive)

	SystemParser(book)
	
	for name in archive:
		if name.lower().endswith('.hhc') and not len(book.contents):
			parser = MSHH.HHCParser(book)
			parser.parse(archive[name])
		elif name.lower().endswith('.hhk') and not len(book.index):
			parser = MSHH.HHKParser(book)
			parser.parse(archive[name])

	book.archive = ChmFilterArchive(archive)

	return book


def read(path):
	root, ext = os.path.splitext(path)
	if ext.lower() == '.chm':
		return read_chm(path)
	else:
		raise ValueError, 'not a CHM file'

