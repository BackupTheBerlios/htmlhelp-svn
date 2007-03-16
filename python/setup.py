#!/usr/bin/python

import sys
from distutils.core import setup

setup(
	name = "PyHTMLHelp",
	version = "0.4",
	description = 'HTML Help Books Python API',
	author = 'Jose Fonseca',
	author_email = 'jrfonseca@users.berlios.de',
	url = 'http://htmlhelp.berlios.de/',
	packages = [
		'htmlhelp',
		'htmlhelp.archive',
		'htmlhelp.format',
		'htmlhelp.util',
	],
	scripts = [
		'hhconvert.py',
		'hhsqldump.py',
	],
)
