

if 0:
	import MySQLdb


	connection = MySQLdb.connect(db = 'htmlhelp')

	cursor = connection.cursor()

	cursor.execute('DESCRIBE book')
	print cursor.fetchall()
			
	connection.close()

"""HTML based used interface."""


import os.path, posixpath, mimetypes

import Book


def guess_type(path):
        base, ext = posixpath.splitext(path)
	if ext in mimetypes.types_map:
		return mimetypes.types_map[ext]
	else:
		return 'application/octet-stream'

def export(book, connection = None):

	print "INSERT INTO books (name, toc_id, index_id) VALUES (%s, %s, %s);", (name.encode('utf-8'), toc_id, index_id)
	book_id = 1

	name = 'test'
	toc_id = export_contents(connection, book.contents, 1)
	#index_id = export_contents(connection, book.index, 1)
	index_id = 0
	
	export_archive(connection, book.archive, book_id)
	

def export_contents(connection, entry, parent_id):

	print "INSERT INTO toc (parent_id, number, name, link) VALUES (%s, %s, %s)", (parent_id, entry.name.encode('utf-8'), entry.link.encode('utf-8'))
	id = parent_id + 1
	
	for child in entry.children:
		number += 1
		export_contents(connection, child, id)
	
	return id
	
def export_index(connection, entry, parent_id):

	print "INSERT INTO index (parent_id, name) VALUES (%s, %s)", (parent_id, entry.name.encode('utf-8'))
	id = parent_id + 1

	for link in entry.links:
		print "INSERT INTO index_links VALUES (%s, %s)", (id, link.encode('utf-8'))
		
	for child in entry.children:
		export_index(connection, child, id)
	
	return id

def export_archive(connection, archive, book_id):

	for path in archive.list:
		data = archive.open(path).read()
		print "INSERT INTO pages (book_id, path, data) VALUES (%s, %s, %s)", (book_id, path.encode('utf-8'), "...")


def main():
	import sys
	import HTB
	
	for arg in sys.argv[1:]:
		try:
			book = HTB.factory(arg)
		except:
			raise

		export(book)
	


if __name__ == '__main__':
	main()
