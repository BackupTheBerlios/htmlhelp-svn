HHC = wine "C:\\Program File\\HTML Help Workshop\\hhc.exe"

all: \
	sample.zip \
	sample.tar \
	sample.tgz \
	sample.chm

sample:
	mkdir sample
	touch sample/empty
	echo "Sample..." > sample/file
	mkdir sample/dir
	echo "Sample..." > sample/dir/file
	mkdir sample/dir/subdir
	echo "Sample..." > sample/dir/subdir/file

sample.zip: sample
	cd $< && zip -r ../$@ *

sample.tgz: sample
	cd $< && tar -czf ../$@ *

sample.tar: sample
	cd $< && tar -cf ../$@ *

sample.chm: sample.hhp
	$(HHC) $<

sample.hhp: sample
	echo "[OPTIONS]" > $@
	echo "" >> $@
	echo "[FILES]" >> $@
	find sample -type f -not -name .svn >> $@

clean:
	rm -f sample

purge:
	rm -f sample.zip sample.tar sample.tgz sample.chm

.PHONY: all clean purge
