"""MySQL HTML help book database."""


import sys
import gzip

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

import Plaintext


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
	# TODO: allow to specity the output fike

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


def compress(data):
	fp = StringIO()
	gz = gzip.GzipFile(None, 'wb', 9, fp)
	gz.write(data)
	gz.close()
	return fp.getvalue()


def dump_archive(book):
	for path in book.archive:
		content = book.archive[path].read()
		title, body = Plaintext.extract(path, content)

		compressed = 0
		compressed_content = compress(content)
		ratio = float(len(compressed_content))/float(len(content))
		if ratio < 0.98:
			compressed = 1
			content = compressed_content
		
		sys.stdout.write('INSERT INTO `page` (book_id, path, compressed, content, title, body) VALUES\n')
		sys.stdout.write(' (' + ',\n  '.join(quote(literal('@book_id'), path, compressed, content, title, body)) + ')')
		sys.stdout.write(';\n')

