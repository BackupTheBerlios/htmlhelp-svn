"""SQL database."""

if 0:
	import MySQLdb


	connection = MySQLdb.connect(db = 'htmlhelp')

	cursor = connection.cursor()

	cursor.execute('DESCRIBE book')
	print cursor.fetchall()
			
	connection.close()

import os.path, posixpath, mimetypes

import Book


def guess_type(path):
	base, ext = posixpath.splitext(path)
	if ext in mimetypes.types_map:
		return mimetypes.types_map[ext]
	else:
		return 'application/octet-stream'


def quote(str):
	return repr(str)

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
	parent_id = 0
	id = parent_id + 1
	for entry in contents:
		id = dump_contents_entry(entry, id, parent_id)
	print ";"

	print "UPDATE `toc` SET `parent_id` = LAST_INSERT_ID() + `parent_id` - 1 WHERE `book_id` = @book_id AND `parent_id` != 1;"
	
	
def dump_contents_entry(entry, id, parent_id):
	print "  (@book_id, %d, %d, %s, %s)," % (parent_id, entry.number, quote(entry.name.encode('utf-8')), quote(entry.link.encode('utf-8')))
	parent_id = id
	id = parent_id + 1
	for subentry in entry:
		id = dump_contents_entry(subentry, id, parent_id)
	
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
	print "INSERT INTO `pages` (book_id, path, data) VALUES"
	for path in archive.list():
		data = archive.open(path).read()
		print "  (@book_id, %s, %s)," % (quote(path.encode('utf-8')), quote(data))
	print ";"


def main():
	import sys
	from DevHelp import factory
	
	for arg in sys.argv[1:]:
		try:
			book = factory(arg)
		except:
			raise

		dump(book)
	


if __name__ == '__main__':
	main()
