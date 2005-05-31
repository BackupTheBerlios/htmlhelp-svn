"""Base test case for archives."""


import unittest


class ArchiveTestCase(unittest.TestCase):

	missing_path = 'AVeryUnlikelyFileName'
	
	def failUnlessFileObj(self, fp, path):
		"""Fail if fp is not a read file-like object.
		
		Given Python dynamic type system it is not enough to verify if fp is an
		instance of 'file'. Instead we verify if all the usual file reading methods
		exist."""
		
		for method in [
				'close',
				'read',
				'readline',
				'seek',
				'tell']:
			self.failUnless(hasattr(fp, method), "'%s' method is missing from '%s' file" % (method, path))
			self.failUnless(callable(getattr(fp, method)), "'%s' method is not callable '%s' file" % (method, path))
	
	def setUp(self):
		"""Must be overriden by subclasses and set self.archive."""
		
		raise NotImplementedError
		
	def testContains(self):
		for path in self.archive:
			self.failUnless(path in self.archive, "'%s' is listed in the archive but reported as missing" % path)
		self.failIf(self.missing_path in self.archive, "'%s' should be reported as missing" % self.missing_path)

	def testGetItem(self):
		for path in self.archive:
			try:
				fp = self.archive[path]
			except KeyError:
				self.fail("'%s' is listed in the archive but not accessible" % path)
			self.failUnlessFileObj(fp, path)
				
		try:
			fp = self.archive[self.missing_path]
			self.fail("'%s' should not be accessible" % self.missing_path)
		except KeyError:
			pass
	
	def testLen(self):
		l = 0
		for path in self.archive:
			l += 1
		self.failUnlessEqual(len(self.archive), l)


class SampleArchiveTestCase(ArchiveTestCase):

	paths = [
		'empty',
		'file',
		'dir/file',
		'dir/subdir/file']
	
	def testList(self):
		for path in self.paths:
			self.failUnless(path in self.archive, "'%s' is missing" % path)
			
	def testLen2(self):
		self.failUnlessEqual(len(self.archive), len(self.paths))
			
	def testEmpty(self):
		fp = self.archive['empty']
		self.failUnlessEqual(fp.read(), '')
	
	def testFile(self):
		for path in [
				'file',
				'dir/file',
				'dir/subdir/file']:
			fp = self.archive['dir/subdir/file']
			self.failUnlessEqual(fp.read(), 'Sample...\n')
