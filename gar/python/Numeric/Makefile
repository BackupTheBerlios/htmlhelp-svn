GARNAME = Numeric

DEVHELP_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).tgz
CHM_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).chm
HTB_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).htb

include ../../gar.mk

post-extract: $(WORKDIR)/www.pfdubois.com/numpy/html2

$(WORKDIR)/www.pfdubois.com/numpy/html2:
	cd $(WORKDIR) && wget -r -np http://www.pfdubois.com/numpy/html2/numpy.html
	patch -i $(FILEDIR)/numpy.css.patch $(WORKDIR)/www.pfdubois.com/numpy/html2/numpy.css

$(WORKDIR)/devhelp.$(GARNAME): $(FILEDIR)/book.devhelp $(WORKDIR)/www.pfdubois.com/numpy/html2
	rm -rf $@
	mkdir -p $@
	cp -a $(FILEDIR)/book.devhelp $@
	cp -a $(WORKDIR)/www.pfdubois.com/numpy/html2 $@/book

$(WORKDIR)/htmlhelp.$(GARNAME): $(FILEDIR)/Numeric.hhp $(FILEDIR)/Numeric.hhc $(WORKDIR)/www.pfdubois.com/numpy/html2
	rm -rf $@
	cp -a $(WORKDIR)/www.pfdubois.com/numpy/html2 $@
	cp -a $(FILEDIR)/Numeric.hhc $@
	cp -a $(FILEDIR)/Numeric.hhp $@
