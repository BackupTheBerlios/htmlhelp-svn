"""MySQL HTML help book database."""


import sys
import htmlentitydefs
import posixpath, mimetypes


#######################################################################
# Store a book directly into a MySQL database


def store(book):
	import MySQLdb

	connection = MySQLdb.connect(db = 'htmlhelp')

	cursor = connection.cursor()

	# FIXME: fill in the rest...
	cursor.execute('...')
			
	connection.close()


#######################################################################
# title and body plaintext extraction

import re

def normalize_space(s):
	"""Normalize whitespace."""

	return ' '.join(s.split())


html_entity_re = re.compile(r'&(?:([a-zA-Z][-.a-zA-Z0-9]*)|#(?:([0-9]+)|[xX]([0-9a-fA-F]+)));?')

def html_entity_decode(s, encoding = 'iso-8859-1'):
	r = []

	p = 0
	mo = html_entity_re.search(s, p)
	while mo:
		r.append(s[p:mo.start()].decode(encoding))
		
		i = mo.lastindex
		e = mo.group(i)
		if i == 1:
			c = htmlentitydefs.name2codepoint[e]
		elif i == 2:
			c = int(e)
		elif i == 3:
			c = int(e, 16)
		else:
			assert 0
		r.append(unichr(c))

		p = mo.end()
		mo = html_entity_re.search(s, p)
	r.append(s[p:].decode(encoding))
	
	return u''.join(r)


html_title_re = re.compile(r'<title(?:\s.*?)?>(.*?)</title>', re.IGNORECASE | re.DOTALL)
html_body_re = re.compile(r'<body(?:\s.*?)?>(.*?)</body>', re.IGNORECASE | re.DOTALL)
html_tag_re = re.compile(r'<.*?>', re.DOTALL)

def extract_html(content):

	mo = html_title_re.search(content)
	if mo:
		title = normalize_space(html_entity_decode(mo.group(1)))
	else:
		title = None

	mo = html_body_re.search(content)
	if mo:
		body = normalize_space(html_entity_decode(html_tag_re.sub(' ', mo.group(1))))
	else:
		body = None
	
	return title, body


def guess_type(path):
	base, ext = posixpath.splitext(path)
	if ext in mimetypes.types_map:
		return mimetypes.types_map[ext]
	else:
		return 'application/octet-stream'
	

def extract(path, content):
	"""Extract the title and body of a document in plaintext."""

	type = guess_type(path)

	if type == 'text/html':
		return extract_html(content)
	elif type == 'text/plain':
		return None, content
	else:
		return None, None


#######################################################################
# SQL literals quoting


class literal(str):
	"""A SQL literal."""

	pass


def quote_int(i):
	"""Quote a integer literal."""

	return literal('%i' % i)


def quote_long(l):
	"""Quote a long integer literal."""

	return literal('%li' % l)


def quote_float(f):
	"""Quote a floating point literal."""

	return literal('%f' % f)


def quote_str(s):
	"""Quote a string literal."""

	s = s.replace('\\', '\\\\')
	
	s = s.replace('\0', '\\0')
	s = s.replace('\b', '\\b')
	s = s.replace('\n', '\\n')
	s = s.replace('\r', '\\r')
	s = s.replace('\t', '\\t')
	s = s.replace('\z', '\\z')

	s = s.replace("'", "\\'")
	s = s.replace('"', '\\"')

	return literal("'" + s + "'")


def quote_unicode(u, encoding = 'UTF-8'):
	"""Quote a unicode literal."""

	return quote_str(u.encode(encoding))


def quote(*args):
	"""Quote one or more literals."""
	
	result = []
	for arg in args:
		if arg is None:
			result.append('NULL')
		elif isinstance(arg, literal):
			result.append(arg)
		elif isinstance(arg, int):
			result.append(quote_int(arg))
		elif isinstance(arg, long):
			result.append(quote_long(arg))
		elif isinstance(arg, float):
			result.append(quote_float(arg))
		elif isinstance(arg, str):
			result.append(quote_str(arg))
		elif isinstance(arg, unicode):
			result.append(quote_unicode(arg))
		else:
			raise TypeError, 'unknown data type'
	if len(result) == 1:
		return result[0]
	else:
		return tuple(result)


#######################################################################
# Dump a book into a SQL language file


def split_link(link):
	i = link.find('#')
	if i >= 0:
		path, anchor = link[:i], link[i+1:]
	else:
		path, anchor = link, ''
	return path, anchor
	

def dump(book):
	dump_book(book)


def dump_book(book):
	title = book.title
	default_path, default_anchor  = split_link(book.default_link)
	
	sys.stdout.write('INSERT INTO `book` (`title`, `default_path`, `default_anchor`) VALUES (%s, %s, %s);\n' % quote(title, default_path, default_anchor))
	sys.stdout.write('SET @book_id = LAST_INSERT_ID();\n')

	dump_contents(book)
	
	dump_index(book)

	dump_archive(book)
	

def dump_contents(book):
	if not len(book.contents):
		return

	sys.stdout.write('INSERT INTO `toc_entry` (`book_id`, `no`, `parent_no`, `title`, `path`, `anchor`) VALUES')
	dump_contents_entries(book.contents, 0)
	sys.stdout.write(';\n')
	
	
def dump_contents_entries(entry, parent_number, cont = 0):
	number = parent_number + 1
	for subentry in entry:
		name = subentry.name
		path, anchor = subentry.link is None and ('', '') or split_link(subentry.link)

		sys.stdout.write(cont and ',\n ' or '\n ')
		sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), number, parent_number, name, path, anchor)) + ')')
		cont = 1

		number = dump_contents_entries(subentry, number, cont)
	
	return number
	

def dump_index(book):
	if not len(book.index):
		return

	sys.stdout.write('INSERT INTO `index_entry` (`book_id`, `no`, `term`) VALUES')
	cont = 0
	no = 0
	for entry in book.index:
		no += 1

		sys.stdout.write(cont and ',\n ' or '\n ')
		sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), no, entry.name)) + ')')
		cont = 1
	sys.stdout.write(';\n')
	
	sys.stdout.write('INSERT INTO `index_link` (`book_id`, `no`, `path`, `anchor`) VALUES');
	cont = 0
	no = 0
	for entry in book.index:
		no += 1
		for	link in entry.links:
			path, anchor = split_link(link)

			sys.stdout.write(cont and ',\n ' or '\n ')
			sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), no, path, anchor)) + ')')
			cont = 1
	sys.stdout.write(';\n')


def dump_archive(book):
	paths = book.list()
	for path in paths:
		content = book.resource(path).read()
		title, body = extract(path, content)
		
		sys.stdout.write('INSERT INTO `page` (book_id, path, content, title, body) VALUES\n')
		sys.stdout.write(' (' + ',\n  '.join(quote(literal('@book_id'), path, content, title, body)) + ')')
		sys.stdout.write(';\n')
	


#######################################################################
# Main program


def main():
	from Formats import factory
	
	for arg in sys.argv[1:]:
		try:
			book = factory(arg)
		except:
			raise

		dump(book)


if __name__ == '__main__':
	main()
