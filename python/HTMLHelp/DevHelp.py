"""DevHelp Books.

See http://www.imendio.com/projects/devhelp/ for more information about DevHelp."""


import os, os.path, urlparse, xml.parsers.expat
import Book, Archive


class SpecParser:

	def __init__(self, book):
		self.book = book
		self.contents_stack = [book.contents]
		self.base = None
		
	def translate_link(self, link):
		if self.base is None:
			return link
		else:
			return urlparse.urljoin(self.base, link)
			
	def start_book(self, name, title, link, base = None, **dummy):
		assert len(self.contents_stack) == 1

		if base is not None:	# Must be first
			self.base = base
		
		self.book.metadata['name'] = name
		self.book.contents.name = title
		self.book.contents.link = self.translate_link(link)
	
	def end_book(self):
		assert len(self.contents_stack) == 1
		
	def start_sub(self, name, link, **dummy):
		assert len(self.contents_stack) > 0

		entry = Book.ContentsEntry(name, self.translate_link(link))
		self.contents_stack[-1].append(entry)
		self.contents_stack.append(entry)
		
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

	def __init__(self, archive, spec):
		Book.Book.__init__(self, archive)

		parser = SpecParser(self)
		parser.parse(archive.open(spec))
	
	
class RawDevHelpBook(DevHelpBook):

	def __init__(self, path):
		basedir, spec = os.path.split(os.path.abspath(path))
		archive = Archive.DirArchive(basedir)
		
		DevHelpBook.__init__(self, archive, spec)


class TgzDevHelpBook(DevHelpBook):

	def __init__(self, path):
		archive = Archive.TarArchive(path)

		DevHelpBook.__init__(self, archive, 'book.devhelp')
	
	def list(self):
		result = []
		for name in self.archive.list():
			if name.startswith('book/'):
				result.append(name[5:])
		return result
		
	def resource(self, path):
		return self.archive.open('book/' + path)


class DevHelpFactory(Book.Factory):

	def __call__(self, path):
		if self.extension(path) == 'devhelp':
			return RawDevHelpBook(path)
		elif self.extension(path) == 'tgz':
			return TgzDevHelpBook(path)

		raise Book.InvalidBookError

factory = DevHelpFactory()


def DevHelpCatalogIterator(self):
	for dir in self.path:
		if os.path.isdir(dir):
			for name in os.listdir(dir):
				path = os.path.join(dir, name, name + '.devhelp')
				if os.path.isfile(path):
					yield Book.CatalogEntry(name, RawDevHelpBook, path)
					
class DevHelpCatalog(Book.Catalog):

	def __init__(self):
		Book.Catalog.__init__(self)
		
		self.path = []

		if 'HOME' in os.environ:
			self.path.append(os.path.join(os.environ['HOME'], '.devhelp', 'books'))
		
		self.path.append('/usr/share/gtk-doc/html')
		self.path.append('/usr/local/share/gtk-doc/html')
	
	def __iter__(self):

		return DevHelpCatalogIterator(self)

catalog = DevHelpCatalog()
