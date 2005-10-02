GARNAME = gcc
GARVERSION = 4.0.2
MASTER_SITES = ftp://ftp.gnu.org/pub/gnu/$(GARNAME)/$(GARNAME)-$(GARVERSION)/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2
PATCHFILES = gar-base.diff

BOOKS = \
	$(WORKSRC)/gcc/doc/cpp.texi \
	$(WORKSRC)/gcc/doc/cppinternals.texi \
	$(WORKSRC)/gcc/doc/gcc.texi \
	$(WORKSRC)/gcc/doc/gccint.texi \
	$(WORKSRC)/gcc/fortran/gfortran.texi \
	$(WORKSRC)/gcc/java/gcj.texi \
	$(WORKSRC)/gcc/ada/gnat_rm.texi \
	$(WORKSRC)/gcc/ada/gnat-style.texi \
#	libstdc++

ifneq ($(shell which gnat),)
BOOKS += $(WORKSRC)/gcc/ada/gnat_ug_unx.texi
endif
	
NODIST_BOOKS = \
	$(WORKSRC)/libiberty/libiberty.texi

include ../../gar.mk

$(WORKSRC)/gcc/ada/gnat_ug_unx.texi:
	$(MAKE) -C $(@D) doctools/xgnatug $(@F)

convert-mshh/libstdc++:
	rm -rf $(SCRATCHDIR)
	cd $(WORKSRC)/libstdc++-v3 && $(SHELL) docs/doxygen/run_doxygen --mode=user --host_alias= . .
	mv html_user $(SCRATCHDIR)
	@$(MAKECOOKIE)
