"""Compiled HTML Help book."""


import formatter, htmllib, os, re, sys


hhp = ''
hhc = ''
hhk = ''

name = ''
version = ''
title = ''
link = ''

skip = 0


class RootHHCParser(htmllib.HTMLParser):
	done = 0

	def end_object(self):
		if not self.done:
			self.done = 1

	def unknown_starttag(self, tag, _attributes):
		global name, link, title

		if not self.done:
			attributes = dict(_attributes)
			if tag == 'param':
				if attributes['name'] == 'Name':
					if not title:
						title = attributes['value']
				elif attributes['name'] == 'Local':
					if not name:
						name = attributes['value']
						name = name[:name.find('.')] 
					if not link:
						link = attributes['value']
					
	
class HHCParser(htmllib.HTMLParser):
	level = 0
	open = 0
	sitemap = 0

	def start_ul(self, attributes):
		self.level = self.level + 1
		self.open = 0

	def end_ul(self):
		global output, skip
		
		if self.level > skip:
			if self.open:
				output.write('\t' * self.level + '</sub>\n')
		self.level = self.level - 1
		self.open = 1

	def start_object(self, _attributes):
		global output, skip
		
		if self.level > skip:
			attributes = dict(_attributes)
			if attributes['type'] == 'text/sitemap':
				self.sitemap = 1
				if self.open:
					output.write('\t' * self.level + '</sub>\n')
				output.write('\t' * self.level +  '<sub')
				self.open = 1
	
	def end_object(self):
		global output, skip
		
		if self.sitemap:
			output.write('>\n')
			self.sitemap = 0

	def unknown_starttag(self, tag, _attributes):
		global name, link, title, output, skip

		if self.sitemap:
			attributes = dict(_attributes)
			if tag == 'param':
				if attributes['name'] == 'Name':
					output.write(' name="' + attributes['value'] + '"')
				elif attributes['name'] == 'Local':
					output.write(' link="' + attributes['value'] + '"')


class HHKParser(htmllib.HTMLParser):
	sitemap = 0
	name = 0

	def start_object(self, _attributes):
		global output
		
		attributes = dict(_attributes)
		if attributes['type'] == 'text/sitemap':
			self.sitemap = 1
			output.write('\t<function')
			self.name = 0
	
	def end_object(self):
		global output
		
		if self.sitemap:
			output.write('/>\n')
			self.sitemap = 0

	def unknown_starttag(self, tag, _attributes):
		global output
		
		if self.sitemap:
			attributes = dict(_attributes)
			if tag == 'param':
				if attributes['name'] == 'Name' and not self.name:
					output.write(' name="' + attributes['value'] + '"')
					self.name = 1
				elif attributes['name'] == 'Local':
					output.write(' link="' + attributes['value'] + '"')


def parse_hhp():
	global hhp, hhc, hhk, link, name, output, title

	# Regular expressions for parsing section headers and options.
	SECTCRE = re.compile(
		r'\['                                 # [
		r'(?P<header>[^]]+)'                  # very permissive!
		r'\]'                                 # ]
		)
	OPTCRE = re.compile(
		r'(?P<option>[]\-[\w_.*,(){}]+)'      # a lot of stuff found by IvL
		r'[ \t]*=[ \t]*'                      # followed by separator
		r'(?P<value>.*)$'                     # everything up to eol
		)

	fp = open(hhp)

	cursect = None
	optname = None
	while 1:
		line = fp.readline()
		if not line:
			break
		# comment or blank line?
		if line.strip() == '' or line[0] in '#;':
			continue
		if line.split()[0].lower() == 'rem' \
		   and line[0] in "rR":	  # no leading whitespace
			continue
		# a section header or option header?
		else:
			# is it a section header?
			mo = SECTCRE.match(line)
			if mo:
				sectname = mo.group('header')
				cursect = sectname
				# So sections can't start with a continuation line
				optname = None
			# no section header in the file?
			elif cursect is None:
				continue
			# an option line?
			else:
				mo = OPTCRE.match(line)
				if mo:
					optname, optval = mo.group('option', 'value')
					optname = optname.strip()
					optval = optval.strip()
					# allow empty values
					if optval == '""':
						optval = ''

					if cursect == 'OPTIONS':
						if optname == 'Contents file':
							if not hhc:
								hhc = os.path.join(os.path.dirname(hhp), optval)
						elif optname == 'Index file':
							if not hhk:
								hhk = os.path.join(os.path.dirname(hhp), optval)
						elif optname == 'Title':
							if not title:
								title = optval
						elif optname == 'Default topic':
							if not link:
								link = optval
				else:
					if cursect == 'FILES':
						pass

def parse_hhc_root():
	global hhc, skip
	
	if hhc and skip:
		parser = RootHHCParser(formatter.NullFormatter())
		parser.feed(open(hhc).read())
		parser.close()


def parse_hhc():
	global hhc, output
	
	if hhc:
		output.write('<chapters>\n')
		parser = HHCParser(formatter.NullFormatter())
		parser.feed(open(hhc).read())
		parser.close()
		output.write('</chapters>\n\n')


def parse_hhk():
	global hhk, output
	
	if hhk:
		output.write('<functions>\n')
		parser = HHKParser(formatter.NullFormatter())
		parser.feed(open(hhk).read())
		parser.close()
		output.write('</functions>\n\n')
