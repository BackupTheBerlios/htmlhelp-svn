# Rules for Microsoft Html Help books generation


ifndef HTMLHELP_LIB_MK
HTMLHELP_LIB_MK := 1


# DocBook XML (using XSL)

XSLTPROC = xsltproc 
XSLTPROC_FLAGS = \
	--docbook
XSLTPROC_FLAGS_HTMLHELP = \
	--stringparam "generate.toc" "" \
	--stringparam "htmlhelp.chm" "$(*F).chm" \
	--stringparam "htmlhelp.hhp" "$(*F).hhp" \
	--stringparam "htmlhelp.hhc" "$(*F).hhc" \
	--stringparam "htmlhelp.hhk" "$(*F).hhk" \
	--param htmlhelp.use.hhk 1 \
	--param htmlhelp.autolabel 1 \
	--param chapter.autolabel 1 \
	--param appendix.autolabel 1 \
	--param section.autolabel 1 \
	--param section.label.includes.component.label 1 \
	--param chunk.first.sections 1

HTMLHELP_XSL = /usr/share/sgml/docbook/stylesheet/xsl/nwalsh/htmlhelp/htmlhelp.xsl


#%.hhp %.hhc %.hhk: %.xml
htmlhelp.%: %.xml
	rm -rf $@
	mkdir -p $@
	$(XSLTPROC) $(XSLTPROC_FLAGS) $(XSLTPROC_FLAGS_HTMLHELP) -o $@/ $(HTMLHELP_XSL) $<
ifdef FIGURES
	cp -r $(FIGURES) $@
endif


include $(GARDIR)/docbook.lib.mk


endif
