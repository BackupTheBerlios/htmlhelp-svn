# DevHelp books generation

all: devhelp


# devhelp	- Generate DevHelp books.

DEVHELP_TARGETS = $(addsuffix .tgz,$(basename $(filter %.sgml %.texi %.texinfo %.txi %.xml,$(BOOKS)))) $(DEVHELP_EXTRA_TARGETS)

devhelp: build pre-devhelp $(DEVHELP_TARGETS) post-devhelp
	$(DONADA)

# returns true if DevHelp books have completed successfully, false otherwise
devhelp-p:
	@$(foreach COOKIEFILE,$(DEVHELP_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


# install	- Install DevHelp books
post-install: devhelp-post-install

devhelp-post-install:
ifdef GARVERSION
	$(foreach FILE,$(DEVHELP_TARGETS), \
		cp -a $(FILE) $(DESTDIR)/$(notdir $(addsuffix -$(GARVERSION)$(suffix $(FILE)),$(basename $(FILE)))) ;)
else
	$(foreach FILE,$(DEVHELP_TARGETS), \
		cp -a $(FILE) $(DESTDIR) ;)
endif


# validate	- Validate DevHelp books
DEVHELP_DTD = $(GARDIR)/stylesheets/devhelp-1.dtd
XMLLINT = xmllint
XMLLINT_FLAGS = --noout --dtdvalid $(DEVHELP_DTD)

validate: devhelp pre-validate validate_target post-validate
	$(DONADA)

validate_target:
	$(foreach FILE,$(DEVHELP_TARGETS), \
		tar -xzf $(FILE) -O book.devhelp | \
		$(XMLLINT) $(XMLLINT_FLAGS) -)


#################### DEVHELP RULES ####################

# DocBook SGML

JADE = jade
JADE_FLAGS = \
	-V %devhelp-name%="$*" \
	-V %devhelp-version%="$(VERSION)"

DEVHELP_DSL = $(GARDIR)/stylesheets/docbook/devhelp.dsl

ifdef DSSSL
DSSSL_ = $(WORKDIR)/devhelp.dsl

$(DSSSL_): $(DEVHELP_DSL)
	sed -e "s@docbook\.dsl@$(DSSSL)@" $< > $@
else
DSSSL_ = $(DEVHELP_DSL)
endif

devhelp.%: %.sgml $(DSSSL)
	rm -rf $@
	mkdir -p $@
	cd $@ && $(JADE) -t sgml -i html $(JADE_FLAGS) -d $(DSSSL_) $(DCL) ../$<
	mkdir -p $@/book
	mv $@/*.html $@/book
ifdef FIGURES
	cp -r $(FIGURES) $@/book
endif


# DocBook XML (using XSL)

XSLTPROC = xsltproc 
XSLTPROC_FLAGS = \
	--docbook
XSLTPROC_FLAGS_DEVHELP = \
	--stringparam "devhelp.name" "$(*F)" \
	--stringparam "devhelp.version" "$(GARVERSION)"

DEVHELP_XSL = $(GARDIR)/stylesheets/docbook/devhelp.xsl

ifdef DSSSL
XSL_ = $(WORKDIR)/devhelp.xsl

$(XSL_): $(DEVHELP_XSL)
	sed -e "s@docbook\.xsl@$(XSL)@" $< > $@
else
XSL_ = $(DEVHELP_XSL)
endif

%.devhelp: %.xml
	@rm -rf $@d
	@mkdir -p $@d
	$(foreach FIGURE,$(FIGURES), cp -r $(FIGURE) $@d;)
	$(XSLTPROC) $(XSLTPROC_FLAGS) $(XSLTPROC_FLAGS_DEVHELP) -o $@d/ $(XSL_) $<
	@touch $@
	
.PRECIOUS: %.devhelp
	

%.tgz: %.devhelp
	tar -czf $@ -C $<d book.devhelp book


include $(GARDIR)/docbook.lib.mk
