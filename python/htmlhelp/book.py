"""Classes for generic HTML help books."""

__docformat__ = 'epytext'


import weakref

from htmlhelp.archive.dummy import DummyArchive


class ContentsEntry(list):
	"""Entry in a table of contents.
	
	It presents a list-like interface but it also provides references to the
	neighbour entries.
	
	@type name: unicode
	@ivar name: name of the entry
	@ivar link: link of the entry
	"""

	# XXX: This is probably over-complicated, as it is unlikely that anything
	# besides a top-down tree trasversal should be necessary.

	def __init__(self, name=None, link=None):
		list.__init__(self)
		self.name = name
		self.link = link
		self.number = None
		self.parentref = None

	def __setitem__(self, index, item):
		item.parentref = weakref.ref(self)
		item.number = index + 1
		list.__setitem__(self, index, item)
	
	def __str__(self):
		lines = [str(self.name) + '\t' + str(self.link)]
		for child in self:
			lines.extend([' ' + line for line in str(child).split('\n')])
		return '\n'.join(lines)
	
	def append(self, item):
		"""Append a new child entry."""
		
		assert isinstance(item, ContentsEntry)

		item.parentref = weakref.ref(self)
		item.number = len(self)
		list.append(self, item)

	def renumber(self):
		"""Renumber the children.  
		
		It should be called whenever the chidren are removed/inserted, but it is
		not necessary when appended and replacing children."""

		# XXX: This should actually be called for the insert and remove methods.
		
		number = 1
		for item in self:
			item.number = number
			number += 1
	
	def get_parent(self):
		"""Get the parent entry."""

		if self.parentref is None:
			return None
		return self.parentref()
	
	def get_prev(self):
		"""Get the previous sibling."""

		parent = self.get_parent()
		if parent is None:
			return None
		index = self.number - 2
		if index < 0:
			return None
		return parent[index]
			
	def get_next(self):
		"""Get the next sibling."""

		parent = self.get_parent()
		if parent is None:
			return None
		index = self.number
		if index >= len(parent):
			return None
		return parent[index]
	
	def get_children(self):
		"""Get the first child."""

		if not len(self):
			return None	
		return self[0]
	
	parent   = property(get_parent,   doc="""Parent entry.""")
	prev     = property(get_prev,     doc="""Prev entry.""")
	next     = property(get_next,     doc="""Next entry.""")
	children = property(get_children, doc="""Sub-entries.""")


class Contents(ContentsEntry):
	"""Book table of contents.
	
	The full contents is itself an node - the root node."""

	pass


class IndexEntry:
	"""Entry in an index.
	
	@type name: unicode
	@ivar name: entry name
	@type links: list
	@ivar links: list of links for this entry
	"""

	def __init__(self, name=None, link=None):
		self.name = name

		if link is not None:
			self.links = [link]
		else:
			self.links = []

	def __cmp__(self, other):
		return cmp(self.name, other.name)

	def __hash__(self):
		return hash(self.name)

	def __str__(self):
		return str(self.name) + '\t' + ' '.join([str(link) for link in self.links])


class Index:
	"""Book index.

	It is a mixture between a dictionary and a list where entries are kept sorted
	and entries duplicate terms are merged together."""

	def __init__(self):
		self.__dict = {}
		self.__list = []
	
	def __len__(self):
		"""Number of entries in the index."""

		assert len(self.__list) == len(self.__dict)

		return len(self.__list)
	
	def __iter__(self):
		"""Iterate over the entries."""

		return iter(self.__list)

	def __contains__(self, term):
		"""Whether a term is in the index."""

		return term in self.__dict
	
	def __getitem__(self, term):
		return self.__dict[term]

	def __str__(self):
		return '\n'.join([str(term) for term in self.__list])
	
	def append(self, entry):
		"""Append an entry.
		
		@type entry: L<IndexEntry>
		"""

		if entry.name in self.__dict:
			self.__dict[entry.name].links.extend(entry.links)
		else:
			self.__dict[entry.name] = entry
			self.__list.append(entry)
			self.__list.sort()


class Book:
	"""Generic HTML Help book.
	
	@ivar name: Name of the book.
	@type archive: L{htmlhelp.archive.base.Archive}
	@ivar archive: Archive with the HTML files, pictures, etc.
	@type contents: L{Contents}
	@ivar contents: Table of Contents.
	@type index: L{Index}
	@ivar index: Index.
	"""
	
	def __init__(self, name=None, archive=None, contents=None, index=None, 
			metadata=None):
		self.name = name
		
		if archive is None:
			self.archive = DummyArchive()
		else:
			self.archive = archive

		if contents is None:
			self.contents = Contents()
		else:
			self.contents = contents
			
		if index is None:
			self.index = Index()
		else:
			self.index = index

		if metadata is None:
			self.metadata = {}
		else:
			self.metadata = metadata
		
	def get_title(self):
		"""Get the book title, which is the name in the contents root entry."""

		return self.contents.name

	title = property(get_title, doc="""Book title.""")

	def get_default_link(self):
		"""Get the book title, which is the link in the contents root entry."""

		return self.contents.link

	default_link = property(get_default_link, doc="""Default link.""")
	
	def list(self):
		"""List the pages in the book."""

		return self.archive.keys()
		
	def resource(self, path):
		"""Return a file-like object with the required link."""
		
		return self.archive[path]

