GARNAME = Numeric
GARVERSION = 22.0

DEVHELP_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).tgz

include ../../gar.mk

post-extract: $(WORKDIR)/www.pfdubois.com/numpy/html2

$(WORKDIR)/www.pfdubois.com/numpy/html2:
	cd $(WORKDIR) && wget -r -np http://www.pfdubois.com/numpy/html2/numpy.html

$(WORKDIR)/devhelp.$(GARNAME): $(FILEDIR)/book.devhelp $(WORKDIR)/www.pfdubois.com/numpy/html2
	rm -rf $@
	mkdir -p $@
	cp -a $(FILEDIR)/book.devhelp $@
	cp -a $(WORKDIR)/www.pfdubois.com/numpy/html2 $@/book
