"""MySQL HTML help book database."""


import sys
import gzip

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

from htmlhelp.plaintext import extract, fulltext_index


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


def split_link(link, page_map):
	i = link.find('#')
	if i >= 0:
		path, anchor = link[:i], link[i+1:]
	else:
		path, anchor = link, ''
	page_no = page_map.get(path, 0)
	return page_no, anchor
	

def dump(book):
	# TODO: allow to specity the output fike

	dump_book(book)


def dump_book(book):
	path_map = {}
	for path in book.archive:
		no = len(path_map) + 1
		path_map[path] = no
	
	alias = book.name
	title = book.title
	page_no, anchor  = split_link(book.default_link, path_map)
	
	sys.stdout.write('INSERT INTO `book` (`alias`, `title`, `page_no`, `anchor`) VALUES (%s, %s, %s, %s);\n' % quote(alias, title, page_no, anchor))
	sys.stdout.write('SET @book_id = LAST_INSERT_ID();\n')

	dump_contents(book, path_map)
	
	dump_index(book, path_map)

	dump_metadata(book)
	
	index = {}
	
	dump_archive(book, path_map, index)

	dump_fulltext(path_map, index)


def dump_contents(book, path_map):
	if not len(book.contents):
		return

	sys.stdout.write('INSERT INTO `toc_entry` (`book_id`, `no`, `parent_no`, `title`, `page_no`, `anchor`) VALUES')
	dump_contents_entries(book.contents, path_map, 0)
	sys.stdout.write(';\n')
	
	
def dump_contents_entries(entry, path_map, parent_number, cont = 0):
	number = parent_number + 1
	for subentry in entry:
		name = subentry.name
		page_no, anchor = subentry.link is None and ('', '') or split_link(subentry.link, path_map)
			
		sys.stdout.write(cont and ',\n ' or '\n ')
		sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), number, parent_number, name, page_no, anchor)) + ')')
		cont = 1

		number = dump_contents_entries(subentry, path_map, number, cont)
	
	return number
	

def dump_index(book, path_map):
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
	
	sys.stdout.write('INSERT INTO `index_link` (`book_id`, `no`, `page_no`, `anchor`) VALUES');
	cont = 0
	no = 0
	for entry in book.index:
		no += 1
		for	link in entry.links:
			page_no, anchor = split_link(link, path_map)

			sys.stdout.write(cont and ',\n ' or '\n ')
			sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), no, page_no, anchor)) + ')')
			cont = 1
	sys.stdout.write(';\n')


def dump_metadata(book):
	if not len(book.metadata):
		return

	sys.stdout.write('INSERT INTO `metadata` (`book_id`, `name`, `value`) VALUES')
	cont = 0
	for name, value in book.metadata.iteritems():
		sys.stdout.write(cont and ',\n ' or '\n ')
		sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), name, value)) + ')')
		cont = 1
	sys.stdout.write(';\n')

	
def compress(data):
	fp = StringIO()
	gz = gzip.GzipFile(None, 'wb', 9, fp)
	gz.write(data)
	gz.close()
	return fp.getvalue()


def dump_archive(book, path_map, index):
	for path in book.archive:
		no = path_map[path]

		content = book.archive[path].read()
		title, body = extract(path, content)

		if body is not None:
			fulltext_index(index, no, body)

		compressed = 0
		if content:
			compressed_content = compress(content)
			ratio = float(len(compressed_content))/float(len(content))
			if ratio < 0.98:
				compressed = 1
				content = compressed_content
		
		sys.stdout.write('INSERT INTO `page` (book_id, no, path, compressed, content, title) VALUES\n')
		sys.stdout.write(' (' + ',\n  '.join(quote(literal('@book_id'), no, path, compressed, content, title)) + ')')
		sys.stdout.write(';\n')


def dump_fulltext(path_map, index):
	if not index:
		return
	word_map = {}
	sys.stdout.write('INSERT INTO `lexeme` (book_id, no, string) VALUES')
	cont = 0
	for word in index:
		no = len(word_map) + 1
		word_map[word] = no
		sys.stdout.write(cont and ',\n ' or '\n ')
		sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), no, word)) + ')')
		cont = 1
	sys.stdout.write(';\n')
		
	sys.stdout.write('INSERT INTO `lexeme_link` (book_id, lexeme_no, page_no, count) VALUES')
	cont = 0
	for word, pages in index.iteritems():
		no = word_map[word]
		for page_no, count in pages.iteritems():
			sys.stdout.write(cont and ',\n ' or '\n ')
			sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), no, page_no, min(count, 255))) + ')')
			cont = 1
	sys.stdout.write(';\n')
