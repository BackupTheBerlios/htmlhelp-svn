"""wxWindows' HTML Help."""


import MSHH, Archive


class HTBBook(mshh.MSHHBook):
	"""wxWindows HTML Help Book."""

	def __init__(self, path):
		archive = Archive.ZipArchive(path)
		
		names = filter(
				lambda name: name[-4:].lower() == '.hhp',
				self.zip.namelist())

		# FIXME: Actually the HTB format allows more than one project inside a zip
		assert len(names == 1)
		hhp = names[0]

		MSHHBook.__init__(self, archive, hhp)


class HTBFactory(Book.Factory):

	def __apply__(self, path):
		if self.extension(path).lower() in ('htb', 'zip'):
			return RawMSHHBook(path)

		raise Book.InvalidBookError

factory = MSHHFactory()
