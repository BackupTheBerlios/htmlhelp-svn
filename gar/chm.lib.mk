# Compiled Html Help books generation


all: chm


# chm	- Generate Compiled Html Help books.

CHM_TARGETS = $(addsuffix .chm,$(basename $(BOOKS)))
BOOKS_TARGETS += $(CHM_TARGETS)

chm: build pre-chm $(CHM_TARGETS) post-chm
	$(DONADA)

# returns true if the Compiled Html Help books have completed successfully, false otherwise
chm-p:
	@$(foreach COOKIEFILE,$(CHM_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


################### CHM RULES ####################

HHC = "C:/Program Files/HTML Help Workshop/hhc.exe"
HHC_FLAGS =

WINE = wine
WINE_FLAGS = 
ifneq ($(shell which $(WINE)),)
HHC := $(WINE) $(WINE_FLAGS) -- $(HHC)
endif

%.chm: %.mshh
	$(HHC) $(HHC_FLAGS) $(wildcard $<d/*.hhp)
	mv $<d/$(@F) $@
	@$(MAKECOOKIE)

%.chm: empty-%.chm
	@$(MAKECOOKIE)


include $(GARDIR)/mshh.lib.mk
