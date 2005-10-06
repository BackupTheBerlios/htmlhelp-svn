GARNAME = perl
GARVERSION = 5.8.6
MASTER_SITES = http://www.cpan.org/src/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2

define BLURB
 See also:
 - http://search.cpan.org/~swmcd/Pod-Tree-1.10/
 - http://search.cpan.org/~autrijus/Pod-HtmlHelp-1.1/
 - http://search.cpan.org/~pvhp/Pod2WinHlp-0.02/README
endef

BOOKS = perl

include ../../gar.mk

convert-devhelp/perl:
	rm -rf $(SCRATCHDIR)
	mkdir -p $(SCRATCHDIR)
	$(SHELL) $(FILEDIR)/perl2devhelp.sh $(WORKSRC) $(GARVERSION) $(SCRATCHDIR)
	cp $(FILEDIR)/perl.css $(SCRATCHDIR)/book
	$(SHELL) $(FILEDIR)/addcss.sh perl.css $(SCRATCHDIR)/book

convert-mshh/perl:
	rm -rf $(SCRATCHDIR)
	$(SHELL) $(FILEDIR)/perl2chm.sh $(WORKSRC) $(SCRATCHDIR)
	cp $(FILEDIR)/perl.css $(SCRATCHDIR)
	$(SHELL) $(FILEDIR)/addcss.sh perl.css $(SCRATCHDIR) 
