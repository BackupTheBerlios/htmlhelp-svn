"""wxWindows' HTML Help."""


import os.path
import Book, MSHH, Archive


class HTBBook(MSHH.MSHHBook):
	"""wxWindows HTML Help Book."""

	def __init__(self, path):
		archive = Archive.ZipArchive(path)
		
		names = filter(
				lambda name: name[-4:].lower() == '.hhp',
				archive.list())

		# FIXME: Actually the HTB format allows more than one project inside a zip
		assert len(names) == 1
		hhp = names[0]

		MSHH.MSHHBook.__init__(self, archive, hhp)
	
	def list(self):
		return filter(
				lambda name: name[-4:].lower() not in ('.hhp', '.hhc', '.hhk'),
				self.archive.list())


def factory(path):
	root, ext = os.path.splitext(path)
	if ext.lower() in ('.htb', '.zip'):
		return HTBBook(path)
	else:
		raise Book.InvalidBookError, 'unknown HTB extension \'%s\'' % ext
