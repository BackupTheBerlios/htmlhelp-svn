#!/usr/bin/python


import os, os.path, posixpath, sys, urllib, urlparse, SimpleHTTPServer
SimpleHTTPServer

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

import Book, DevHelp


BaseRequestHandler = SimpleHTTPServer.SimpleHTTPRequestHandler


class MyRequestHandler(BaseRequestHandler):

	root_dir = os.path.dirname(sys.argv[0])
	book_factory = DevHelp.DevHelpFactory()
	
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
				return self.send_book_contents(words[1])
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
		title = book.title()
		link = book.link()

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

	def send_book_contents(self, name):
		book = self.book_factory.book(name)
		title = book.title()
		link = book.link()
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
			'<body>\n' % title)

		def walk_contents(contents, level = 1):
			f.write('\t'*level)
			if level == 1:
				f.write('<ul>\n')
			else:
				f.write('<ul class="closed">\n')
			for entry in contents:
				childs = entry.childs()
				f.write('\t'*(level + 1))
				if childs:
					f.write('<li class="closed">')
				else:
					f.write('<li class="none">')
				f.write('<a href="%s" target="mainfrm">%s</a>' % (entry.link(), entry.title()))
				if childs:
					f.write('\n')
					walk_contents(childs, level + 1)
					f.write('\t'*(level + 1))
				f.write('</li>\n')
			f.write('\t'*level + '</ul>\n')

		walk_contents(contents)
		
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

