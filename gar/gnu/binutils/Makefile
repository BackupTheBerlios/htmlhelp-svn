GARNAME = binutils
GARVERSION = 2.15
MASTER_SITES = \
	ftp://ftp.gnu.org/gnu/$(GARNAME)/ \
	ftp://ftp.kernel.org/pub/linux/devel/$(GARNAME)/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2

CONFIGURE_SCRIPTS = \
	$(WORKSRC)/bfd/configure \
	$(WORKSRC)/binutils/configure \
	$(WORKSRC)/gas/configure \
	$(WORKSRC)/ld/configure
BUILD_SCRIPTS = \
	$(WORKSRC)/bfd/doc/Makefile \
	$(WORKSRC)/binutils/doc/Makefile \
	$(WORKSRC)/gas/doc/Makefile \
	$(WORKSRC)/ld/Makefile

BOOKS = \
	$(WORKSRC)/bfd/doc/bfd.texinfo \
	$(WORKSRC)/bfd/doc/bfdint.texi \
	$(WORKSRC)/binutils/doc/binutils.texi \
	$(WORKSRC)/gas/doc/as.texi \
	$(WORKSRC)/ld/ld.texinfo \
	$(WORKSRC)/ld/ldint.texinfo
NODIST_BOOKS = \
	$(WORKSRC)/liberty/libiberty.texi \
	$(WORKSRC)/etc/standards.texi

include ../../gar.mk

MAKEINFO_FLAGS += -I ../../bfd/doc -I ../bfd/doc

build-$(WORKSRC)/bfd/doc/Makefile: BUILD_ARGS = bfd.info
build-$(WORKSRC)/binutils/doc/Makefile: BUILD_ARGS = binutils.info
build-$(WORKSRC)/gas/doc/Makefile: BUILD_ARGS = as.info
build-$(WORKSRC)/ld/Makefile: BUILD_ARGS = ld.info
