"""Classes for generic HTML help books."""


from __future__ import generators

import os.path, weakref


class Error(Exception):
	"""Common base class for all excpetions in this module."""

	pass


class InvalidBookError(Error):
	"""Attempt to open an invalid book."""
		
	pass


def EntryListIterator(parent):
	# Iterator for the subentries in a table of contents / index entry.

	child = parent._children_head()
	while child:
		yield child
		child = child._next_sibling()


class EntryList(object):
	# List-alike wrapper object for the subentries in a table of contents / index
	# entry.

	def __init__(self, parent):
		self._parent = parent

	def __nonzero__(self):
		return self._parent._children_tail is not None

	def __len__(self):
		n = 0
		child = self._parent._children_head()
		while child:
			n += 1
			child = child._next_sibling()
		return n

	def append(self, child):
		parent = self._parent
		
		if parent._children_tail is None:
			parent._children_tail = child
			parent._children_head = weakref.ref(child)
		else:
			parent._children_tail._next_sibling = weakref.ref(child)
			child._prev_sibling = parent._children_tail
			parent._children_tail = child
			
		child._parent = weakref.ref(parent)

	def __iter__(self):
		return EntryListIterator(self._parent)


class ContentsEntry(object):
	"""Entry in a table of contents."""

	def __init__(self, name = None, link = None):
		self.name = name
		self.link = link

		self._parent = lambda: None
		self._prev_sibling = None
		self._next_sibling = lambda: None
		self._children_head = lambda: None
		self._children_tail = None

	parent = property(
			lambda self: self._parent(), 
			doc = """Parent entry.""")
	
	prev = property(
			lambda self: self._prev_sibling, 
			doc = """Prev entry.""")
	
	next = property(
			lambda self: self._next_sibling(), 
			doc = """Next entry.""")
	
	children = property(
			lambda self: EntryList(self), 
			doc = """Sub-entries.""")


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

		self._parent = lambda: None
		self._prev_sibling = None
		self._next_sibling = lambda: None
		self._children_head = lambda: None
		self._children_tail = None

	parent = property(
			lambda self: self._parent(), 
			doc = """Parent entry.""")
	
	prev = property(
			lambda self: self._prev_sibling, 
			doc = """Prev entry.""")
	
	next = property(
			lambda self: self._next_sibling(), 
			doc = """Next entry.""")
	
	children = property(
			lambda self: EntryList(self), 
			doc = """Sub-entries.""")

class Index(IndexEntry):
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
	
	def resource(self, link):
		"""Return a file-like object with the required link."""
		
		return self.archive.open(link)
	

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


