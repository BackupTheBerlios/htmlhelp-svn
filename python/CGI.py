#!/usr/bin/python


if __name__ == "__main__":
	import cgitb
	cgitb.enable()


import cgi, os, shutil, sys

try:
	from cStringIO import StringIO
except ImportError:
	from StringIO import StringIO

import Formats, HTML


class Request(HTML.Request):

	def __init__(self):
		path = os.getenv('PATH_INFO', '')[1:]
		query = cgi.parse()

		HTML.Request.__init__(self, path, query)

		self.code = 200
		self.message = None
		self.headers = {}
		self.fp = StringIO()
	
	def set_response(self, code, message = None):
		self.code = code
		self.message = message
	
	def set_header(self, name, value):
		self.headers[name] = value
	
	def write(self, data):
		self.fp.write(data)

	def finish(self):
		for name, value in self.headers.iteritems():
			sys.stdout.write('%s: %s\n' % (name, value))
		sys.stdout.write('\n')
		buf = self.fp.getvalue()
		sys.stdout.write(buf)


def main():
	catalog = Formats.catalog
	resource = HTML.CatalogResource(catalog)
	
	request = Request()
	path = request.path
	for path in request.path.split('/'):
		resource = resource.child(path)
		if resource is None:
			sys.stdout.write('Content-Type: text/html\n')
			sys.stdout.write('\n')
			sys.stdout.write(
				'<html>\n'
				'<head>\n'
				'\t<title>Error</title>\n'
				'</head>\n'
				'<body>\n'
				'\t<h1>Error</h1>\n'
				'\t<p>Not found: %s</p>\n'
				'</body>\n'
				'</html>\n' % request.path)
			return
	
	resource.render(request)


def test():
	"""Test server.

	To test from command line type:

		python -c 'import CGI;CGI.test()'

	"""
	import CGIHTTPServer
	
	class MyRequestHandler(CGIHTTPServer.CGIHTTPRequestHandler):

		def is_cgi(self):
			path = self.path

			x = '/CGI.py'
			i = len(x)
			if path[:i] == x and (not path[i:] or path[i] == '/'):
				self.cgi_info = '', path[1:]
				return 1
			return 0
	
	CGIHTTPServer.test(MyRequestHandler)
	

if __name__ == "__main__":
	main()

