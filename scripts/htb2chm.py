"""Converts wxWidgets' HTB to Windows' CHM."""


import sys
import os
import os.path
import tempfile
import shutil
import glob
import zipfile


hhc = 'C:\\Program Files\\HTML Help Workshop\\hhc.exe'


def unzip(filename, dir):
	sys.stderr.write('Extracting %s\n' % filename)
	
	zip = zipfile.ZipFile(filename, 'r')

	for name in zip.namelist():
		if name.endswith('/'):
			os.mkdir(os.path.join(dir, name))
		else:
			fp = file(os.path.join(dir, name), 'wb')
			fp.write(zip.read(name))


def find(ext, dir):
	return [os.path.join(dir, name) for name in os.listdir(dir) if name.endswith(ext)]


def convert(path):
	tempdir = tempfile.mkdtemp()

	unzip(path, tempdir)
	
	for hhp in find('.hhp', tempdir):
		ret = os.spawnl(os.P_WAIT, hhc, os.path.basename(hhc), hhp)
		if ret != 1:
			sys.stderr.write('%s: compilation failed with code %d\n' % (os.path.basename(hhp), ret))

	chms = find('.chm', tempdir)
	if len(chms) == 1:
		root, ext = os.path.splitext(path)
		dst = root + '.chm'
	else:
		dst = os.path.dirname(path)
	for chm in chms:
		shutil.move(chm, dst)
	
	shutil.rmtree(tempdir)


def main():
	if not sys.platform.startswith('win'):
		sys.stderr.write('Only Windows platform is supported.\n')
		sys.exit(1)

	if not os.path.exists(hhc):
		sys.stderr.write('HHC not found on \'%s\'.\n' % hhc)
		sys.exit(1)
		
	for arg in sys.argv[1:]:
		for file in glob.glob(arg):
			convert(file)
		

if __name__ == '__main__':
	main()
