# DevHelp books generation

all: devhelp

# devhelp	- Generate DevHelp books.
ifdef GARVERSION
TAG = -$(GARVERSION)
else
TAG =
endif

DEVHELP_TARGETS ?= $(addsuffix $(TAG).tgz,$(basename $(filter %.sgml %.texi %.texinfo %.txi %.xml,$(BOOKS))))

devhelp: build pre-devhelp $(DEVHELP_TARGETS) post-devhelp
	$(DONADA)

# returns true if DevHelp books have completed successfully, false otherwise
devhelp-p:
	@$(foreach COOKIEFILE,$(DEVHELP_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


post-install:
	$(foreach FILE,$(DEVHELP_TARGETS), cp -a $(FILE) $(DESTDIR)/devhelp ;)

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


#################### XML RULES ####################
# Rules to generate DocBook XML books


# Texinfo

# NOTE: This uses a local version of texinfo from CVS where I try to fix some
# of the bugs of makeinfo DocBook XML output until they're accepted upstream.
#MAKEINFO = makeinfo 
MAKEINFO = /home/jfonseca/projects/htmlhelp/texinfo/makeinfo/makeinfo
MAKEINFO_FLAGS = --docbook --ifinfo

%.xml: %.txi
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)
	
%.xml: %.texi
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)

%.xml: %.texinfo
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)


#################### DEVHELP RULES ####################

# DocBook SGML

JADE = jade
JADE_FLAGS = \
	-V %devhelp-name%="$*" \
	-V %devhelp-version%="$(VERSION)"

DEVHELP_DSL = $(GARDIR)/stylesheets/devhelp.dsl

ifdef DSSSL
DSSSL_ = $(WORKDIR)/devhelp.dsl

$(DSSSL_): $(DEVHELP_DSL)
	sed -e "s@docbook\.dsl@$(DSSSL)@" $< > $@
else
DSSSL_ = $(DEVHELP_DSL)
endif

%$(TAG).tgz: %.sgml $(DSSSL)
	cd $(WORKDIR) && $(JADE) -t sgml -i html $(JADE_FLAGS) -d $(DSSSL_) $(DCL) ../$<
	mkdir -p $(WORKDIR)/book
	mv $(WORKDIR)/*.html $(WORKDIR)/book
ifdef FIGURES
	cp -r $(FIGURES) $(WORKDIR)/book
endif
	tar -czf $@ -C $(WORKDIR) book.devhelp book
	rm -rf $(WORKDIR)/book.devhelp $(WORKDIR)/book


# DocBook XML (using XSL)

XSLTPROC = xsltproc 
XSLTPROC_FLAGS = \
	--docbook \
	--stringparam "generate.toc" "" \
	--stringparam "devhelp.spec" "book.devhelp" \
	--stringparam "devhelp.name" "$(*F)" \
	--stringparam "devhelp.version" "$(GARVERSION)" \
	--param devhelp.autolabel 1

DEVHELP_XSL = $(GARDIR)/stylesheets/devhelp.xsl

ifdef DSSSL
XSL_ = $(WORKDIR)/devhelp.xsl

$(XSL_): $(DEVHELP_XSL)
	sed -e "s@docbook\.xsl@$(XSL)@" $< > $@
else
XSL_ = $(DEVHELP_XSL)
endif

%$(TAG).tgz : %.xml
	mkdir -p $(WORKDIR)/book
	$(XSLTPROC) $(XSLTPROC_FLAGS) -o $(WORKDIR)/book/ $(XSL_) $<
	mv $(WORKDIR)/book/book.devhelp $(WORKDIR)
ifdef FIGURES
	cp -r $(FIGURES) $(WORKDIR)/book
endif
	tar -czf $@ -C $(WORKDIR) book.devhelp book
	rm -rf $(WORKDIR)/book.devhelp $(WORKDIR)/book
