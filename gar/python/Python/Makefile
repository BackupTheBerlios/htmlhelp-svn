GARNAME = Python
GARVERSION = 2.4
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

$(WORKDIR)/$(GARNAME).mshh:
	rm -rf $@d
	cp -a $(WORKSRC) $@d
	cd $@d && python2.4 /usr/lib/python2.4/doc/tools/prechm.py -v $(GARVERSION) $(GARNAME)
	touch $@
