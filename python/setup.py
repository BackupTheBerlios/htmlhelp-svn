#!/usr/bin/python
# -*- coding: iso8859-1 -*-

from distutils.core import setup, Extension
 
chmlib = Extension(
	'_chmlib', 
	sources = ['chmlib.i'],
	libraries = ['chm'])

setup(
	name = "PyHTMLHelp",
	version = "0.1",
	description = 'HTML Help Books Python API',
	author = 'José Fonseca',
	author_email = 'jrfonseca@users.berlios.de',
	url = 'http://htmlhelp.berlios.de/',
	packages = ['HTMLHelp'],
	py_modules = ['chmlib'],
	ext_modules = [chmlib],
	scripts = [
		'scripts/hhconvert',
		'scripts/hhsqldump'])
