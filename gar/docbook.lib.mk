# Rules to generate DocBook XML books

ifndef DOCBOOK_LIB_MK
DOCBOOK_LIB_MK := 1


# NOTE: This tries to use a local version of texinfo from CVS where I try to
# fix some of the bugs of makeinfo DocBook XML output until they're accepted
# upstream.
MAKEINFO_ALT = ~/projects/htmlhelp/texinfo/makeinfo/makeinfo
MAKEINFO = $(shell [ -x $(MAKEINFO_ALT) ] && echo $(MAKEINFO_ALT) || echo makeinfo)
MAKEINFO_FLAGS = --docbook --ifinfo


%.xml: %.txi
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)
	
%.xml: %.texi
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)

%.xml: %.texinfo
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)


endif
