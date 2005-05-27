#!/usr/bin/env python


import unittest

from htmlhelp.book import *


class ContentsEntryTestCase(unittest.TestCase):
	
	def setUp(self):
		self.parent = ContentsEntry()
		self.child = ContentsEntry()
		self.sibling = ContentsEntry()
		self.parent.append(self.child)
		self.parent.append(self.sibling)
	
	def testAppend(self):
		self.failUnlessEqual(self.child.number, 1)
		self.failUnlessEqual(self.sibling.number, 2)
		
	def testLen(self):
		self.failUnlessEqual(len(self.child), 0)
		self.failUnlessEqual(len(self.parent), 2)
	
	def testGetItem(self):
		try:
			self.child[0]
			self.fail()
		except IndexError:
			pass
		self.failUnless(self.parent[1] is self.sibling)
	
	def testSetItem(self):
		parent = ContentsEntry()
		child = ContentsEntry()
		parent.append(child)
		new_child = ContentsEntry()
		parent[0] = new_child
		self.failUnless(parent[0] is new_child)
		self.failUnlessEqual(new_child.number, 1)
	
	def testInsert(self):
		parent = ContentsEntry()
		child = ContentsEntry()
		parent.append(child)
		new_child = ContentsEntry()
		parent.insert(0, new_child)
		self.failUnless(parent[0] is new_child)
		self.failUnlessEqual(new_child.number, 1)
		self.failUnless(parent[1] is child)
		self.failUnlessEqual(child.number, 2)
	
	def testParent(self):
		self.failUnless(self.child.parent is self.parent)
		self.failUnless(self.parent.parent is None)
	
	def testChildren(self):
		self.failUnless(self.parent.children is self.child)
		self.failUnless(self.child.children is None)
	
	def testNext(self):
		self.failUnless(self.child.next is self.sibling)
		self.failUnless(self.sibling.next is None)
		self.failUnless(self.parent.next is None)

	def testPrev(self):
		self.failUnless(self.sibling.prev is self.child)
		self.failUnless(self.child.prev is None)
		self.failUnless(self.parent.prev is None)
	

if __name__ == '__main__':
	unittest.main()
