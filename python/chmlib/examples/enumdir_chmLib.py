#!/usr/bin/python

import sys
import chmlib

def _print_ui(h, ui, context):
	print "   %1d %8d %8d   %s" % (ui.space, ui.start, ui.length, ui.path)
	
	return chmlib.CHM_ENUMERATOR_CONTINUE

def main():
	if len(sys.argv) < 2:
		sys.stderr("%s <chmfile> [dir] [dir] ...\n", sys.argv[0])
		exit(1)

	h = chmlib.chm_open(sys.argv[1])
	if not h:
		sys.stderr("failed to open %s\n", sys.argv[1])
		exit(1)

	if len(sys.argv) < 3:
		print "/:"
		print " spc    start   length   name"
		print " ===    =====   ======   ===="

		if not chmlib.chm_enumerate_dir(h, "/", chmlib.CHM_ENUMERATE_ALL, _print_ui, None):
				print "   *** ERROR ***"
	else:
		for arg in sys.argv[2:]:
			print "%s:\n" % arg
			print " spc    start   length   name"
			print " ===    =====   ======   ===="

			if not chmlib.chm_enumerate_dir(h, arg, chmlib.CHM_ENUMERATE_ALL, _print_ui, None):
				print "   *** ERROR ***"

	chmlib.chm_close(h)

if __name__ == '__main__':
	main()
