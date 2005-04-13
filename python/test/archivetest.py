"""Base test case for archives."""


import unittest


class ArchiveTestCase(unittest.TestCase):

	missing_path = 'AVeryUnlikelyFileName'
	
	def failUnlessFileObj(self, fp):
		for method in [
				'close',
				'flush',
				'read',
				'readline',
				'tell']:
			self.failUnless(hasattr(fp, method))
			self.failUnless(callable(getattr(fp, method)))
		
	def testContains(self):
		for path in self.archive:
			self.failUnless(path in self.archive)
		self.failIf(self.missing_path in self.archive)

	def testGetItem(self):
		for path in self.archive:
			try:
				fp = self.archive[path]
			except KeyError:
				self.fail()
				
		try:
			fp = self.archive[self.missing_path]
			self.fail()
		except KeyError:
			pass
	
	def testLen(self):
		l = 0
		for path in self.archive:
			l += 1
		self.failUnlessEqual(len(self.archive), l)


class SampleArchiveTestCase(ArchiveTestCase):

	def testList(self):
		for path in [
				'empty',
				'file',
				'dir/file',
				'dir/subdir/file']:
			self.failUnless(path in self.archive)
			
	def testEmpty(self):
		fp = self.archive['empty']
		self.failUnlessEqual(fp.read(), '')
	
	def testFile(self):
		for path in [
				'file',
				'dir/file',
				'dir/subdir/file']:
			fp = self.archive['dir/subdir/file']
			self.failUnlessEqual(fp.read(), 'Test\n')
