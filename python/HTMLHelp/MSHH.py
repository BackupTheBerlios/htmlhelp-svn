"""Microsoft HTML Help."""


import os, re, sys, HTMLParser

import Book, Archive


class MSHTMLParser(HTMLParser.HTMLParser):

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


class HHCParser(MSHTMLParser):

	def __init__(self, book):
		HTMLParser.HTMLParser.__init__(self)

		self.book = book
		self.contents_stack = []
		self.node = None

	def handle_starttag(self, tag, attrs):
		attrs = dict(attrs)
		if tag == 'ul':
			if len(self.contents_stack) == 0:
				node = self.book.contents
			else:
				if self.node is None:
					self.node = Book.ContentsEntry(None, None)
				
				node = self.node
				
			self.contents_stack.append(node)
			self.node = None
		elif tag == 'object':
			if attrs['type'] == 'text/sitemap':
				self.node = Book.ContentsEntry(None, None)
			else:
				self.node = None
		elif tag == 'param':
			if self.node is not None:
				if attrs['name'] == 'Name':
					self.node.name = attrs['value'].strip()
				elif attrs['name'] == 'Local':
					self.node.link = attrs['value']
			
	def handle_endtag(self, tag):
		if tag == 'ul':
			self.contents_stack.pop()
		elif tag == 'object':
			if self.node is not None:
				self.contents_stack[-1].append(self.node)


class HHKParser(MSHTMLParser):

	def __init__(self, book):
		HTMLParser.HTMLParser.__init__(self)

		self.book = book
		self.entry = None

	def handle_starttag(self, tag, attrs):
		attrs = dict(attrs)
		if tag == 'object':
			if attrs['type'] == 'text/sitemap':
				self.entry = Book.IndexEntry(None)
		elif tag == 'param':
			if self.entry is not None:
				if attrs['name'] == 'Name':
					if self.entry.name is None:
						self.entry.name = attrs['value'].strip()
				elif attrs['name'] == 'Local':
					self.entry.links.append(attrs['value'])
	
	def handle_endtag(self, tag):
		if tag == 'object':
			if self.entry is not None:
				self.book.index.append(self.entry)
				self.entry = None


class HHPParser:
	
	OPTCRE = re.compile(
		r'(?P<option>[]\-[\w_.*,(){}]+)'      # a lot of stuff found by IvL
		r'[ \t]*=[ \t]*'                      # followed by separator
		r'(?P<value>.*)$'                     # everything up to eol
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
				parser.parse(self.book.resource(value))
			elif name == 'Index file':
				parser = HHKParser(self.book)
				parser.parse(self.book.resource(value))
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


class MSHHBook(Book.Book):
	"""Microsoft HTML Help."""

	def __init__(self, archive, hhp):
		Book.Book.__init__(self, archive)

		parser = HHPParser(self)
		parser.parse(archive.open(hhp))
	

class RawMSHHBook(MSHHBook):

	def __init__(self, path):
		basedir, hhp = os.path.split(os.path.abspath(path))
		archive = Archive.DirArchive(basedir)
		
		MSHHBook.__init__(self, archive, hhp)


def factory(path):
	root, ext = os.path.splitext(path)
	if ext.lower() == '.hhp':
		return RawMSHHBook(path)
	else:
		raise Book.InvalidBookError, 'not a HTML Help Project file'
