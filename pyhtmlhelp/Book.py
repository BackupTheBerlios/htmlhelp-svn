"""Classes for generic HTML help books."""


class Entry:
	"""General book entry."""

	def __init__(self, title, link):
		self._title = title
		self._link = link
		
	def title(self):
		return self._title
	
	def link(self):
		return self._link


class ContentsEntry(Entry):
	"""Contents entry."""

	def __init__(self, title, link, childs = ()):
		Entry.__init__(self, title, link)

		self._childs = childs
		
	def childs(self):
		return self._childs


class Contents(list):
	"""Book contents.

	A list of ContentsEntry objects."""
	
	pass


class IndexEntry(Entry):
	"""Index entry."""

	pass


class Index:
	"""Book index.

	A list of IndexEntry objects."""

	pass


class SearchEntry(Entry):
	"""Search result entry."""

	pass


class Search(list):
	"""Search result.

	A list of SearchEntry objects."""

	pass


class Book:

	def title(self):
		"""Returns a string with the book title."""
		
		assert 0
	
	def link(self):
		"""Returns the relative link of the default topic."""

		assert 0
		
	def contents(self):
		"""Returns an object describing the book table of contents."""

		return Contents()
	
	def index(self):
		"""Returns an object describing the book index."""

		return Index()
	
	def search(self, term):
		"""Returns an object with the search results."""

		return Search()
	
	def page(self, link, highlight = ()):
		"""Return a file-like object with the required page."""
		
		assert 0


class List(list):

	pass


class Factory:

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

