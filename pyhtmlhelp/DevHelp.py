"""DevHelp Books.

See http://devhelp.codefactory.se/ for more information about DevHelp."""


import os, os.path, xml.parsers.expat
import Book


class SpecParser:

	def __init__(self, book):
		self.book = book
		self.contents_stack = [book.contents.childs]
		self.base = None
		
	def translate_link(self, link):
		if self.base is None:
			return link
		else:
			return urlparse.urljoin(base, link)
			
	def start_book(self, name, title, link, base = None, **dummy):
		assert len(self.contents_stack) == 1

		if base is not None:	# Must be first
			self.base = base
		
		self.book.name = name
		self.book.title = title
		self.book.default = self.translate(link)
	
	def end_book(self):
		assert len(self.contents_stack) == 1
		
	def start_sub(self, name, link, **dummy):
		assert len(self.contents_stack) > 0

		node = Book.ContentsNode(name, self.translate_link(link))
		self.contents_stack[-1].append(node)
		self.contents_stack.append(node.childs)
		
	def end_sub(self):
		assert len(self.contents_stack) > 1
		
		self.contents_stack.pop()

	def start_function(self, name, link, **dummy):
		entry = Book.IndexEntry(name, self.translate_link(link))
		self.book.index.append(entry)
		
	def handle_element_start(self, name, attributes):
		method = 'start_' + name
		if hasattr(self, method):
			_attributes = {}
			for key, value in attributes.items():
				_attributes[key.encode()] = value
			apply(getattr(self, method), (), _attributes)
	
	def handle_element_end(self, name):
		method = 'end_' + name
		if hasattr(self, method):
			apply(getattr(self, method))
	
	def parse(self, fp):
		parser = xml.parsers.expat.ParserCreate()
		parser.StartElementHandler = self.handle_element_start
		parser.EndElementHandler = self.handle_element_end
		parser.ParseFile(fp)


class DevHelpBook(Book.Book):

	pass


class UncompressedDevHelpBook(DevHelpBook):
	
	def __init__(self, spec):
		DevHelpBook.__init__(self)

		self.basedir = os.path.dirname(spec)

		parser = SpecParser(self)
		parser.parser(open(spec))
	
	def get(self, link):
		path = os.path.join(self.basedir, link)

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
