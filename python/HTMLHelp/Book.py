"""Classes for generic HTML help books."""


from __future__ import generators

import os.path, weakref


class Error(Exception):
	"""Common base class for all excpetions in this module."""

	pass


class InvalidBookError(Error):
	"""Attempt to open an invalid book."""
		
	pass


class ContentsEntry(list):
	"""Entry in a table of contents."""

	def __init__(self, name = None, link = None):
		list.__init__(self)
		self.name = name
		self.link = link
		self.number = None
		self.parentref = None

	def __setitem__(self, index, item):
		item.parentref = weakref.ref(self)
		item.number = index + 1
		list.__setitem__(self, index, item)
	
	def append(self, item):
		assert isinstance(item, ContentsEntry)

		item.parentref = weakref.ref(self)
		item.number = len(self)
		list.append(self, item)

	def renumber(self):
		number = 1
		for item in self:
			item.number = number
			number += 1
	
	def get_parent(self):
		if self.parentref is None:
			return None
		return self.parentref()
	
	def get_prev(self):
		parent = self.get_parent()
		if parent is None:
			return None
		index = self.number - 2
		if index < 0:
			return None
		return parent[index]
			
	def get_next(self):
		parent = self.get_parent()
		if parent is None:
			return None
		index = self.number
		if index >= len(parent):
			return None
		return parent[index]
	
	def get_children(self):
		if not len(self):
			return None	
		return self[0]
	
	parent   = property(get_parent,   doc = """Parent entry.""")
	prev     = property(get_prev,     doc = """Prev entry.""")
	next     = property(get_next,     doc = """Next entry.""")
	children = property(get_children, doc = """Sub-entries.""")


class Contents(ContentsEntry):
	"""Book table of contents."""

	pass


class IndexEntry(object):
	"""Entry in an index."""

	def __init__(self, name = None, link = None):
		self.name = name
		self.links = []

		if link is not None:
			self.links.append(link)


class Index(list):
	"""Book index."""

	pass


class Book(object):
	"""Base book class."""
	
	def __init__(self, archive):
		self.archive = archive
		self.metadata = {}
		self.contents = Contents()
		self.index = Index()
		
	title = property(
			lambda self: self.contents.name,
			doc = """Book title.""")

	default_link = property(
			lambda self: self.contents.link,
			doc = """Default link.""")
	
	def list(self):
		"""List the pages in the book."""

		return self.archive.list()
		
	def resource(self, path):
		"""Return a file-like object with the required link."""
		
		return self.archive.open(path)
	

class Factory(object):
	"""Abstract book factory."""
	
	def __apply__(self, path):
		"""Create a book instance from the given path."""
		
		raise NotImplementedError

	def extension(self, path):
		"""Utility function to determine the extension of a path."""
		
		root, ext = os.path.splitext(path)
		return ext[1:]
		

class CatalogEntry(object):

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

		
class Catalog(object):
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


