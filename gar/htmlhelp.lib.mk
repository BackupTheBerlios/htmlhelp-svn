# HTML Help books generation

all: htmlhelp


# books	- Generate HTML Help books.

HTMLHELP_TARGETS ?=

include $(addprefix $(GARDIR)/,$(sort $(HTMLHELP_EXTRA_LIBS)))

htmlhelp: build pre-htmlhelp $(HTMLHELP_TARGETS) post-htmlhelp
	$(DONADA)

# returns true if all books have completed successfully, false otherwise
htmlhelp-p:
	@$(foreach COOKIEFILE,$(HTMLHELP_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


# books	- Archive HTML Help books

books:
	$(foreach FILE,$(HTMLHELP_TARGETS), \
		test -e $(FILE) && \
		cp -a $(FILE) $(BOOKSDIR)/$(notdir $(basename $(FILE)))$(if $(GARVERSION),-$(GARVERSION),)$(suffix $(FILE)) ;)


empty-%:
	@echo -e "$(ERRORCOLOR)Don't know how to make $*$(NORMALCOLOR)"
