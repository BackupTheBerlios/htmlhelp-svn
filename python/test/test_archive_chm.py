#!/usr/bin/env python


import unittest
import archivetest

from htmlhelp.archive.chm import ChmArchive
from htmlhelp.archive.filter import FilterArchive


class SampleFilterArchive(FilterArchive):

	root = '/'

	def filter(self, path):
		if path.startswith(self.root):
			return path[len(self.root):]
		else:
			return None

	def translate(self, path):
		return self.root + path


class ChmArchiveTestCase(archivetest.SampleArchiveTestCase):
	
	def setUp(self):
		self.archive = SampleFilterArchive(ChmArchive('data/sample_archive.chm'))
	

if __name__ == '__main__':
	unittest.main()
