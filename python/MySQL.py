"""MySQL HTML help book database."""


import sys
import HTMLParser, htmlentitydefs
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


class HTMLExtractor(HTMLParser.HTMLParser):

	# FIXME: detect the encoding from the HTML
	encoding = 'iso8859-1'

	ignore_tags = ('script',)
	indent_tags = ('dd', 'ol', 'ul')
	vspace_before_tags = ('dt', 'hr', 'pre', 'table',)
	vspace_after_tags = ('h1', 'h2', 'h3', 'h4', 'h5', 'hr', 'pre', 'table',)
	hspace_before_tags = ('td', 'th')
	hspace_after_tags = ()
	paragraph_tags = ('dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'p')
	annotation_tags = {'li': u'\xb7 ', 'br': u'\n', 'hr': u'\x2014'}
	
	def __init__(self):
		HTMLParser.HTMLParser.__init__(self)
		
		self.title = None
		self.body = None

		self.in_title = 0
		self.in_body = 0
		self.ignore = 0
		self.verbatim = 0

		self.indent = 0

		self.text_pieces = []
		
	def handle_starttag(self, tag, attrs):
		attrs = dict(attrs)

		if tag == 'title':
			self.title = u''
			self.in_title = 1
		if tag == 'body':
			self.body = u''
			self.in_body = 1
			
		if tag == 'pre':
			self.verbatim = 1
			
		if tag in self.ignore_tags:
			self.ignore += 1
		if self.ignore:
			return
		
		if tag in self.vspace_before_tags:
			self.do_verbatim(u'\n')

		if tag in self.indent_tags:
			self.indent += 1
			
		if tag in self.hspace_before_tags:
			self.do_verbatim(u'\t')

		if tag in self.paragraph_tags:
			self.do_verbatim(u' '*self.indent)

		if tag in self.annotation_tags:
			self.do_verbatim(self.annotation_tags[tag])
	
	def handle_endtag(self, tag):
		if tag == 'title':
			self.do_flush()
			self.in_title = 0
		if tag == 'body':
			self.do_flush()
			self.in_body = 0

		if tag == 'pre':
			self.verbatim = 0
		
		if tag in self.ignore_tags:
			self.ignore -= 1
		if self.ignore:
			return
		
		if tag in self.hspace_after_tags:
			self.do_verbatim(u'\t')

		if tag in self.paragraph_tags:
			self.do_verbatim(u'\n')

		if tag in self.indent_tags:
			self.indent -= 1

		if tag in self.vspace_after_tags:
			self.do_verbatim(u'\n')

	def handle_data(self, data):
		if self.ignore:
			return

		text = data.decode(self.encoding)
	
		if self.verbatim:
			self.do_verbatim(text)
		else:
			self.do_text(text)

	def handle_charref(self, name):
		text = unichr(int(name))

		self.do_text(text)
			
	def handle_entityref(self, name):
		try:
			text = unichr(htmlentitydefs.name2codepoint[name])
		except KeyError:
			return

		self.do_text(text)
			
	def do_text(self, text):
		self.text_pieces.append(text)

	def do_flush(self):
		text = u''.join(self.text_pieces)
		self.do(u' '.join(text.strip().split()))
		self.text_pieces = []
		
	def do_verbatim(self, text):
		self.do_flush()
		self.do(text)

	def do(self, text):
		if self.in_title:
			self.title += text
		if self.in_body:
			self.body += text


def extract_html(content):
	parser = HTMLExtractor()
	parser.feed(content)

	return parser.title, parser.body


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


def test_extract():
	for arg in sys.argv[1:]:
		title, body = extract_html(open(arg, "rt").read())
		print title.encode('latin-1', 'ignore')
		print
		print body.encode('latin-1', 'ignore')
		print
		print


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
	
	sys.stdout.write('INSERT INTO `books` (`title`, `default_path`, `default_anchor`) VALUES (%s, %s, %s);\n' % quote(title, default_path, default_anchor))
	sys.stdout.write('SET @book_id = LAST_INSERT_ID();\n')

	dump_contents(book.contents)
	
	dump_index(book.index)

	dump_archive(book.archive)
	

def dump_contents(contents):
	sys.stdout.write('INSERT INTO `toc` (`book_id`, `number`, `parent_number`, `name`, `path`, `anchor`) VALUES')
	dump_contents_entries(contents, 0)
	sys.stdout.write(';\n')
	
	
def dump_contents_entries(entry, parent_number, cont = 0):
	number = parent_number + 1
	for subentry in entry:
		name = subentry.name
		path, anchor = split_link(subentry.link)

		sys.stdout.write(cont and ',\n ' or '\n ')
		sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), number, parent_number, name, path, anchor)) + ')')
		cont = 1

		number = dump_contents_entries(subentry, number, cont)
	
	return number
	

def dump_index(index):
	sys.stdout.write('INSERT INTO `index` (`book_id`, `parent_id`, `term`) VALUES')
	dump_index_entries(index, 0)
	sys.stdout.write(';\n')
	
	sys.stdout.write('SET @index_id = LAST_INSERT_ID();\n')
	sys.stdout.write('UPDATE `index` SET `parent_id` = @index_id + `parent_id` - 1 WHERE `book_id` = @book_id AND `parent_id` != 0;\n')
	
	sys.stdout.write('INSERT INTO `index_links` (`index_id`, `path`, `anchor`) VALUES');
	dump_index_links(index, 0)
	sys.stdout.write(';\n')
	
	
def dump_index_entries(entry, parent_id, cont = 0):
	id = parent_id + 1
	for subentry in entry:
		sys.stdout.write(cont and ',\n ' or '\n ')
		sys.stdout.write('(' + ', '.join(quote(literal('@book_id'), parent_id, subentry.name)) + ')')
		cont = 1
				
		id = dump_index_links(subentry, id, cont)
	
	return id

def dump_index_links(entry, parent_id, cont = 0):
	id = parent_id + 1
	for subentry in entry:
		for	link in subentry.links:

			path, anchor = split_link(link)

			sys.stdout.write(cont and ',\n ' or '\n ')
			sys.stdout.write('(' + ', '.join(quote(literal('@index_id + %d - 1' % id), path, anchor)) + ')')
			cont = 1
				
		id = dump_index_links(subentry, id, cont)
	
	return id


def dump_archive(archive):
	# FIXME: collate an appropriate number of inserts in order to keep the packet
	# size into a reasonable size...
	max_allowed_packet = 1047552

	paths = archive.list()
	for i in range(len(paths)):
		path = paths[i]

		content = archive.open(path).read()
		title, body = extract(path, content)
		
		#sys.stdout.write(i and ',\n ' or '\n ')
		sys.stdout.write('INSERT INTO `pages` (book_id, path, content, title, body) VALUES')
		sys.stdout.write('(' + ',\n  '.join(quote(literal('@book_id'), path, content, title, body)) + ')')
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
