"""Microsoft HTML Help."""


import os
import re
import sys
import HTMLParser

import Archive
import Book


#######################################################################
# Parsing/formatting


class SitemapParser(HTMLParser.HTMLParser):

	def __init__(self):
		HTMLParser.HTMLParser.__init__(self)

		self.in_object = 0
		
	def error(self, message):
		lineno, offset = self.getpos()
		sys.stderr.write(message) 
		if self.lineno is not None:
			sys.stderr.write(", at line %d" % lineno)
		if self.offset is not None:
			sys.stderr.write(", column %d" % (offset + 1))
		sys.stderr.write('\n')

	def parse(self, fp):
		self.feed(fp.read())
		self.close()

	def handle_starttag(self, tag, attrs):
		attrs = dict(attrs)
		if tag == 'ul':
			self.handle_ul_start()
		elif tag == 'li':
			self.handle_li()
		elif tag == 'object':
			if attrs['type'] == 'text/sitemap':
				self.handle_object_start()
				self.in_object = 1
		elif tag == 'param':
			if self.in_object:
				self.handle_param(attrs.get('name').strip(), attrs.get('value').strip())
			
	def handle_endtag(self, tag):
		if tag == 'ul':
			self.handle_ul_end()
		elif tag == 'object':
			if self.in_object:
				self.handle_object_end()
				self.in_object = 0
				
	def handle_ul_start(self):
		pass

	def handle_li(self):
		pass
	
	def handle_object_start(self):
		pass
	
	def handle_param(self, name, value):
		pass
	
	def handle_object_end(self):
		pass

	def handle_ul_end(self):
		pass
	

class HHCParser(SitemapParser):

	def __init__(self, book):
		SitemapParser.__init__(self)

		self.contents = book.contents
		self.contents_stack = []
		
		self.entry = self.contents

	def handle_ul_start(self):
		if self.entry is None:
			self.entry = self.contents_stack[-1]
			
		self.contents_stack.append(self.entry)
		
		self.entry = None
	
	def handle_li(self):
		self.entry = None
	
	def handle_object_start(self):
		self.entry = Book.ContentsEntry(None, None)
		
	def handle_param(self, name, value):
		if name == 'Name':
			self.entry.name = value
		elif name == 'Local':
			self.entry.link = value
			
	def handle_object_end(self):
		self.contents_stack[-1].append(self.entry)

	def handle_ul_end(self):
		self.entry = self.contents_stack.pop()
		

class HHKParser(SitemapParser):

	def __init__(self, book):
		SitemapParser.__init__(self)

		self.index = book.index

		self.entry = None

	def handle_object_start(self):
		self.entry = Book.IndexEntry()
		
	def handle_param(self, name, value):
		if name == 'Name':
			if self.entry.name is None:
				self.entry.name = value
		elif name == 'Local':
			self.entry.links.append(value)
			
	def handle_object_end(self):
		self.index.append(self.entry)
		self.entry = None


class HHPParser:
	
	OPTCRE = re.compile(
		r'(?P<option>[]\-[\w_.*,(){}]+)'      # a lot of stuff found by IvL
		r'[ \t]*=[ \t]*'                      # followed by separator
		r'(?P<value>.*)$'                     # everything up to EOL
		)

	def __init__(self, book):
		self.book = book
		self.section = None
	
	def handle_section(self, name):
		self.section = name
	
	def handle_option(self, name, value):
		if self.section == 'OPTIONS':
			if name == 'Contents file':
				parser = HHCParser(self.book)
				parser.parse(self.book.archive[value])
			elif name == 'Index file':
				parser = HHKParser(self.book)
				parser.parse(self.book.archive[value])
			elif name == 'Title':
				self.book.contents.name = value
			elif name == 'Default topic':
				self.book.contents.link = value
	
	def handle_line(self, line):
		pass
	
	def parse(self, fp):
		section = None
		while 1:
			line = fp.readline()
			if not line:
				break
			
			# strip comments
			i = line.rfind(';')
			if i >= 0:
				line = line[:i]

			# strip whitespace
			line = line.rstrip()

			# is it empty?
			if line == '':
				continue

			# is it a section header?
			if line[:1] == '[' and line[-1:] == ']':
				section = line[1:-1]
				self.handle_section(section)
			# no section header in the file?
			elif section is None:
				continue
			# an option line?
			elif line.find('=') >= 0:
				optname, optval = line.split('=', 1)
				optname = optname.strip()
				optval = optval.strip()
				# allow empty values
				if optval == '""':
					optval = ''

				self.handle_option(optname, optval)
			else:
				self.handle_line(line)


########################################################################
# Archive filters


class MshhFilterArchive(Archive.FilterArchive):

	def filter(self, path):
		if path[-4:].lower() not in ('.hhp', '.hhc', '.hhk'):
			return path
		else:
			return None

	translate = filter


########################################################################
# Readers


def read_hhp(path):
	"""Uncompressed HTML Help Book."""

	basedir = os.path.dirname(os.path.abspath(path))
	archive = Archive.DirArchive(basedir)
		
	book = Book.Book(archive)
	
	parser = HHPParser(book)
	parser.parse(open(path))
		
	book.archive = MshhFilterArchive(archive)

	return book


def read(path):
	root, ext = os.path.splitext(path)
	if ext.lower() == '.hhp':
		return read_hhp(path)
	else:
		raise ValueError, 'not a HTML Help Project file'


########################################################################
# Writers



