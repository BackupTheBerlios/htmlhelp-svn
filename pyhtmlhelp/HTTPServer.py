#!/usr/bin/python


import cgi, os, os.path, posixpath, sys, urllib, urlparse, SimpleHTTPServer
SimpleHTTPServer

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

import Book, DevHelp


def rsplit(path):
	"""Split a pathname.  Returns tuple "(head, tail)" where "tail" is
	everything after the first slash.  Either part may be empty."""

	head, tail = '', path
	while tail and not head:
		i = tail.find('/')
		if i >= 0:
			head, tail = tail[:i], tail[i+1:]
		else:
			head, tail = tail, ''
	return head, tail


BookFactory = DevHelp.DevHelpFactory


class HTMLUI:

	def __init__(self, book_factory):
		self.book_factory = book_factory

	def render(self, path, query):
		head, tail = rsplit(path)
		
		if head in ('', 'index.htm', 'index.html'):
			return self.render_list()
		else:
			book = self.book_factory.book(head)
			
			if tail:
				return book.page(tail)
			elif query.has_key('action'):
				if query['action'][-1] == 'contents':
					return self.render_contents(book)
				else:
					return self.render_empty()
			else:
				return self.render_book(book)
	
	def render_empty(self):
		f = StringIO()
		f.write(
			'<html>\n'
			'<head>\n'
			'</head>\n'
			'<body>\n'
			'</body>\n'
			'</html>\n')
		f.seek(0)
		
		return f
		
	def render_list(self):
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
		f.seek(0)
		
		return f

	def render_book(self, book):
		f = StringIO()
		f.write(
			'<html>\n'
			'<head>\n'
			'\t<title>%s</title>\n'
			'</head>\n'
			'\t\t<frameset cols="250,*">\n'
			'\t\t<frame src="?action=contents" name="navigation">\n'
			'\t<frame src="%s" name="main">\n'
			'\t</frameset>\n'
			'</html>\n' % (book.title(), book.link()))
		f.seek(0)
		
		return f

	def render_tree(self, f, entries, level = 1):
		f.write('\t'*level)
		if level == 1:
			f.write('<ul>\n')
		else:
			f.write('<ul class="closed">\n')
		for entry in entries:
			childs = entry.childs()
			f.write('\t'*(level + 1))
			if childs:
				f.write('<li class="closed">')
			else:
				f.write('<li class="none">')
			f.write('<a href="%s" target="mainfrm">%s</a>' % (entry.link(), entry.title()))
			if childs:
				f.write('\n')
				self.render_tree(f, childs, level + 1)
				f.write('\t'*(level + 1))
			f.write('</li>\n')
		f.write('\t'*level + '</ul>\n')

	def render_contents(self, book):
		contents = book.contents()

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
			'<body>\n' % book.title())

		self.render_tree(f, contents, level = 1)
		
		f.write('</body>\n'
			'</html>\n')
		f.seek(0)
		
		return f
		
		
BaseRequestHandler = SimpleHTTPServer.SimpleHTTPRequestHandler


class MyRequestHandler(BaseRequestHandler):

	root_dir = os.path.dirname(sys.argv[0])
	book_factory = BookFactory()
	htmlui = HTMLUI(book_factory)
	
	def send_head(self):
		scheme, netloc, path, query, fragment = urlparse.urlsplit(self.path)
		path = posixpath.normpath(urllib.unquote(path))
		query = cgi.parse_qs(query)
		
		head, tail = rsplit(path)

		if head == 'books':
			self.send_response(200)
			self.send_header("Content-type", "text/html") 
			self.end_headers() 
			return self.htmlui.render(tail, query)
		else:
			return BaseRequestHandler.send_head(self)

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

