#!/usr/bin/python
# -*- coding: iso8859-1 -*-

import sys
from distutils.core import setup, Extension

py_modules = []
ext_modules = []

if not sys.platform.startswith('win'):
	chmlib = Extension(
		'_chmlib', 
		sources = ['chmlib.i'],
		libraries = ['chm'])
	py_modules.append('chmlib')
	ext_modules.append(chmlib)

setup(
	name = "PyHTMLHelp",
	version = "0.2",
	description = 'HTML Help Books Python API',
	author = 'José Fonseca',
	author_email = 'jrfonseca@users.berlios.de',
	url = 'http://htmlhelp.berlios.de/',
	packages = ['htmlhelp'],
	py_modules = py_modules,
	ext_modules = ext_modules,
	scripts = [
		'hhconvert.py',
		'hhsqldump.py'])
