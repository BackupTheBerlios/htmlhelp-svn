GARNAME = gri
GARVERSION = 2.12.7
MASTER_SITES = http://ftp1.sourceforge.net/$(GARNAME)/
DISTFILES = $(GARNAME)-$(GARVERSION).tgz

CONFIGURE_SCRIPTS = $(WORKSRC)/configure

BUILD_SCRIPTS = $(WORKSRC)/doc/Makefile
BUILD_ARGS = html html-install DOC_DIR=./tmp

BOOKS = $(WORKSRC)/doc/gri.texi

include ../../gar.mk

$(WORKSRC)/doc/gri.mshh: $(WORKSRC)/doc/gri.texi
	rm -rf $@d
	cp -a $(<D)/tmp/html $@d
	cd $@d && ../../../../../$(FILEDIR)/gri2chm.pl < ../gri.texi
	touch $@
	
$(WORKSRC)/doc/gri.tgz:
	$(DONADA)
