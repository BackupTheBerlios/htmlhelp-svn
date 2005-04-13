all: \
	sample.zip \
	sample.tar \
	sample.tgz

sample.zip: sample
	cd $< && zip -r ../$@ *

sample.tgz: sample
	cd $< && tar -czf ../$@ *

sample.tar: sample
	cd $< && tar -cf ../$@ *
