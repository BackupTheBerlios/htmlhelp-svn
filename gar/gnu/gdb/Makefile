GARNAME = gdb
GARVERSION = 6.0
MASTER_SITES = \
	ftp://ftp.gnu.org/pub/gcc/$(GARNAME)/ \
	ftp://sources.redhat.com/pub/$(GARNAME)/releases/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2

CONFIGURE_SCRIPTS = $(WORKSRC)/gdb/configure

BUILD_SCRIPTS = $(WORKSRC)/gdb/doc/Makefile
BUILD_ARGS = clean gdb.info
	
BOOKS = \
	$(WORKSRC)/gdb/doc/gdb.texinfo \
	$(WORKSRC)/gdb/doc/gdbint.texinfo

NOINST_BOOKS = \
	$(WORKSRC)/gdb/doc/stabs.texinfo

include ../../gar.mk

MAKEINFO_FLAGS += -I ../mi -I ../../readline/doc

