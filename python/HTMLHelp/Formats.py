"""Generic interface to all formats."""


import Book, DevHelp, MSHH, HTB, CHM


class GlobalFactory(Book.Factory):

	def __init__(self):
		self.__factories = []
	
	def register(self, factory):
		assert not isinstance(factory, self.__class__)

		self.__factories.append(factory)

	def unregister(self, factory):
		raise NotImplementedError

	def __call__(self, path):
		for factory in self.__factories:
			try:
				return factory(path)
			except Book.InvalidBookError:
				pass

		raise Book.InvalidBookError('could not find an appropriate factory to open book %s' % path)

factory = GlobalFactory()
factory.register(DevHelp.factory)
factory.register(MSHH.factory)
factory.register(HTB.factory)
factory.register(CHM.factory)

