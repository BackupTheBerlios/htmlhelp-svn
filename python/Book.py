"""Classes for generic HTML help books."""


import os.path


class Error(Exception):

	pass


class InvalidBookError(Error):
		
	pass


class Entry(object):
	"""Shared by the table of contents and index entries."""

	def __init__(self, name = None, link = None):
		self.name = name
		self.link = link
	

class ContentsEntryList(list):
	pass


class ContentsEntry(Entry):
	"""Entry in a table of contents."""

	def __init__(self, name = None, link = None, children = None):
		Entry.__init__(self, name, link)
		if children is None:
			self.children = ContentsEntryList()
		else:
			self.children = children


class Contents(ContentsEntry):
	"""Book table of contents."""

	pass


class IndexEntry(Entry):
	"""An entry in the index."""
	

class Index(list):
	"""Book index."""

	pass


class SearchEntry(Entry):
	"""Search result entry."""

	pass


class Search(list):
	"""Search result.

	A list of SearchEntry objects."""

	pass


class Book(object):
	"""Abstract book class."""
	
	def __init__(self, archive):
		self.archive = archive
		self.metadata = {}
		self.contents = Contents()
		self.index = Index()
		
	title = property(lambda self: self.contents.name)

	default_link = property(lambda self: self.contents.link)
	
	def resource(self, link):
		"""Return a file-like object with the required link."""
		
		return self.archive.open(link)
	

class Factory(object):
	
	def __apply__(self, path):
		raise NotImplementedError

	def extension(self, path):
		root, ext = os.path.splitext(path)
		return ext
		

class CatalogEntry(object):

	def __init__(self, name, factory, path):
		self.name = name
		self.__factory = factory
		self.__path = path
		self.__book = None

	def open(self):
		if self.__book is None:
			self.__book = self.__factory(self.__path)
		return self.__book

	book = property(lambda self: self.open())

		
class Catalog(object):

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


