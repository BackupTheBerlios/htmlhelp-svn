#!/usr/bin/python

if __name__ == "__main__":
	import cgitb
	cgitb.enable()


import cgi, os, shutil, sys

import HTML


def main():
	path = os.getenv('PATH_INFO', '')[1:]
	query = cgi.parse()

	html = HTML.HTML()
	f = html(path, query)

	sys.stdout.write('Content-Type: text/html\n')
	sys.stdout.write('\n')

	shutil.copyfileobj(f, sys.stdout)


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

