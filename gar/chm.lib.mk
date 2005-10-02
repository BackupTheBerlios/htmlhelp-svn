# Compiled Html Help books generation


htmlhelp: chm


# chm	- Generate Compiled Html Help books.

CHM_TARGETS = $(addprefix compile-chm/,$(BOOKS))

chm: build pre-chm $(CHM_TARGETS) post-chm
	$(DONADA)

# returns true if the Compiled Html Help books have completed successfully, false otherwise
chm-p:
	@$(foreach COOKIEFILE,$(CHM_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


##################### CHM RULES ###################

include $(GARDIR)/mshh.lib.mk

HHC = "C:/Program Files/HTML Help Workshop/hhc.exe"
HHC_FLAGS =

WINE = wine
WINE_FLAGS = 
ifneq ($(shell which $(WINE)),)
HHC := $(WINE) $(WINE_FLAGS) $(HHC)
endif

compile-chm/%: pre-convert-mshh/% convert-mshh/% post-convert-mshh/%
	@echo -e " $(WORKCOLOR)==> Compiling $(BOLD)$(WORKDIR)/$(BOOK_FILENAME).chm$(NORMALCOLOR)"
	@#-$(HHC) $(HHC_FLAGS) $(wildcard $(SCRATCHDIR)/*.hhp)
	-@cd $(SCRATCHDIR) && $(HHC) $(HHC_FLAGS) $(notdir $(wildcard $(SCRATCHDIR)/*.hhp))
	@mv $(notdir $(wildcard $(SCRATCHDIR)/*.chm)) $(BOLD)$(WORKDIR)/$(BOOK_FILENAME).chm
	@rm -rf $(SCRATCHDIR)
	@$(MAKECOOKIE)

