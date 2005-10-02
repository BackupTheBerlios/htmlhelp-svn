GARNAME = numpy
GARVERSION = 20010907

MASTER_SITES = http://numeric.scipy.org/numpydoc/
DISTFILES = \
	numdoc.htm \
	numpy.html \
	numpy-1.html \
	numpy-2.html \
	numpy-3.html \
	numpy-4.html \
	numpy-5.html \
	numpy-6.html \
	numpy-7.html \
	numpy-8.html \
	numpy-9.html \
	numpy-10.html \
	numpy-11.html \
	numpy-12.html \
	numpy-13.html \
	numpy-14.html \
	numpy-15.html \
	numpy-16.html \
	numpy-17.html \
	numpy-18.html \
	numpy-19.html \
	numpy-20.html \
	numpy-21.html \
	numpy-22.html \
	numpy-23.html \
	numpy.css \
	numpy-1.gif \
	numpy-2.gif \
	numpy-3.gif \
	numpy-5.gif \
	numpy-6.gif \
	numpy-8.gif \
	numpy-9.gif \
	numpy-11.gif

BOOKS = numpydoc

include ../../gar.mk

post-extract:
	patch -i $(FILEDIR)/numpy.css.patch $(WORKDIR)/numpy.css
	@$(MAKECOOKIE)

convert-devhelp/numpydoc:
	rm -rf $(SCRATCHDIR)
	mkdir -p $(SCRATCHDIR)
	cp -a $(FILEDIR)/book.devhelp $(SCRATCHDIR)
	mkdir -p $(SCRATCHDIR)/book
	cp -pL $(addprefix $(WORKDIR)/,$(DISTFILES)) $(SCRATCHDIR)/book
	@$(MAKECOOKIE)

convert-mshh/numpydoc:
	rm -rf $(SCRATCHDIR)
	mkdir -p $(SCRATCHDIR)
	cp -a $(FILEDIR)/Numeric.hhp $(SCRATCHDIR)
	cp -a $(FILEDIR)/Numeric.hhc $(SCRATCHDIR)
	cp -a $(FILEDIR)/Numeric.hhk $(SCRATCHDIR)
	cp -pL $(addprefix $(WORKDIR)/,$(DISTFILES)) $(SCRATCHDIR)
	@$(MAKECOOKIE)
