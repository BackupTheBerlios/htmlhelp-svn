"""Microsoft Compiled HTML Help (CHM) archives support."""


import sys

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

from htmlhelp.archive import Archive

# TODO: Implement the ChmArchive using istorage for Windows platforms
# See:
#  http://bonedaddy.net/pabs3/code/#istorage
#  http://www.oreilly.com/catalog/pythonwin32/chapter/ch12.html
if 1:

	# NOTE: Use PyCHM - Python bindings for CHMLIB
	# http://gnochm.sourceforge.net/pychm.html
	from chm import chmlib

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
			result, ui = chmlib.chm_resolve_object(self.chm, path)
			if result != chmlib.CHM_RESOLVE_SUCCESS:
				raise KeyError, "missing file: %s" % path

			size, buffer = chmlib.chm_retrieve_object(self.chm, ui, 0L, ui.length)
			
			if size != ui.length:
				raise IOError, "incomplete file: %s\n" % ui.path

			fp = StringIO()
			fp.write(buffer)	
			fp.seek(0)
			return fp

		def keys(self):
			result = []
			chmlib.chm_enumerate(self.chm, chmlib.CHM_ENUMERATE_NORMAL | chmlib.CHM_ENUMERATE_FILES, _enumerate, result)
			return result
		


