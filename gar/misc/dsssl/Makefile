GARNAME = dsssl
MASTER_SITES = ftp://ftp.ornl.gov/pub/sgml/wg8/$(GARNAME)/
DISTFILES = helpsrc.zip
PATCHFILES = gar-base.diff

BOOKS = $(WORKDIR)/dsssl.mshh

WORKSRC = $(WORKDIR)/helpsrc

include ../../gar.mk

$(WORKDIR)/dsssl.mshh: $(WORKSRC)
	cp -a $< $@d
	touch $@
