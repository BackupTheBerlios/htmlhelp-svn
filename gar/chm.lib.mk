# Compiled Html Help books generation

all: htmlhelp

# htmlhelp	- Generate Compiled Html Help books.

HTMLHELP_TARGETS = $(addsuffix .chm,$(basename $(filter %.texi %.texinfo %.txi %.xml,$(BOOKS))))

htmlhelp: build pre-htmlhelp $(HTMLHELP_TARGETS) post-htmlhelp
	$(DONADA)

# returns true if the Compiled Html Help books have completed successfully, false otherwise
htmlhelp-p:
	@$(foreach COOKIEFILE,$(HTMLHELP_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)

post-install: htmlhelp-post-install
	
# install	- Install Compiled Html Help books
htmlhelp-post-install:
ifdef GARVERSION
	$(foreach FILE,$(HTMLHELP_TARGETS), \
		cp -a $(FILE) $(DESTDIR)/chm/$(addsuffix -$(GARVERSION)$(suffix $(FILE)),$(basename $(FILE))) ;)
else
	$(foreach FILE,$(HTMLHELP_TARGETS), \
		cp -a $(FILE) $(DESTDIR)/chm ;)
endif


# Compilation

ifdef WIN32
HHC = "C:/Program Files/HTML Help Workshop/hhc.exe"
else
WINE = wine
HHC = $(WINE) -- "C:/Program Files/HTML Help Workshop/hhc.exe"

.PRECIOUS: %.hhp %.hhc %.hhk
endif


%.chm: htmlhelp.%
	cd $< && $(HHC) $(*F).hhp
	mv $</$(@F) $@


include $(GARDIR)/hh.lib.mk
