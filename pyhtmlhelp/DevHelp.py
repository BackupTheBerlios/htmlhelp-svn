"""DevHelp Books.

See http://devhelp.codefactory.se/ for more information about DevHelp."""


import os, os.path, xml.parsers.expat
import Book


class DevHelpBook(Book.Book):

	# TODO: Implement indexing.

	_title = None
	_link = None
	_base = None
	_contents = None
	_index = None
		
	def _start_book(self, title, link, base = None, **attributes):
		self._title = title
		self._link = link
		if base is not None:
			self._base = base
		
	def _start_sub(self, name, link, **attributes):
		assert len(self.__contents_stack) > 0

		childs = Book.Contents()
		entry = Book.ContentsEntry(name, link, childs)
		self.__contents_stack[-1].append(entry)
		self.__contents_stack.append(childs)
		
	def _end_sub(self):
		assert len(self.__contents_stack) > 0
		
		self.__contents_stack.pop()

	def _start_element(self, name, attributes):
		if self.__dispatch_start.has_key(name):
			_attributes = {}
			for key, value in attributes.items():
				_attributes[key.encode()] = value
			apply(self.__dispatch_start[name], (), _attributes)
	
	def _end_element(self, name):
		if self.__dispatch_end.has_key(name):
			apply(self.__dispatch_end[name])
	
	def parse_fp(self, fp, do_metadata, do_contents, do_index):
		self.__dispatch_start = {}
		self.__dispatch_end = {}
		
		if do_metadata:
			self.__dispatch_start['book'] = self._start_book

		if do_contents:
			self._contents = Book.Contents()
			self.__contents_stack = [self._contents]
			
			self.__dispatch_start['sub'] = self._start_sub
			self.__dispatch_end['sub'] = self._end_sub

		if do_index:
			self._index = Book.Index()
	
		if self.__dispatch_start or self.__dispatch_end:
			parser = xml.parsers.expat.ParserCreate()
			parser.StartElementHandler = self._start_element
			parser.EndElementHandler = self._end_element
			parser.ParseFile(fp)

		if do_contents:
			assert len(self.__contents_stack) == 1

			del self.__contents_stack

		del self.__dispatch_start, self.__dispatch_end

	def parse(self, do_metadata = 0, do_contents = 0, do_index = 0):
		"""Parse the book spec file.

		It should be overriden by the inherited classes to open the
		spec file and passe it to parse_fp()."""

		pass
	
	def title(self):
		if self._title is None or self._link == None:
			self.parse(do_metadata = 1)

		return self._title

	def link(self):
		if self._link == None:
			self.parse(do_metadata = 1)

		return self._link

	def contents(self):
		if self._contents is None:
			self.parse(do_contents = 1)
	
		return self._contents
	
	def index(self):
		if self._index is None:
			self.parse(do_index = 1)
	
		return self._index


class UncompressedDevHelpBook(DevHelpBook):
	
	def __init__(self, spec, base = None):
		self._spec = spec
		if base is None:
			self._base = os.path.dirname(spec)
		else:
			self._base = base
	
	def parse(self, do_metadata = 0, do_contents = 0, do_index = 0):
		self.parse_fp(open(self._spec), do_metadata, do_contents, do_index)
	
	def page(self, link, highlight = None):
		path = os.path.join(self._base, link)

		return open(path)


class DevHelpFactory(Book.CachingFactory):

	def __init__(self, search_path = None):
		Book.CachingFactory.__init__(self)
		
		self._search_path = [
			os.path.join(os.getenv('HOME', ''), '.devhelp2', 'books'),
			'/usr/share/gtk-doc/html']
		if search_path is not None:
			self._search_path.extend(search_path)
		
		self._path_hash = {}

	def enumerate_uncached(self):
		enum = Book.List()
		
		self._path_hash = {}
		for dir in self._search_path:
			if os.path.isdir(dir):
				for name in os.listdir(dir):
					spec = os.path.join(dir, name, name + '.devhelp')
					if os.path.isfile(spec):
						self._path_hash[name] = spec
						enum.append(name)

		return enum
		
	def book_uncached(self, name):
		if not self._path_hash.has_key(name):
			self.enumerate()

		spec = self._path_hash[name]

		return UncompressedDevHelpBook(spec)
