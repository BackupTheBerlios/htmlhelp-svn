#!/usr/bin/python

import sys
import os
import formatter
import htmllib
import string

output = sys.stdout

name = ''
version = ''
title = ''
link = ''

nodes = {}
functions = []

class Node:
	parents = []
	childs = []
	title = ''
	done = 0

	def __init__(self, path):
		self.path = path

		nodes[path] = self
		
	def parse(self):
		parser = HtmlParser(formatter.NullFormatter())
		parser.node = self
		parser.feed(open(self.path).read())
		parser.close()
	
	def write(self):
		global output
		
		if not self.done:
			self.done = 1
			print self.path + ', ' + str(len(self.parents))
			if len(self.parents) <= 1:
				output.write('<sub name="' + self.title + '" link="' + self.path + '">\n')
				for child in self.childs:
					child.write()
				output.write('</sub>\n')
		

class HtmlParser(htmllib.HTMLParser):

	def anchor_bgn(self, href, name, type):
		import os.path, string

		href = string.split(href, '#')[0]
		if not href:
			return
			
		path = os.path.join(os.path.dirname(self.node.path), href)
		if os.path.isdir(path):
			path = os.path.join(path, 'index.html')
		path = os.path.normpath(path)
			
		print href + ' -> ' + path
		if os.path.exists(path):
			if not nodes.has_key(path):
				child = Node(path)
				self.node.childs.append(child)
				#child.parents.append(self)
				child.parse()
			else:
				child = nodes[path]
				if child != self:
					if child not in self.node.childs:
						self.node.childs.append(child)
				#	if self not in child.parents:
				#		child.parents.append(self)
					
	
def convert(path):
	root = Node(path)
	root.parse()
	
	global link, name, output, title, version

	output.write('<?xml version="1.0"?>\n\n')

	output.write('<book ')
	if name:
		output.write('name="' + name + '" ')
	if version:
		output.write('version="' + version + '" ')
	if title:
		output.write('title="' + title + '" ')
	if link:
		output.write('link="' + link + '" ')
	output.write('>\n\n')

	root.write()
	print nodes

	output.write('</book>\n')


def usage():
	pass

def main():
	import getopt, sys

	global link, name, output, title, version

	try:
		opts, args = getopt.getopt(sys.argv[1:], "l:n:o:t:v:", ["link=", "name=", "output=", "title=", "version="])
	except getopt.GetoptError:
		usage()
		sys.exit(2)

	for opt, arg in opts:
		if opt in ("-l", "--link"):
			link = arg
		elif opt in ("-n", "--name"):
			name = arg
		elif opt in ("-o", "--output"):
			output = open(arg, 'w')
		elif opt in ("-t", "--title"):
			title = arg
		elif opt in ("-t", "--version"):
			version = arg

	convert(args[0])

	
if __name__ == '__main__' :
	main()

