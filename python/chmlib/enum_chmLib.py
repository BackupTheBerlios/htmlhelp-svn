#!/usr/bin/python

import sys
import chmlib

def _print_ui(h, ui, context):
	print "   %1d %8d %8d   %s" % (ui.space, ui.start, ui.length, ui.path)
	
	return chmlib.CHM_ENUMERATOR_CONTINUE

def main():
	for arg in sys.argv[1:]:
		h = chmlib.chm_open(arg)

		if not h:
			sys.stderr.write("failed to open %s\n" % arg)
			sys.exit(1)

		print "%s:" % arg
		print " spc    start   length   name"
		print " ===    =====   ======   ===="

		if not chmlib.chm_enumerate(h, chmlib.CHM_ENUMERATE_ALL, _print_ui, None):
			print "   *** ERROR ***"

		chmlib.chm_close(h)

if __name__ == '__main__':
	main()
