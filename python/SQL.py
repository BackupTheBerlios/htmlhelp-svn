"""SQL database."""

if 0:
	import MySQLdb


	connection = MySQLdb.connect(db = 'htmlhelp')

	cursor = connection.cursor()

	cursor.execute('DESCRIBE book')
	print cursor.fetchall()
			
	connection.close()

import os.path, posixpath, mimetypes, sys

import Book


def guess_type(path):
	base, ext = posixpath.splitext(path)
	if ext in mimetypes.types_map:
		return mimetypes.types_map[ext]
	else:
		return 'application/octet-stream'


def quote(s):

	s = s.replace('\\', '\\\\')
	
	s = s.replace('\0', '\\0')
	s = s.replace('\b', '\\b')
	s = s.replace('\n', '\\n')
	s = s.replace('\r', '\\r')
	s = s.replace('\t', '\\t')
	s = s.replace('\z', '\\z')

	s = s.replace("'", "\\'")
	s = s.replace('"', '\\"')

	return "'" + s + "'"


#######################################################################
# Dump a book into a SQL language file


def dump(book):
	dump_book(book)


def dump_book(book):
	print "INSERT INTO `books` (`title`, `default_link`) VALUES (%s, %s);" % (quote(book.title.encode('utf-8')), quote(book.default_link.encode('utf-8')))
	print "SET @book_id = LAST_INSERT_ID();" 

	dump_contents(book.contents)
	#dump_index(book.index, 0)
	dump_archive(book.archive)
	

def dump_contents(contents):
	print "INSERT INTO `toc` (`book_id`, `parent_id`, `number`, `name`, `link`) VALUES"
	id = dump_contents_entry(contents, 0, 1)

	print "UPDATE `toc` SET `parent_id` = LAST_INSERT_ID() + `parent_id` - 1 WHERE `book_id` = @book_id AND `parent_id` != 1;"
	
	
def dump_contents_entry(entry, parent_id, last):
	id = parent_id + 1
	for subentry in entry:
		sublast = last and subentry is entry[-1]
		print "  (@book_id, %d, %d, %s, %s)%s" % (parent_id, subentry.number, quote(subentry.name.encode('utf-8')), quote(subentry.link.encode('utf-8')), (sublast and not len(subentry)) and ";" or ",")
		id = dump_contents_entry(subentry, id, sublast)
	
	return id
	

def dump_index_entry(entry, id, parent_id):

	print "INSERT INTO `index` (`parent_id`, `name`) VALUES (%s, %s)" % (parent_id, entry.name.encode('utf-8'))
	id = parent_id + 1

	for link in entry.links:
		print "INSERT INTO `index_links` VALUES (%s, %s)", (id, link.encode('utf-8'))
		
	parent_id = id
	id = parent_id + 1
	for subentry in entry:
		id = dump_index_entry(child, id, parent_id)
	
	return id


def dump_archive(archive):
	print "INSERT INTO `pages` (book_id, path, content) VALUES"
	paths = archive.list()
	for path in paths:
		last = path is paths[-1]
		content = archive.open(path).read()
		print "  (@book_id, %s, %s)%s" % (quote(path.encode('utf-8')), quote(content), last and ';' or ',')


def main():
	from HTB import factory
	
	for arg in sys.argv[1:]:
		try:
			book = factory(arg)
		except:
			raise

		dump(book)
	


if __name__ == '__main__':
	main()
