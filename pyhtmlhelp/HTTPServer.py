#!/usr/bin/python


import cgi, os, os.path, posixpath, sys, urllib, urlparse, SimpleHTTPServer
SimpleHTTPServer

import HTML


BaseRequestHandler = SimpleHTTPServer.SimpleHTTPRequestHandler


class MyRequestHandler(BaseRequestHandler):

	root_dir = os.path.dirname(sys.argv[0])
	html = HTML.HTML()
	
	def send_head(self):
		scheme, netloc, path, query, fragment = urlparse.urlsplit(self.path)
		path = posixpath.normpath(urllib.unquote(path))
		query = cgi.parse_qs(query)
		
		head, tail = HTML.rsplit(path)

		if head == 'books':
			self.send_response(200)
			self.send_header("Content-type", "text/html") 
			self.end_headers() 
			return self.html(tail, query)
		else:
			return BaseRequestHandler.send_head(self)

	def list_directory(self, path):
		self.send_error(404, "No permission to list directory")
		return None
		
	def translate_path(self, path):
		path = posixpath.normpath(urllib.unquote(path))
		words = path.split('/')
		words = filter(None, words)
		path = self.root_dir
		for word in words:
			head, word = os.path.split(word)
			if word in (os.curdir, os.pardir): continue
			path = os.path.join(path, word)
		return path


def main():
	SimpleHTTPServer.test(MyRequestHandler)


if __name__ == "__main__":
		main()

