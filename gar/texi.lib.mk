# Rules to generate DocBook XML books from Texinfo

ifndef TEXI_LIB_MK
TEXI_LIB_MK := 1


# NOTE: This tries to use a local version of texinfo from CVS where I try to
# fix some of the bugs of makeinfo DocBook XML output until they're accepted
# upstream.
MAKEINFO = $(shell [ -x ~/projects/htmlhelp/texinfo/makeinfo/makeinfo ] && \
	   echo ~/projects/htmlhelp/texinfo/makeinfo/makeinfo || \
	   echo makeinfo)
MAKEINFO_FLAGS = --docbook --ifinfo


%.xml: %.txi
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)
	
%.xml: %.texi
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)

%.xml: %.texinfo
	cd $(@D) && $(MAKEINFO) $(MAKEINFO_FLAGS) -o $(@F) $(<F)


endif
