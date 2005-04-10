"""Microsoft Compiled HTML Help (CHM) archives support."""


import sys

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

from htmlhelp.archive import Archive


#######################################################################
# Windows platforms

if sys.platform.startswith('win'):
	
	import pythoncom
	from win32com import storagecon

	class ChmArchive(Archive):
		"""Compiled HTML Help (CHM) archive.

        This class is an adaptor for the chmlib bindings."""

		def __init__(self, path):
			# FIXME: implement the ChmArchive using istorage
			# See:
			#  http://bonedaddy.net/pabs3/code/#istorage
			#  http://www.oreilly.com/catalog/pythonwin32/chapter/ch12.html
			raise NotImplementedError
			
			if not pythoncom.StgIsStorageFile(path):
				print "The file is not a storage file!"
				raise ValueError, "The file is not a storage file!"
			
			#flags = storagecon.STGM_READ | storagecon.STGM_SHARE_EXCLUSIVE
			flags = storagecon.STGM_READ | storagecon.STGM_SHARE_DENY_WRITE
			self.stg = pythoncom.StgOpenStorage(path, None, flags, None, 0)

		def __del__(self):
			pass

		def __getitem__(self, path):
			fp = StringIO()

			flags = STGM_READ|STGM_SHARE_EXCLUSIVE
			stg = self.stg.OpenStream(path, None, flags, 0)
		
			fp.write(stg.read())

			fp.seek(0)
			
			return fp

		def __iter__(self):
			return iter(self.keys())
			
		def keys(self):
			result = []
			enum = self.stg.EnumElements(0, None, 0)
			while enum is not None:
				print dir(enum)

				enum = enum.Next()
			return result


#######################################################################
# Chmlib SWIG bindings
	
else:

	import chmlib

	class ChmArchive(Archive):
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


