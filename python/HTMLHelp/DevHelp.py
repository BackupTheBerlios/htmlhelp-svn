"""DevHelp Books.

See http://www.imendio.com/projects/devhelp/ for more information about DevHelp."""


import os
import os.path
import urlparse
import xml.parsers.expat

import Archive
import Book
import Catalog


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
		parser.parse(archive[spec])
	
	
class RawDevHelpFilterArchive(Archive.Archive):
	
	def filter(self, path):
		if not path.endswidth('.devhelp'):
			return path
		else:
			return None
	
	translate = filter


class RawDevHelpBook(DevHelpBook):

	def __init__(self, path):
		basedir, spec = os.path.split(os.path.abspath(path))
		archive = Archive.DirArchive(basedir)
		
		DevHelpBook.__init__(self, archive, spec)

		self.archive = RawDevHelpFilterArchive(archive)


class TgzDevHelpFilterArchive(Archive.FilterArchive):

	def filter(self, path):
		if path[:5] == 'book/':
			return path[5:]
		else:
			return None

	def translate(self, path):
		return 'book/' + path


class TgzDevHelpBook(DevHelpBook):
	"""A DevHelp book in a .tgz tarball."""

	def __init__(self, path):
		archive = Archive.TarArchive(path)

		DevHelpBook.__init__(self, archive, 'book.devhelp')
	
		self.archive = TgzDevHelpFilterArchive(archive)


def factory(path):
	"""Attempt to open a DevHelp book from the given."""
	
	root, ext = os.path.splitext(path)
	if ext == '.devhelp':
		return RawDevHelpBook(path)
	elif ext == '.tgz':
		return TgzDevHelpBook(path)
	else:
		raise ValueError, 'unknown DevHelp book extension \'%s\'' % ext


class DevHelpCatalog(Catalog.Catalog):

	def __init__(self):
		Catalog.Catalog.__init__(self)
		
		self.path = []

		if 'HOME' in os.environ:
			self.path.append(os.path.join(os.environ['HOME'], '.devhelp', 'books'))
		
		self.path.append('/usr/share/gtk-doc/html')
		self.path.append('/usr/local/share/gtk-doc/html')
	
	def __iter__(self):
		for dir in self.path:
			if os.path.isdir(dir):
				for name in os.listdir(dir):
					path = os.path.join(dir, name, name + '.devhelp')
					if os.path.isfile(path):
						yield Catalog.CatalogEntry(name, RawDevHelpBook, path)
		raise StopIteration

catalog = DevHelpCatalog()
