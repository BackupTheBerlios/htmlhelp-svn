GARNAME = Numeric

BOOKS = $(WORKDIR)/$(GARNAME).devhelp $(WORKDIR)/$(GARNAME).mshh

include ../../gar.mk

post-extract: $(WORKDIR)/www.pfdubois.com/numpy/html2

$(WORKDIR)/www.pfdubois.com/numpy/html2:
	cd $(WORKDIR) && wget -r -np http://www.pfdubois.com/numpy/html2/numpy.html
	patch -i $(FILEDIR)/numpy.css.patch $(WORKDIR)/www.pfdubois.com/numpy/html2/numpy.css

$(WORKDIR)/$(GARNAME).devhelp: $(WORKDIR)/www.pfdubois.com/numpy/html2
	rm -rf $@d
	mkdir -p $@d
	cp -a $(FILEDIR)/book.devhelp $@d
	cp -a $(WORKDIR)/www.pfdubois.com/numpy/html2 $@d/book
	touch $@

$(WORKDIR)/$(GARNAME).mshh: $(WORKDIR)/www.pfdubois.com/numpy/html2
	rm -rf $@d
	cp -a $(WORKDIR)/www.pfdubois.com/numpy/html2 $@d
	cp -a $(FILEDIR)/Numeric.hhp $@d
	cp -a $(FILEDIR)/Numeric.hhc $@d
	cp -a $(FILEDIR)/Numeric.hhk $@d
	touch $@
