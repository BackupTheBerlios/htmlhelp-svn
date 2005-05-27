"""Microsoft Compiled HTML Help (CHM) archives support."""


import sys

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

from htmlhelp.archive import Archive


#######################################################################
# Windows platforms


# TODO: implement the ChmArchive using istorage
# See:
#  http://bonedaddy.net/pabs3/code/#istorage
#  http://www.oreilly.com/catalog/pythonwin32/chapter/ch12.html


#######################################################################
# Chmlib SWIG bindings
	

if 1:

	import chmlib

	def _enumerate(chm, ui, result):
		assert ui.path.find('\0') == -1
	
		result.append(ui.path)
		
		return chmlib.CHM_ENUMERATOR_CONTINUE

	class ChmArchive(Archive):
		"""Compiled HTML Help (CHM) archive."""

		def __init__(self, path):
			Archive.__init__(self)
			
			self.chm = chmlib.chm_open(path)

		def __del__(self):
			chmlib.chm_close(self.chm)
		
		def __contains__(self, path):
			return path in self.keys()

		def __getitem__(self, path):
			ui = chmlib.chm_resolve_object(self.chm, path)
			if ui is None:
				raise KeyError, "missing file: %s" % path

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

		def keys(self):
			result = []
			chmlib.chm_enumerate(self.chm, chmlib.CHM_ENUMERATE_NORMAL | chmlib.CHM_ENUMERATE_FILES, _enumerate, result)
			return result
		


