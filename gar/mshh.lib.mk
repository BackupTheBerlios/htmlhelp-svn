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

%.mshh: %.xml
	@rm -rf $@d
	@mkdir -p $@d
	$(XSLTPROC) $(XSLTPROC_FLAGS) $(XSLTPROC_FLAGS_HTMLHELP) -o $@d/ $(HTMLHELP_XSL) $<
	$(foreach FIGURE,$(FIGURES), cp -r $(FIGURE) $@d;)
	@touch $@


# Texinfo (using texi2html)

#TEXI2HTML = ~/projects/htmlhelp/texi2html/cvs/texi2html
TEXI2HTML = texi2html
TEXI2HTML_FLAGS = 
TEXI2HTML_FLAGS_HTMLHELP = --init-file chm.init

%.mshh: %.texi
	@rm -rf $@d $*
	cd $(<D) && $(TEXI2HTML) $(TEXI2HTML_FLAGS) $(TEXI2HTML_FLAGS_HTMLHELP) $(<F)
	mv $* $@d
	@touch $@
	
%.mshh: %.texinfo
	@rm -rf $@d $*
	cd $(<D) && $(TEXI2HTML) $(TEXI2HTML_FLAGS) $(TEXI2HTML_FLAGS_HTMLHELP) $(<F)
	mv $* $@d
	@touch $@
	

.PRECIOUS: %.mshh

endif
