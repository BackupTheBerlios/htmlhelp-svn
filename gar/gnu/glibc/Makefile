GARNAME = glibc
GARVERSION = 2.3.2
MASTER_SITES = ftp://ftp.gnu.org/pub/gnu/$(GARNAME)/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.bz2 $(GARNAME)-linuxthreads-$(GARVERSION).tar.bz2
PATCHFILES = gar-base.diff

CONFIGURE_SCRIPTS = $(WORKSRC)/configure
CONFIGURE_ARGS = --enable-add-ons=linuxthreads --disable-sanity-checks

BUILD_SCRIPTS = $(WORKSRC)/manual/Makefile
BUILD_ARGS = info

BOOKS = $(WORKSRC)/manual/libc.texinfo

include ../../gar.mk

MAKEINFO_FLAGS += --force

extract-$(GARNAME)-linuxthreads-$(GARVERSION).tar.bz2: EXTRACTDIR = $(WORKDIR)/$(GARNAME)-$(GARVERSION)
