# Rules for Microsoft Html Help books generation


ifndef MSHH_LIB_MK
MSHH_LIB_MK := 1


# DocBook XML (using XSL)

XSLTPROC = xsltproc 
XSLTPROC_FLAGS = 

XSLTPROC_FLAGS_HTMLHELP = \
	--stringparam "generate.toc" "" \
	--stringparam "htmlhelp.chm" "$(*F).chm" \
	--stringparam "htmlhelp.hhp" "$(*F).hhp" \
	--stringparam "htmlhelp.hhc" "$(*F).hhc" \
	--stringparam "htmlhelp.hhk" "$(*F).hhk" \
	--param htmlhelp.hhc.show.root 0 \
	--param htmlhelp.use.hhk 1 \
	--param htmlhelp.autolabel 1 \
	--param chapter.autolabel 1 \
	--param appendix.autolabel 1 \
	--param section.autolabel 1 \
	--param section.label.includes.component.label 1 \
	--param chunk.first.sections 1

HTMLHELP_XSL = /usr/share/sgml/docbook/stylesheet/xsl/nwalsh/htmlhelp/htmlhelp.xsl

xml-mshh/%: %
	@echo -e " $(WORKCOLOR)==> Converting $(BOLD)$*$(NORMALCOLOR)"
	$(XSLTPROC) $(XSLTPROC_FLAGS) $(XSLTPROC_FLAGS_HTMLHELP) -o $(SCRATCHDIR)/ $(HTMLHELP_XSL) $<


# Texinfo (using texi2html)

TEXI2HTML = texi2html
TEXI2HTML_FLAGS = 
TEXI2HTML_FLAGS_HTMLHELP = --init-file chm.init

texi-mshh/%: %
	@echo -e " $(WORKCOLOR)==> Converting $(BOLD)$*$(NORMALCOLOR)"
	$(TEXI2HTML) $(TEXI2HTML_FLAGS) $(TEXI2HTML_FLAGS_HTMLHELP) --out $(SCRATCHDIR) $*


# Common targets

pre-convert-mshh/%:
	@rm -rf $(SCRATCHDIR)
	@mkdir -p $(SCRATCHDIR)

convert-mshh/%.sgml: pre-convert-mshh/% sgml-mshh/%.sgml
	@true

convert-mshh/%.texi: pre-convert-mshh/% texi-mshh/%.texi
	@true

convert-mshh/%.texinfo: pre-convert-mshh/% texi-mshh/%.texinfo
	@true

convert-mshh/%.txi: pre-convert-mshh/% texi-mshh/%.txi
	@true

convert-mshh/%.xml: pre-convert-mshh/% xml-mshh/%.xml
	@true

post-convert-mshh/%: convert-mshh/%
	@$(foreach BOOK_EXTRA,$(BOOK_EXTRAS), \
		mkdir -p $(SCRATCHDIR)/$(BOOK_EXTRA_DST); \
		cp -a $(BOOK_EXTRA_SRC) $(SCRATCHDIR)/$(BOOK_EXTRA_DST);)
	@cd $(SCRATCHDIR) ; $(BOOK_PATCH)

endif
