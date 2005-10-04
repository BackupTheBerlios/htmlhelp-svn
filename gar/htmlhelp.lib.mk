# HTML Help books generation


all: htmlhelp


# htmlhelp - Generate HTML Help books.

BOOKS ?=

BOOK_VERSION ?= $(GARVERSION)

BOOK_FILENAME ?= $(if $(BOOK_NAME),$(BOOK_NAME),$(basename $(*F)))$(if $(BOOK_VERSION),-$(BOOK_VERSION),)

BOOK_EXTRA_SRC = $(word 1,$(subst :, ,$(BOOK_EXTRA)))
BOOK_EXTRA_DST = $(word 2,$(subst :, ,$(BOOK_EXTRA)))

include $(addprefix $(GARDIR)/,$(sort $(HTMLHELP_EXTRA_LIBS)))

htmlhelp: build
	$(DONADA)

# books	- Archive HTML Help books

BOOKARCHIVE_TARGETS = $(addprefix do-bookarchive/,$(BOOKS))

bookarchive: htmlhelp $(BOOKARCHIVE_TARGETS) ;

do-bookarchive/%:
	@$(foreach FILE,$(wildcard $(WORKDIR)/$(BOOK_FILENAME).*), \
		echo -e " $(WORKCOLOR)==> Copying $(BOLD)$(FILE)$(NORMALCOLOR)"; \
		cp -a $(FILE) $(BOOKARCHIVEDIR)/ ;)
