"""Microsoft HTML Help."""


import os, re, sys, HTMLParser
import Book


class HHCParser(HTMLParser.HTMLParser):

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
				assert self.node is not None
				
				node = self.node
				
			self.contents_stack.append(node)
			self.node = None
		elif tag == 'object':
			if attrs['type'] == 'text/sitemap':
				self.node = Book.ContentsNode(None, None)
			else:
				self.node = None
		elif tag == 'param':
			if self.node:
				if attrs['name'] == 'Name':
					self.node.name = attrs['value'].strip()
				elif attrs['name'] == 'Local':
					self.node.link = attrs['value']
			
	def handle_endtag(self, tag):
		if tag == 'ul':
			self.contents_stack.pop()
		elif tag == 'object':
			if self.node:
				self.contents_stack[-1].childs.append(self.node)

	def parse(self, fp):
		self.feed(fp.read())


class HHKParser(HTMLParser.HTMLParser):

	def __init__(self, book):
		HTMLParser.HTMLParser.__init__(self)

		self.book = book
		self.entry = None

	def handle_starttag(self, tag, attrs):
		attrs = dict(attrs)
		if tag == 'object':
			if attrs['type'] == 'text/sitemap':
				self.entry = Book.IndexEntry(None, None)
		elif tag == 'param':
			if self.entry:
				if attrs['name'] == 'Name':
					if self.entry.term is None:
						self.entry.term = attrs['value'].strip()
				elif attrs['name'] == 'Local':
					self.entry.link = attrs['value']
	
	def handle_endtag(self, tag):
		if tag == 'object':
			if self.entry:
				self.book.index.append(self.entry)
				self.entry = None

	def parse(self, fp):
		self.feed(fp.read())


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
		print '[%s]' % (name,)
		self.section = name
	
	def handle_option(self, name, value):
		print name+'='+ value
		if self.section == 'OPTIONS':
			if name == 'Contents file':
				parser = HHCParser(self.book)
				parser.parse(self.book.get(value))
			elif name == 'Index file':
				parser = HHKParser(self.book)
				parser.parse(self.book.get(value))
			elif name == 'Title':
				self.book.title = value
			elif name == 'Default topic':
				self.book.default = value
	
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

	pass


class UncompressedMSHHBook(MSHHBook):

	def __init__(self, hhp):
		MSHHBook.__init__(self)

		self.basedir = os.path.dirname(hhp)

		parser = HHPParser(hhp)
		parser.parse(open(hhp))
	
	def get(self, link):
		path = os.path.join(self.basedir, link)

		return open(path)


class CHMBook(MSHHBook):
	"""Windows Compiled HTML Help."""
	
	# TODO: Fill in the rest.

	pass


import zipfile
try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO


class HTBBook(MSHHBook):
	"""wxWindows HTML Help Book."""

	def __init__(self, htb):
		MSHHBook.__init__(self)

		self.zip = zipfile.ZipFile(htb, "r")

		for name in self.zip.namelist():
			if name[-4:] == '.hhp':
				parser = HHPParser(self)
				parser.parse(self.get(name))
				return

		raise Exception, "project file not found"
	
	def list(self):
		return self.zip.namelist()
		
	def get(self, link):
		fp = StringIO()
		fp.write(self.zip.read(link))
		fp.seek(0)
		return fp


if __name__ == "__main__":
	import sys
	
	for arg in sys.argv:
		if arg[-4:] == '.htb':
			book = HTBBook(arg)
			print book
		elif arg[-4:] == '.hhp':
			book = UncompressedMSHHBook(arg)
			print book
