GARNAME = perl
GARVERSION = 5.8.2
MASTER_SITES =http://www.cpan.org/src/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2

define BLURB
 See also:
 - http://search.cpan.org/~swmcd/Pod-Tree-1.10/
 - http://search.cpan.org/~autrijus/Pod-HtmlHelp-1.1/
 - http://search.cpan.org/~pvhp/Pod2WinHlp-0.02/README
endef

BOOKS = $(WORKDIR)/$(GARNAME).mshh

include ../../gar.mk

$(WORKDIR)/$(GARNAME).mshh: $(FILEDIR)/perl2chm.sh $(FILEDIR)/perl.css
	rm -rf $@d
	$(SHELL) $(FILEDIR)/perl2chm.sh $(WORKSRC) $@d
	cp $(FILEDIR)/perl.css $@d
	$(FILEDIR)/addcss.sh perl.css $@d 
	touch $@
