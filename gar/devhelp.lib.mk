# DevHelp books generation

all: devhelp


# devhelp	- Generate DevHelp books.

DEVHELP_TARGETS = $(addsuffix .tgz,$(basename $(BOOKS)))
HTMLHELP_TARGETS += $(DEVHELP_TARGETS)

devhelp: build pre-devhelp $(DEVHELP_TARGETS) post-devhelp
	$(DONADA)

# returns true if DevHelp books have completed successfully, false otherwise
devhelp-p:
	@$(foreach COOKIEFILE,$(DEVHELP_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


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

%.devhelp: %.sgml $(DSSSL)
	@rm -rf $@d
	@mkdir -p $@d
	cd $@d && $(JADE) -t sgml -i html $(JADE_FLAGS) -d $(DSSSL_) $(DCL) ../$(<F)
	@mkdir -p $@d/book
	@mv $@/*.html $@/book
	$(foreach FIGURE,$(FIGURES), cp -r $(FIGURE) $@d/book;)


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
	$(XSLTPROC) $(XSLTPROC_FLAGS) $(XSLTPROC_FLAGS_DEVHELP) -o $@d/ $(XSL_) $<
	$(foreach FIGURE,$(FIGURES), cp -r $(FIGURE) $@d/book;)
	@touch $@
	
.PRECIOUS: %.devhelp
	

%.tgz: %.devhelp
	tar -czf $@ -C $<d book.devhelp book
	@$(MAKECOOKIE)

%.tgz: empty-%.tgz
	@$(MAKECOOKIE)


include $(GARDIR)/docbook.lib.mk
