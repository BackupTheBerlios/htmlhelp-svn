GARNAME = Numeric
GARVERSION = 22.0

DEVHELP_TARGETS = $(WORKDIR)/$(GARNAME)-$(GARVERSION).tgz

include ../../gar.mk

post-extract: $(WORKDIR)
	cd $(WORKDIR) && wget -r -np http://www.pfdubois.com/numpy/html2/numpy.html

$(WORKDIR)/book.devhelp: $(FILEDIR)/book.devhelp
	cp -a $< $@

$(WORKDIR)/book: extract
	mv $(WORKDIR)/www.pfdubois.com/numpy/html2 $(WORKDIR)/book

$(WORKDIR)/$(GARNAME)-$(GARVERSION).tgz: $(WORKDIR)/book.devhelp $(WORKDIR)/book
	tar -czf $@  -C $(WORKDIR) book book.devhelp
