"""DevHelp Books.

See http://www.imendio.com/projects/devhelp/ for more information about DevHelp."""


import os
import os.path
import urlparse
import xml.parsers.expat
import tarfile
import time

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

import Archive
import Book
import Catalog


#######################################################################
# DevHelp XML spec parsing/formatting
#
# For format description see:
# - http://cvs.gnome.org/lxr/source/devhelp/dtd/devhelp-1.dtd
# - http://cvs.gnome.org/lxr/source/devhelp/README


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


class SpecFormatter:

	def __init__(self, fp, encoding = 'utf-8'):
		self.fp = fp
		self.encoding = encoding

		self.fp.write('<?xml version="1.0" encoding="%s"?>\n' % self.encoding)
	
	def book(self, book, name = None):
		if name is None:
			if 'name' not in book.metadata:
				raise ValueError, 'Required book name not specified.'
			name = book.metadata['name']

		self.fp.write('<book name="%s" title="%s" link="%s"' % (self.escape(name), self.escape(book.contents.name), self.escape(book.contents.link)))
		if 'version' in book.metadata:
			self.fp.write(' version="%s"' % self.escape(book.metadata['version']))
		if 'author' in book.metadata:
			self.fp.write(' author="%s"' % self.escape(book.metadata['author']))
		self.fp.write('>\n')
		self.chapters(book.contents)
		self.functions(book.index)
		self.fp.write('</book>\n')

	def chapters(self, contents):
		self.fp.write('<chapters>\n')
		self.chapter(contents)
		self.fp.write('</chapters>\n')

	def chapter(self, parent):
		for child in parent:
			self.fp.write('<sub name="%s" link="%s"' % (self.escape(child.name), self.escape(child.link)))
			if len(child):
				self.fp.write('>\n')
				self.chapter(child)
				self.fp.write('</sub>\n')
			else:
				self.fp.write('/>\n')

	def functions(self, index):
		self.fp.write('<functions>\n')
		for entry in index:
			self.function(entry)
		self.fp.write('</functions>\n')

	def function(self, entry):
		name = entry.name
		for link in entry.links:
			self.fp.write('<function name="%s" link="%s"/>\n' % (self.escape(name), self.escape(link)))

	def escape(self, s):
		"""Helper to add special character quoting."""
		
		if s is None:
			return ''
		
		s = s.replace("&", "&amp;") # Must be first

		if isinstance(s, unicode):
			s = s.encode(self.encoding, 'xmlcharrefreplace')

		s = s.replace("<", "&lt;")
		s = s.replace(">", "&gt;")
		s = s.replace("'", "&apos;")
		s = s.replace('"', "&quot;")

		return s


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


def read_spec(path):
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


#######################################################################
# Writers


def _addfile(tar, name, fp):
	tarinfo = tarfile.TarInfo(name)
	
	fp.seek(0, 2)
	tarinfo.size = fp.tell()
	fp.seek(0)
	
	tarinfo.mtime = time.time()
	
	tar.addfile(tarinfo, fp)
	

def write_tgz(book, path, name = None):

	tar = tarfile.open(path, "w:gz")

	fp = StringIO()
	formatter = SpecFormatter(fp)
	formatter.book(book, name)
	_addfile(tar, 'book.devhelp', fp)

	for name in book.archive:
		fp = book.archive[name]
		_addfile(tar, 'book/' + name, fp)
	

def write(book, path, name=None):
	if not path.endswith('.tgz'):
		raise ValueError
	write_tgz(book, path, name=None)


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
