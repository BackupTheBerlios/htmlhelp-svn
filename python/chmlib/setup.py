#!/usr/bin/python

from distutils.core import setup, Extension
 
module1 = Extension(
	'_chmlib', 
	sources = [
		'chmlib.i'],
	libraries = [
		'chm',
		])

setup(
	name = "chmlib",
	version = "0.3",
	ext_modules = [module1])
