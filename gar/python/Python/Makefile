GARNAME = Python
GARVERSION = 2.3
MASTER_SITES = http://www.python.org/ftp/python/doc/$(GARVERSION)/
DISTFILES = html-$(GARVERSION).tar.bz2

WORKSRC = $(WORKDIR)/Python-Docs-$(GARVERSION)

DEVHELP_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).tgz
CHM_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).chm
HTB_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).htb


include ../../gar.mk


$(WORKDIR)/devhelp.$(GARNAME): $(FILEDIR)/py2devhelp.py
	rm -rf $@
	mkdir -p $@
	cp -a $(WORKSRC) $@/book
	cd $@/book && python ../../../$(FILEDIR)/py2devhelp.py ../book

$(WORKDIR)/htmlhelp.$(GARNAME): $(FILEDIR)/pythlp.py
	rm -rf $@
	cp -a $(WORKSRC) $@
	cd $@ && python ../../$(FILEDIR)/pythlp.py $(GARNAME)
