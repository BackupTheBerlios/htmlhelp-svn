"""DevHelp Books.

See http://www.imendio.com/projects/devhelp/ for more information about DevHelp."""


import os
import os.path
import urlparse
import xml.parsers.expat

import Archive
import Book
import Catalog


#######################################################################
# DevHelp XML spec parsing/formatting
#
# See http://cvs.gnome.org/lxr/source/devhelp/dtd/devhelp-1.dtd


class SpecParser:
	"""DevHelp spec file parser."""

	def __init__(self):
		self.contents = Book.Contents()
		self.contents_stack = [self.contents]
		
		self.index = Book.Index()

		self.metadata = {}

		self.base = None
		
	def translate_link(self, link):
		if self.base is None:
			return link
		else:
			return urlparse.urljoin(self.base, link)
			
	def start_book(self, name, title, link, base = None, author = None, version = None, **dummy):
		assert len(self.contents_stack) == 1

		if base is not None:	# Must be first
			self.base = base
		
		self.contents.name = title
		self.contents.link = self.translate_link(link)

		# Metadata
		self.metadata['name'] = name
		if version is not None:
			self.metadata['version'] = version
		if author is not None:
			self.metadata['author'] = author
	
	def end_book(self):
		assert len(self.contents_stack) == 1
	
	def start_chapter(self, name, link, **dummy):
		assert len(self.contents_stack) > 0

		entry = Book.ContentsEntry(name, self.translate_link(link))
		self.contents_stack[-1].append(entry)
		self.contents_stack.append(entry)
		
	def end_chapter(self):
		assert len(self.contents_stack) > 1
		
		self.contents_stack.pop()

	start_sub = start_chapter

	end_sub = end_chapter
	
	def start_function(self, name, link, **dummy):
		entry = Book.IndexEntry(name, self.translate_link(link))
		self.index.append(entry)
		
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


#######################################################################
# Archive filters


class DirDevhelpFilterArchive(Archive.FilterArchive):
	
	def filter(self, path):
		if not path.endswith('.devhelp'):
			return path
		else:
			return None
	
	translate = filter


class TgzDevhelpFilterArchive(Archive.FilterArchive):

	def filter(self, path):
		if path[:5] == 'book/':
			return path[5:]
		else:
			return None

	def translate(self, path):
		return 'book/' + path


#######################################################################
# Readers


def read_plain(path):
	"""Read a DevHelp book on a plain directory."""

	basedir, spec = os.path.split(os.path.abspath(path))
	
	archive = Archive.DirArchive(basedir)

	parser = SpecParser()
	parser.parse(file(path, 'rt'))

	book = Book.Book(
			DirDevhelpFilterArchive(archive),
			parser.contents,
			parser.index,
			parser.metadata)

	return book


def read_tgz(path):
	"""A DevHelp book in a gzip'ed tarball."""

	archive = Archive.TarArchive(path)

	parser = SpecParser()
	parser.parse(archive['book.devhelp'])

	book = Book.Book(
			TgzDevhelpFilterArchive(archive),
			parser.contents,
			parser.index,
			parser.metadata)

	return book
	

def read(path):
	"""Attempt to open a DevHelp book from the given path."""
	
	root, ext = os.path.splitext(path)
	if ext == '.devhelp':
		return read_spec(path)
	elif ext == '.tgz':
		return read_tgz(path)
	else:
		raise ValueError, 'unknown DevHelp book extension \'%s\'' % ext


factory = read


#######################################################################
# Catalog


class DevhelpCatalog(Catalog.Catalog):

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
						yield Catalog.CatalogEntry(name, RawDevhelpBook, path)
		raise StopIteration

catalog = DevhelpCatalog()
