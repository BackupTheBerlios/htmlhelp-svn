"""Book catalogs."""


import os.path, weakref


class CatalogEntry:

	def __init__(self, name, factory, path):
		self.name = name
		self.__factory = factory
		self.__path = path
		self.__book = None

	def get_book(self):
		book = self.__book
		if book is None:
			if 1:
				book = self.__factory(self.__path)
				self.__book = book
			else:
				book = self.__book()
				if book is None:
					book = self.__factory(self.__path)
					self.__book = weakref.ref(book)
		return book

	book = property(
			get_book,
			doc = """The book associated with this entry.""")

		
class Catalog:
	"""A collection of books."""

	def __contains__(self, name):
		for entry in self:
			if entry.name == name:
				return 1

		return 0

	def __getitem__(self, name):
		for entry in self:
			if entry.name == name:
				return entry

		raise KeyError
	
	def __iter__(self):
		raise NotImplementedError


