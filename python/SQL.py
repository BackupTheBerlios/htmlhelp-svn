"""SQL database."""


import sys


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
# title and plaintext extraction


def extract(path, content):
	"""Extract the title and plaintext version of a document."""

	# FIXME: implement this...

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

	return quote.string(u.encode(encoding))


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
	sys.stdout.write('INSERT INTO `pages` (book_id, path, content, title, plaintext) VALUES')
	paths = archive.list()
	for i in range(len(paths)):
		path = paths[i]

		content = archive.open(path).read()
		title, plaintext = extract(path, content)
		
		sys.stdout.write(i and ',\n ' or '\n ')
		sys.stdout.write('(' + ',\n  '.join(quote(literal('@book_id'), path, content, title, plaintext)) + ')')
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
