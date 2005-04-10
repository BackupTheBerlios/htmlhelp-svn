"""wxWindows' HTML Help."""


import os.path
import zipfile

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

from htmlhelp.book import Book
from htmlhelp.archive.zip import ZipArchive
from htmlhelp.archive.filter import FilterArchive
from htmlhelp.format.mshh import HHPParser, MshhFilterArchive, Formatter


#######################################################################
# Readers


def read_htb(path):
	"""wxWindows HTML Help """

	name = os.path.splitext(os.path.basename(path))[0]

	archive = ZipArchive(path)
		
	book =  Book(name, archive)
	
	names = filter(
			lambda name: name[-4:].lower() == '.hhp',
			archive.keys())
	if not len(names):
		raise ValueError, 'no HHP file found.'
	if len(names) > 1:
		# FIXME: Actually the HTB format allows more than one project inside a zip
		raise ValueError, 'HTB with multiple books are not supported'
	hhp = names[0]

	parser = HHPParser(book)
	parser.parse(archive[hhp])
		
	book.archive = MshhFilterArchive(archive)

	return book


def read(path):
	root, ext = os.path.splitext(path)
	if ext.lower() in ('.htb', '.zip'):
		return read_htb(path)
	else:
		raise ValueError, 'unknown HTB extension \'%s\'' % ext


#######################################################################
# Writers


def write_htb(book, path, name = None):
	zip = zipfile.ZipFile(path, 'w')

	if name is None:
		# TODO: choose a better default here
		name = 'book'
		
	formatter = Formatter(book, name)
	
	fp = StringIO()
	formatter.write_hhp(fp)
	zip.writestr(name + '.hhp', fp.getvalue())
	
	fp = StringIO()
	formatter.write_hhc(fp)
	zip.writestr(name + '.hhc', fp.getvalue())
	
	fp = StringIO()
	formatter.write_hhk(fp)
	zip.writestr(name + '.hhk', fp.getvalue())
	
	for name in book.archive:
		fp = book.archive[name]
		zip.writestr(name, fp.read())


def write(book, path, name=None):
	if not path.endswith('.htb'):
		raise ValueError
	write_htb(book, path, name)


