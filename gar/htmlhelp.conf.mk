SHELL=/bin/bash

GARCHIVEROOT = $(GARDIR)/../garchive

COOKIEDIR = $(COOKIEROOTDIR)
WORKDIR = $(WORKROOTDIR)

BOOKARCHIVEROOT ?= $(GARDIR)/../books
BOOKARCHIVEDIR ?= $(BOOKARCHIVEROOT)

#HTMLHELP_EXTRA_LIBS ?= chm.lib.mk devhelp.lib.mk htb.lib.mk
HTMLHELP_EXTRA_LIBS ?= devhelp.lib.mk htb.lib.mk
#HTMLHELP_EXTRA_LIBS ?= devhelp.lib.mk

COLOR_GAR = no
