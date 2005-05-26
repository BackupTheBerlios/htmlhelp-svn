#!/usr/bin/env python


import unittest
import archivetest

from htmlhelp.archive.tar import TarArchive


class TarArchiveTestCase(archivetest.SampleArchiveTestCase):
	
	def setUp(self):
		self.archive = TarArchive('data/sample.tar')
	

class TgzArchiveTestCase(archivetest.SampleArchiveTestCase):
	
	def setUp(self):
		self.archive = TarArchive('data/sample.tgz')
	

if __name__ == '__main__':
	unittest.main()
