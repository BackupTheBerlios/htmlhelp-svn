GARNAME = gri
GARVERSION = 2.12.7
MASTER_SITES = http://ftp1.sourceforge.net/$(GARNAME)/
DISTFILES = $(GARNAME)-$(GARVERSION).tgz

CONFIGURE_SCRIPTS = $(WORKSRC)/configure

BUILD_SCRIPTS = $(WORKSRC)/doc/Makefile
BUILD_ARGS = html html-install DOC_DIR=./tmp

BOOKS = gri

include ../../gar.mk

convert-mshh/gri:
	rm -rf $(SCRATCHDIR)
	cp -a $(WORKSRC)/doc/tmp $(SCRATCHDIR)
	cd $(SCRATCHDIR) && $(CURDIR)/$(FILEDIR)/gri2chm.pl < $(CURDIR)/$(WORKSRC)/doc/gri.texi
	@$(MAKECOOKIE)
