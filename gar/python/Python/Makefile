GARNAME = Python
GARVERSION = 2.3.2
MASTER_SITES = http://www.python.org/ftp/python/doc/$(GARVERSION)/
DISTFILES = html-$(GARVERSION).tar.bz2

WORKSRC = $(WORKDIR)/Python-Docs-$(GARVERSION)

BOOKS = $(WORKDIR)/$(GARNAME).mshh $(WORKDIR)/$(GARNAME).devhelp


include ../../gar.mk


$(WORKDIR)/$(GARNAME).devhelp: $(FILEDIR)/py2devhelp.py
	rm -rf $@d
	mkdir -p $@d
	cp -a $(WORKSRC) $@d/book
	cd $@d/book && python ../../../../$(FILEDIR)/py2devhelp.py ../book
	touch $@

$(WORKDIR)/$(GARNAME).mshh: $(FILEDIR)/pythlp.py
	rm -rf $@d
	cp -a $(WORKSRC) $@d
	cd $@d && python ../../../$(FILEDIR)/pythlp.py $(GARNAME)
	touch $@
