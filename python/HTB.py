"""wxWindows' HTML Help."""


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


class HTBFactory(Book.Factory):

	def __call__(self, path):
		if self.extension(path).lower() in ('htb', 'zip'):
			return HTBBook(path)

		raise Book.InvalidBookError

factory = HTBFactory()
