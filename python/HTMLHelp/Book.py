"""Classes for generic HTML help books."""


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

	def __cmp__(self, other):
		return cmp(self.name, other.name)

	def __hash__(self):
		return hash(self.name)


class Index(object):
	"""Book index.

	It is a mix between a dictionary and a list where entries are kept sorted and
	those with duplicate terms are merged together."""

	def __init__(self):
		self.__dict = {}
		self.__list = []
	
	def __len__(self):
		assert len(self.__list) == len(self.__dict)

		return len(self.__list)
	
	def __iter__(self):
		return iter(self.__list)

	def __contains__(self, term):
		return term in self.__dict
	
	def __getitem__(self, term):
		return self.__dict[term]

	def append(self, entry):
		"""Append an entry."""

		if entry.name in self.__dict:
			self.__dict[entry.name].links.extend(entry.links)
		else:
			self.__dict[entry.name] = entry
			self.__list.append(entry)
			self.__list.sort()


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
