# Compiled Html Help books generation


all: chm


# chm	- Generate Compiled Html Help books.

CHM_TARGETS = $(addsuffix .chm,$(basename $(filter %.texi %.texinfo %.txi %.xml,$(BOOKS)))) $(CHM_EXTRA_TARGETS)

chm: build pre-chm $(CHM_TARGETS) post-chm
	$(DONADA)

# returns true if the Compiled Html Help books have completed successfully, false otherwise
chm-p:
	@$(foreach COOKIEFILE,$(CHM_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


# install	- Install Compiled Html Help books
post-install: chm-post-install

chm-post-install:
ifdef GARVERSION
	$(foreach FILE,$(CHM_TARGETS), \
		cp -a $(FILE) $(DESTDIR)/$(notdir $(addsuffix -$(GARVERSION)$(suffix $(FILE)),$(basename $(FILE)))) ;)
else
	$(foreach FILE,$(CHM_TARGETS), \
		cp -a $(FILE) $(DESTDIR) ;)
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


include $(GARDIR)/htmlhelp.lib.mk
