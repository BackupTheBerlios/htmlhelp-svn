#!/usr/bin/python

import os, os.path, sys
import xml.parsers.expat

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

class Book:

	def title(self):
		pass
	
	def table_of_context(self):
		pass
	
	def index(self):
		pass
	
	def search(self, term):
		pass
	
	def page(self, link, highlight = ()):
		pass

class BookFactory:

	def enumerate(self):
		pass
	
	def book(self, name):
		pass

class CachingBookFactory(BookFactory):

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

class DevHelpBook(Book):

	# TODO: Implement indexing.

	_title = None
	_link = None
	_base = None
	_toc = None
	_index = None
	_link_prefix = None
		
	def link(self, link):

		return link

	def _start_book(self, title, link, base = None, **attributes):
		self._title = title
		self._link = link
		if base is not None:
			self._base = base
		
	def _start_sub(self, name, link, **attributes):
		toc_entry = (name, self.link(link), [])
		self.__toc_stack[-1].append(toc_entry)
		self.__toc_stack.append(toc_entry[2])
		
	def _end_sub(self):
		self.__toc_stack.pop()

	def _start_element(self, name, attributes):
		if self.__dispatch_start.has_key(name):
			_attributes = {}
			for key, value in attributes.items():
				_attributes[key.encode()] = value
			apply(self.__dispatch_start[name], (), _attributes)
	
	def _end_element(self, name):
		if self.__dispatch_end.has_key(name):
			apply(self.__dispatch_end[name])
	
	def parse_spec(self, spec, do_metadata, do_toc, do_index):
		self.__dispatch_start = {}
		self.__dispatch_end = {}
		
		if do_metadata:
			self.__dispatch_start['book'] = self._start_book

		if do_toc:
			self._toc = []
			self.__toc_stack = [self._toc]
			
			self.__dispatch_start['sub'] = self._start_sub
			self.__dispatch_end['sub'] = self._end_sub

		if do_index:
			self._index = []
	
		if self.__dispatch_start or self.__dispatch_end:
			parser = xml.parsers.expat.ParserCreate()
			parser.StartElementHandler = self._start_element
			parser.EndElementHandler = self._end_element
			parser.ParseFile(spec)

		if do_toc:
			assert len(self.__toc_stack) == 1
			del self.__toc_stack

		del self.__dispatch_start, self.__dispatch_end

	def parse(self, do_metadata = 0, do_toc = 0, do_index = 0):
		pass
	
	def title(self):
		if self._title is None or self._link == None:
			self.parse(do_metadata = 1)

		return (self._title, self.link(self._link))

	def toc(self):
		if self._toc is None:
			self.parse(do_toc = 1)
	
		return self._toc
	
	def index(self):
		if self._index is None:
			self.parse(do_index = 1)
	
		return self._index

	def search(self, term):
		return []

class UncompressedDevHelpBook(DevHelpBook):
	
	def __init__(self, spec, base = None):
		self._spec = spec
		if base is None:
			self._base = os.path.dirname(spec)
		else:
			self._base = base
	
	def parse(self, do_metadata = 0, do_toc = 0, do_index = 0):
		self.parse_spec(open(self._spec), do_metadata, do_toc, do_index)
	
	def page(self, link, highlight = None):
		path = os.path.join(self._base, link)
		return open(path)

class DevHelpBookFactory(CachingBookFactory):

	def __init__(self, search_path = None):
		CachingBookFactory.__init__(self)
		
		self._search_path = [
			os.path.join(os.getenv('HOME'), '.devhelp2', 'books'),
			'/usr/share/gtk-doc/html']
		if search_path is not None:
			self._search_path.extend(search_path)
		
		self._path_hash = {}

	def enumerate_uncached(self):
		enum = []
		
		self._path_hash = {}
		for dir in self._search_path:
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
		
		

import SimpleHTTPServer
import urllib, urlparse

BaseRequestHandler = SimpleHTTPServer.SimpleHTTPRequestHandler

import posixpath

class MyRequestHandler(BaseRequestHandler):

	root_dir = os.path.dirname(sys.argv[0])
	book_factory = DevHelpBookFactory()
	
	def send_head(self):
		#scheme, netloc, path, query, fragment = urlparse.urlsplit(self.path)
		path = posixpath.normpath(urllib.unquote(self.path))
		words = path.split('/')
		words = filter(None, words)

		if len(words) > 0 and words[0] == 'books':
			if len(words) == 1:
				return self.send_books()
			elif len(words) == 2:
				return self.send_book(words[1])
			elif words[2] == 'tree.html':
				return self.send_book_toc(words[1])
			else:
				name = words[1]
				book = self.book_factory.book(name)
				link = '/'.join(words[2:])
				return book.page(link)
		else:
			return BaseRequestHandler.send_head(self)

	def send_books(self):
		names = self.book_factory.enumerate()

		f = StringIO()
		f.write(
			'<html>\n'
			'<head>\n'
			'\t<title>DevHelp Books</title>\n'
			'</head>\n'
			'<body>\n')
		for name in names:
			f.write('\t<p><a href="%s/">%s</a></p>' % (name, name))
		f.write(
			'</body>\n'
			'</html>\n')
		
		return self.send_html(f)

	def send_book(self, name):
		book = self.book_factory.book(name)
		title, link = book.title()

		f = StringIO()
		f.write(
			'<html>\n'
			'<head>\n'
			'\t<title>%s</title>\n'
			'</head>\n'
			'\t\t<frameset cols="250,*">\n'
			'\t\t<frame src="tree.html" name="treefrm">\n'
			'\t<frame src="%s" name="mainfrm">\n'
			'\t</frameset>\n'
			'</html>\n' % (title, link))
		
		return self.send_html(f)

	def send_book_toc(self, name):
		book = self.book_factory.book(name)
		title, link = book.title()
		toc = book.toc()

		tree_open = '/icons/tree_minus.png'
		tree_closed = '/icons/tree_plus.png'
		tree_none = '/icons/tree_none.png'
		
		f = StringIO()
		f.write(
			'<html>\n'
			'<head>\n'
			'\t<title>%s</title>\n'
			'\t<link href="/styles/tree.css" type="text/css" rel="stylesheet" />\n'
			'\t<script type="text/javascript" src="/scripts/tree.js" />\n'
			'</head>\n'
			'<body>\n' % title)

		def walk_toc(nodes, level = 1):
			f.write('\t'*level)
			if level == 1:
				f.write('<ul>\n')
			else:
				f.write('<ul class="closed">\n')
			for name, link, childs in nodes:
				f.write('\t'*(level + 1))
				if childs:
					f.write('<li class="closed">')
				else:
					f.write('<li class="none">')
				f.write('<a href="%s" target="mainfrm">%s</a>' % (link, name))
				if childs:
					f.write('\n')
					walk_toc(childs, level + 1)
					f.write('\t'*(level + 1))
				f.write('</li>\n')
			f.write('\t'*level + '</ul>\n')

		walk_toc(toc)
		
		f.write('</body>\n'
			'</html>\n')
		
		return self.send_html(f)

	def send_html(self, f):
		f.seek(0)
		self.send_response(200)
		self.send_header("Content-type", "text/html") 
		self.end_headers() 
		return f

	def list_directory(self, path):
		self.send_error(404, "No permission to list directory")
		return None
		
	def translate_path(self, path):
		path = posixpath.normpath(urllib.unquote(path))
		words = path.split('/')
		words = filter(None, words)
		path = self.root_dir
		for word in words:
			head, word = os.path.split(word)
			if word in (os.curdir, os.pardir): continue
			path = os.path.join(path, word)
		return path
def main():
	SimpleHTTPServer.test(MyRequestHandler)

if __name__ == "__main__":
		main()

