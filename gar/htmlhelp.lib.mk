# HTML Help books generation

all: books


# books	- Generate HTML Help books.

BOOKS_TARGETS =

books: build pre-books $(BOOKS_TARGETS) post-books
	$(DONADA)

# returns true if all books have completed successfully, false otherwise
books-p:
	@$(foreach COOKIEFILE,$(BOOKS_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


# booksarchive	- Archive HTML Help books
BOOKSDIR ?= $(GARDIR)/../books

booksarchive: 
	$(foreach FILE,$(BOOKS_TARGETS), \
		test -e $(FILE) && \
		cp -a $(FILE) $(BOOKSDIR)/$(notdir $(basename $(FILE)))$(if $(GARVERSION),-$(GARVERSION),)$(suffix $(FILE)) ;)


HTMLHELP_EXTRA_LIBS ?= chm.lib.mk devhelp.lib.mk htb.lib.mk

include $(addprefix $(GARDIR)/,$(sort $(HTMLHELP_EXTRA_LIBS)))


empty-%:
	@echo -e "$(ERRORCOLOR)Don't know how to make $*$(NORMALCOLOR)"
