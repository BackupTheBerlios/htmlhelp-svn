"""Title and body plaintext extraction."""


import htmlentitydefs
import mimetypes
import posixpath
import re


def normalize_space(s):
	"""Normalize whitespace."""

	return ' '.join(s.split())


_html_entity_re = re.compile(r'&(?:([a-zA-Z][-.a-zA-Z0-9]*)|#(?:([0-9]+)|[xX]([0-9a-fA-F]+)));?')

def html_entity_decode(s, encoding = 'iso-8859-1'):
	r = []

	p = 0
	mo = _html_entity_re.search(s, p)
	while mo:
		r.append(s[p:mo.start()].decode(encoding))
		
		i = mo.lastindex
		e = mo.group(i)
		try:
			if i == 1:
				c = htmlentitydefs.name2codepoint[e]
			elif i == 2:
				c = int(e)
			elif i == 3:
				c = int(e, 16)
			else:
				assert 0
			r.append(unichr(c))
		except KeyError:
			r.append(mo.group(0))

		p = mo.end()
		mo = _html_entity_re.search(s, p)
	r.append(s[p:].decode(encoding))
	
	return u''.join(r)


_html_title_re = re.compile(r'<title(?:\s.*?)?>(.*?)</title>', re.IGNORECASE | re.DOTALL)
_html_body_re = re.compile(r'<body(?:\s.*?)?>(.*?)</body>', re.IGNORECASE | re.DOTALL)
_html_tag_re = re.compile(r'<.*?>', re.DOTALL)

def extract_html(content):

	mo = _html_title_re.search(content)
	if mo:
		title = normalize_space(html_entity_decode(mo.group(1)))
	else:
		title = None

	mo = _html_body_re.search(content)
	if mo:
		body = normalize_space(html_entity_decode(_html_tag_re.sub(' ', mo.group(1))))
	else:
		body = None
	
	return title, body


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

