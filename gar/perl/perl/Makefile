GARNAME = perl
GARVERSION = 5.8.2
MASTER_SITES =http://www.cpan.org/src/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2

define BLURB
 It requires Pod::Tree.

 See also:
 - http://search.cpan.org/src/AUTRIJUS/Pod-HtmlHelp-1.1/README
 - http://search.cpan.org/~pvhp/Pod2WinHlp-0.02/README
endef

BOOKS = $(WORKDIR)/$(GARNAME).mshh

include ../../gar.mk

$(WORKDIR)/$(GARNAME).mshh: $(FILEDIR)/perl2chm.pl $(FILEDIR)/perl.css
	rm -rf $@d
	perl $(FILEDIR)/perl2chm.pl $(WORKSRC) $@d
	cp $(FILEDIR)/perl.css $@d
	$(FILEDIR)/addcss.sh perl.css $@d 
	touch $@
