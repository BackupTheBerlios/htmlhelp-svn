"""wxWindows' HTML Help."""


import os.path

import Archive
import Book
import MSHH


class HtbBook(MSHH.MshhBook):
	"""wxWindows HTML Help Book."""

	def __init__(self, path):
		archive = Archive.ZipArchive(path)
		
		names = filter(
				lambda name: name[-4:].lower() == '.hhp',
				archive.keys())

		# FIXME: Actually the HTB format allows more than one project inside a zip
		assert len(names) == 1
		hhp = names[0]

		MSHH.MshhBook.__init__(self, archive, hhp)
	
		self.archive = MSHH.MshhFilterArchive(archive)


def factory(path):
	root, ext = os.path.splitext(path)
	if ext.lower() in ('.htb', '.zip'):
		return HtbBook(path)
	else:
		raise ValueError, 'unknown HTB extension \'%s\'' % ext
