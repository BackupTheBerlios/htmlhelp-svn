"""Dox Books.

See http://dox.berlios.de/ for more information about Dox."""


from __future__ import generators

import os, os.path, urlparse, xml.parsers.expat
import Book


class Parser:

	def __init__(self, book):
		self.book = book
		self.base = None
		
	def translate_link(self, link):
		if self.base is None:
			return link
		else:
			return urlparse.urljoin(self.base, link)
			
	def start_base(self, href, **dummy):
		self.base = base

	def translate_name(self, name):
		"""Trim the trailing digits."""

		while name[-1:].isdigit():
			name = name[-1:]
		return name

	def handle_element_start(self, name, attributes):
		method = 'start_' + self.translate(name)
		if hasattr(self, method):
			_attributes = {}
			for key, value in attributes.items():
				_attributes[key.encode()] = value
			apply(getattr(self, method), (), _attributes)
	
	def handle_element_end(self, name):
		method = 'end_' + self.translate(name)
		if hasattr(self, method):
			apply(getattr(self, method))
	
	def parse(self, fp):
		parser = xml.parsers.expat.ParserCreate()
		parser.StartElementHandler = self.handle_element_start
		parser.EndElementHandler = self.handle_element_end
		parser.ParseFile(fp)


class TocParser(Parser):

	def __init__(self, book):
		Parser.__init__(self, book)
		
		self.contents_stack = [book.contents]
		
	def start_dox(self, name, title, link, base = None, **dummy):
		assert len(self.contents_stack) == 1

		if base is not None:	# Must be first
			self.base = base
		
		self.book.metadata['name'] = name
		self.book.contents.name = title
		self.book.contents.link = self.translate_link(link)
	
	def start_tocsect(self, name, url, **dummy):
		assert len(self.contents_stack) > 0

		entry = Book.ContentsEntry(name, self.translate_link(url))
		self.contents_stack[-1].children.append(entry)
		self.contents_stack.append(entry)
		
	def end_tocsect(self):
		assert len(self.contents_stack) > 1
		
		self.contents_stack.pop()


class IndexParser(Parser):

	def start_function(self, name, link, **dummy):
		entry = Book.IndexEntry(name, self.translate_link(link))
		self.book.index.append(entry)
		
