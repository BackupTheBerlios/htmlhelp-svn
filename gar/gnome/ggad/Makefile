GARNAME = ggad
MASTER_SITES = cvs://anonymous@anoncvs.gnome.org/cvs/gnome/
DISTFILES = GGAD

BOOKS = $(WORKDIR)/GGAD/DocBook/ggad.sgml

DSSSL = book-html.dsl

FIGURES = figures/*

include ../../gar.mk

checksum-GGAD:
	$(DONADA)

JADE_FLAGS += -c $(WORKSRC)/catalog

$(WORKDIR)/GGAD/DocBook/ggad.sgml: $(WORKDIR)/GGAD/DocBook/book.sgml
	sed -e '1s/1\.0/1.1/' $< > $@
