GARNAME = gcc
GARVERSION = 3.4.3
MASTER_SITES = \
	ftp://ftp.gnu.org/pub/gnu/$(GARNAME)/releases/$(GARNAME)-$(GARVERSION)/ \
	ftp://gcc.gnu.org/pub/$(GARNAME)/releases/$(GARNAME)-$(GARVERSION)/ \
	ftp://sources.redhat.com/pub/$(GARNAME)/releases/$(GARNAME)-$(GARVERSION)/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2
PATCHFILES = gar-base.diff

BOOKS = \
	$(WORKSRC)/gcc/doc/cpp.texi \
	$(WORKSRC)/gcc/doc/cppinternals.texi \
	$(WORKSRC)/gcc/doc/gcc.texi \
	$(WORKSRC)/gcc/doc/gccint.texi \
	$(WORKSRC)/gcc/f/g77.texi \
	$(WORKSRC)/gcc/java/gcj.texi \
	$(WORKSRC)/gcc/ada/gnat_rm.texi \
	$(WORKSRC)/gcc/ada/gnat-style.texi \
	$(WORKSRC)/libstdc++-v3/docs/doxygen/libstdc++.mshh

ifneq ($(shell which gnat),)
BOOKS += $(WORKSRC)/gcc/ada/gnat_ug_unx.texi
endif
	
NODIST_BOOKS = \
	$(WORKSRC)/libiberty/libiberty.texi

include ../../gar.mk

MAKEINFO_FLAGS += --force
MAKEINFO_FLAGS += -I ../doc/include

$(WORKSRC)/gcc/ada/gnat_ug_unx.texi:
	$(MAKE) -C $(@D) doctools/xgnatug $(@F)

$(WORKSRC)/libstdc++-v3/docs/doxygen/libstdc++.mshh:
	cd $(WORKSRC)/libstdc++-v3 && $(SHELL) docs/doxygen/run_doxygen --mode=user . .
	ln -sf html_user $@d
	@touch $@
