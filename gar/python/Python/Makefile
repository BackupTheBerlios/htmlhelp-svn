# See also:
# 
#  http://www.orgmf.com.ar/condor/pytstuff.html
#  http://alldunn.com/python/

GARNAME = Python
GARVERSION = 2.2.1
MASTER_SITES = http://www.python.org/ftp/python/doc/$(GARVERSION)/
DISTFILES = html-$(GARVERSION).tar.bz2

EXTRACTDIR = $(WORKDIR)/html

DEVHELP_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).tgz
CHM_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).chm
HTB_EXTRA_TARGETS = $(WORKDIR)/$(GARNAME).htb


include ../../gar.mk


$(WORKDIR)/devhelp.$(GARNAME): $(FILEDIR)/py2devhelp.py
	rm -rf $@
	mkdir -p $@
	cp -a $(WORKDIR)/html $@/book
	cd $@/book && python ../../../$(FILEDIR)/py2devhelp.py ../book

$(WORKDIR)/htmlhelp.$(GARNAME): $(FILEDIR)/pythlp.py
	rm -rf $@
	cp -a $(WORKDIR)/html $@
	cd $@ && python ../../$(FILEDIR)/pythlp.py $(GARNAME)
