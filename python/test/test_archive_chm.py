#!/usr/bin/env python


import unittest
import archivetest

from htmlhelp.archive.chm import ChmArchive
from htmlhelp.archive.filter import FilterArchive


class SampleFilterArchive(FilterArchive):

	def filter(self, path):
		if path[:8] == '/sample/':
			return path[1:]
		else:
			return None

	def translate(self, path):
		return '/sample/' + path


class ChmArchiveTestCase(archivetest.SampleArchiveTestCase):
	
	def setUp(self):
		self.archive = SampleFilterArchive(ChmArchive('data/sample.chm'))
	

if __name__ == '__main__':
	unittest.main()
