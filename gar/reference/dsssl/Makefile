GARNAME = dsssl
MASTER_SITES = ftp://ftp.ornl.gov/pub/sgml/wg8/$(GARNAME)/
DISTFILES = helpsrc.zip

WORKSRC = $(WORKDIR)/helpsrc

CHM_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).chm
HTB_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).htb

include ../../gar.mk

$(WORKDIR)/htmlhelp.$(GARNAME): $(WORKSRC)
	cp -a $< $@
