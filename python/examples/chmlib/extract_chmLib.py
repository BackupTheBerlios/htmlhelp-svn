#!/usr/bin/python

import sys
import os
import os.path
import chmlib

def rmkdir(path):
	if not os.path.exists(path):
		try:
			os.makedirs(path)
		except OSError:
			return -1
	
	return 0
	
def _extract_callback(h, ui, base_path):
	if ui.path[:1] != '/':
		return chmlib.CHM_ENUMERATOR_CONTINUE
	
	path = os.path.join(base_path, ui.path[1:])

	if ui.length != 0:
		print "--> %s" % ui.path
		try:
			fout = open(path, "wb")
		except IOError:
			return chmlib.CHM_ENUMERATOR_FAILURE
		
		offset = 0L
		remain = ui.length
		while remain:
			buffer = chmlib.chm_retrieve_object(h, ui, offset, 32768)
			if buffer:
				fout.write(buffer)
				print len(buffer)
				print remain
				assert len(buffer) <= remain
				offset += len(buffer)
				remain -= len(buffer)
			else:
				sys.stderr.write("incomplete file: %s\n" % ui.path);
				break
	
	else:
		if rmkdir(path) == -1:
			return chmlib.CHM_ENUMERATOR_FAILURE
	
	return chmlib.CHM_ENUMERATOR_CONTINUE

def main():
	if len(sys.argv) < 3:
		sys.stderr.write("usage: %s <chmfile> <outdir>\n" % sys.argv[0])
		sys.exit(1)

	h = chmlib.chm_open(sys.argv[1]);
	if not h:
		sys.stderr("failed to open %s\n" % sys.argv[1])
		sys.exit(1)

	print "%s:" % sys.argv[1]
	base_path = sys.argv[2]
	if not chmlib.chm_enumerate(h, chmlib.CHM_ENUMERATE_ALL, _extract_callback, base_path):
		print "   *** ERROR ***"

	chmlib.chm_close(h);

if __name__ == '__main__':
	main()
