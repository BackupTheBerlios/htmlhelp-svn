"""Classes for generic HTML help books."""


class ContentsNodeList(list):
	pass


class ContentsNode(object):
	"""Node in a table of contents."""

	def __init__(self, name, link, childs = None):
		self.name = name
		self.link = link
		if childs is None:
			self.childs = ContentsNodeList()
		else:
			self.childs = childs
	
	def __repr__(self):
		return '%s(%s, %s, %s)' % (self.__class__.__name__, repr(self.name), repr(self.link), repr(self.childs))


class Contents(ContentsNode):
	"""Table of contents.
	
	Is simultaneously the root node."""

	def __init__(self):
		ContentsNode.__init__(self, None, None)


class IndexEntry(object):
	"""An entry in the index."""

	# TODO: Allow sub-entries
	
	def __init__(self, term, link):
		self.term = term
		self.link = link

	def __repr__(self):
		return '%s(%s, %s)' % (self.__class__.__name__, repr(self.term), repr(self.link))
	

class Index(list):
	"""Index."""

	pass


class SearchEntry(object):
	"""Search result entry."""

	def __init__(self, name, link):
		self.name = name
		self.link = link


class Search(list):
	"""Search result.

	A list of SearchEntry objects."""

	pass


class Book(object):
	
	def __init__(self):
		self.name = None
		self.title = None
		self.default = None
		
		self.contents = Contents()
		self.index = Index()
		
	def search(self, term):
		"""Returns an object with the search results."""

		return Search()
	
	def get(self, link):
		"""Return a file-like object with the required link."""
		
		assert 0
	
	def __repr__(self):
		return '<%s: contents=%s, index=%s>' % (self.__class__.__name__, repr(self.contents), repr(self.index))


class List(list):

	pass


class Factory(object):

	def enumerate(self):
		"""Enumerate the available books."""
		
		return List()
	
	def book(self, name):
		"""Get the required book."""

		pass


class CachingFactory(Factory):

	# TODO: Implement cache aging and limiting.

	def __init__(self):
		self._enum_cache = None
		self._book_cache = {}
	
	def cache_book(self, name, book):
		self._book_cache[name] = book
	
	def enumerate_uncached(self):
		pass
		
	def enumerate(self):
		if self._enum_cache is None:
			self._enum_cache = self.enumerate_uncached()
			
		return self._enum_cache

	def book_uncached(self, name):
		pass
	
	def book(self, name):
		if self._book_cache.has_key(name):
			return self._book_cache[name]
		else:
			book = self.book_uncached(name)
			
			self._book_cache[name] = book
			return book

