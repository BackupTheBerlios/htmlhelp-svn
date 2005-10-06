GARNAME = gri
GARVERSION = 2.12.7
MASTER_SITES = http://ftp1.sourceforge.net/$(GARNAME)/
DISTFILES = $(GARNAME)-$(GARVERSION).tgz

CONFIGURE_SCRIPTS = $(WORKSRC)/configure

BUILD_SCRIPTS = $(WORKSRC)/doc/Makefile
BUILD_ARGS = DOC_DIR=$(CURDIR)/$(WORKDIR)/doc html html-install

BOOKS = gri

include ../../gar.mk

convert-mshh/gri: $(CURDIR)/$(FILEDIR)/gri2chm.pl
	rm -rf $(SCRATCHDIR)
	cp -a $(WORKDIR)/doc/html $(SCRATCHDIR)
	perl $(FILEDIR)/gri2chm.pl $(WORKSRC)/doc/gri.texi $(SCRATCHDIR)
	@$(MAKECOOKIE)
