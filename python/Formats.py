"""Generic interface to all formats."""


from __future__ import generators

import Book, DevHelp, MSHH, HTB




class GlobalFactory(Book.Factory):

	def __init__(self):
		self.__factories = []
	
	def register(self, factory):
		assert not isinstance(factory, self.__class__)

		self.__factories.append(factory)

	def unregister(self, factory):
		raise NotImplementedError

	def __apply__(self, path):
		for factory in self.__factories:
			try:
				return factory(path)
			except InvalidBookError:
				pass

		raise InvalidBookError('could not find an appropriate factory to open book %s' % path)

factory = GlobalFactory()
factory.register(DevHelp.factory)
factory.register(HTB.factory)


def GlobalCatalogIterator(self):
	for catalog in self._GlobalCatalog__catalogs:
		for entry in catalog:
			yield entry

class GlobalCatalog(Book.Catalog):

	def __init__(self):
		self.__catalogs = []
	
	def extend(self, catalog):
		assert not isinstance(catalog, self.__class__)

		self.__catalogs.append(catalog)

	def __contains__(self, name):
		for catalog in self.__catalogs:
			if name in catalog:
				return 1

		return 0

	def __getitem__(self, name):
		for catalog in self.__catalogs:
			if name in catalog:
				return catalog[name]

		raise KeyError
		
	def __iter__(self):
		return GlobalCatalogIterator(self)

catalog = GlobalCatalog()
catalog.extend(DevHelp.catalog)
