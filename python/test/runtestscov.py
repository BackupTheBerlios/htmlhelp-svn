#!/usr/bin/env python


import os
import os.path
import unittest
import sys
from coverage import the_coverage as coverage

dir = os.path.abspath('../htmlhelp/')

def main():
	coverage.use_cache(False)
	coverage.erase()
	suite = unittest.TestSuite()
	
	for name in os.listdir('.'):
		if name.startswith('test_') and name.endswith('.py'):
			suite.addTest(unittest.defaultTestLoader.loadTestsFromName(name[:-3]))
	coverage.start()
	unittest.TextTestRunner(stream=sys.stdout, verbosity=2).run(suite)
	coverage.stop()

	print
	
	global dir
	coverage.canonicalize_filenames()
	modules = [name for name in coverage.cexecuted.iterkeys() if name.startswith(dir)]
	modules.sort()
	coverage.report(modules)


if __name__ == '__main__':
	main()
