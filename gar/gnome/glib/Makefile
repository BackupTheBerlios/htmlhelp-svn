GARNAME = glib
GARVERSION = 2.2.1
MASTER_SITES = ftp://ftp.gtk.org/pub/gtk/v2.2/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2

BOOKS = \
	$(WORKSRC)/docs/reference/glib/glib-docs.xml \
	$(WORKSRC)/docs/reference/gobject/gobject-docs.xml

XSL = /usr/share/gtk-doc/gtk-doc.xsl

#FIGURES = mainloop-states.*

include ../../gar.mk

post-extract:
	mv $(WORKSRC)/docs/reference/glib/glib-docs.sgml  $(WORKSRC)/docs/reference/glib/glib-docs.xml
	mv $(WORKSRC)/docs/reference/gobject/gobject-docs.sgml $(WORKSRC)/docs/reference/gobject/gobject-docs.xml
	
