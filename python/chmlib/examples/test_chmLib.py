#!/usr/bin/python

import sys
import os
import os.path
import chmlib

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
			print "hi"
			if buffer:
				fout.write(buffer)
				print len(buffer)
				print remain
				assert len(buffer) <= remain
				offset += len(buffer)
				remain -= len(buffer)
			else:
				sys.stderr.write("incomplete file: %s\n" % ui.path);
	
	else:
		if rmkdir(path) == -1:
			return chmlib.CHM_ENUMERATOR_FAILURE
	
	return chmlib.CHM_ENUMERATOR_CONTINUE

def main():
	if len(sys.argv) < 4:
		sys.stderr.write("usage: %s <chmfile> <filename> <destfile>\n" % sys.argv[0])
		sys.exit(1)

	h = chmlib.chm_open(sys.argv[1]);
	if not h:
		sys.stderr("failed to open %s\n" % sys.argv[1])
		sys.exit(1)

	print "resolving %s:" % sys.argv[2]
	ui = chmlib.chm_resolve_object(h, sys.argv[2])
	if ui:
		print ui.path, ui.length
		buffer = chmlib.chm_retrieve_object(h, ui, 0L, ui.length)
		if not buffer:
			print "    extract failed"
			sys.exit(2)
		
		fout = open(sys.argv[3], "wb")
		if not fout:
			print "    create failed"
		
		fout.write(buffer)
		print "    finished"
	else:
		print "    failed"

	chmlib.chm_close(h);

if __name__ == '__main__':
	main()
