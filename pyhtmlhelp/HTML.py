"""HTML based used interface."""


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


class HTML:

	book_factory = BookFactory()

	def __call__(self, path, query):
		head, tail = rsplit(path)
		
		if head in ('', 'index.htm', 'index.html'):
			return self.main()
		else:
			book = self.book_factory.book(head)
			
			if tail:
				return book.page(tail)
			elif query.has_key('action'):
				if query['action'][-1] == 'contents':
					return self.book_contents(book)
				else:
					return self.empty()
			else:
				return self.book_main(book)
	
	def empty(self):
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
		
	def main(self):
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

	def book_main(self, book):
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

	def tree(self, f, entries, level = 1):
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
				self.tree(f, childs, level + 1)
				f.write('\t'*(level + 1))
			f.write('</li>\n')
		f.write('\t'*level + '</ul>\n')

	def book_contents(self, book):
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

		self.tree(f, contents, level = 1)
		
		f.write('</body>\n'
			'</html>\n')
		f.seek(0)
		
		return f
