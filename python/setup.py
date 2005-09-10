#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys
from distutils.core import setup

setup(
	name = "PyHTMLHelp",
	version = "0.3",
	description = 'HTML Help Books Python API',
	author = 'José Fonseca',
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
