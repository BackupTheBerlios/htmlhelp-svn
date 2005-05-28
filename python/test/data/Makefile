HHC = wine "C:\\Program File\\HTML Help Workshop\\hhc.exe"

all: \
	sample_archive \
	sample_archive.zip \
	sample_archive.tar \
	sample_archive.tgz \
	sample_archive.chm

sample_archive:
	mkdir $@
	touch $@/empty
	echo "Sample..." > $@/file
	mkdir $@/dir
	echo "Sample..." > $@/dir/file
	mkdir $@/dir/subdir
	echo "Sample..." > $@/dir/subdir/file

sample_archive.zip: sample_archive
	cd $< && zip -r ../$@ *

sample_archive.tgz: sample_archive
	cd $< && tar -czf ../$@ *

sample_archive.tar: sample_archive
	cd $< && tar -cf ../$@ *

sample_archive.chm: sample_archive.hhp
	$(HHC) $<

sample_archive.hhp: sample_archive
	echo "[OPTIONS]" > $@
	echo "" >> $@
	echo "[FILES]" >> $@
	find $< -type f -not -name .svn >> $@

clean:
	rm -f \
		sample_archive.hhp

purge:
	rm -f \
		sample_archive \
		sample_archive.zip \
		sample_archive.tar \
		sample_archive.tgz \
		sample_archive.chm

.PHONY: all clean purge
