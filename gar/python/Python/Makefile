GARNAME = Python
GARVERSION = 2.2.1
MASTER_SITES = http://www.python.org/ftp/python/doc/$(GARVERSION)/
DISTFILES = html-$(GARVERSION).tar.bz2

DEVHELP_TARGETS = $(WORKDIR)/$(GARNAME)-$(GARVERSION).tgz

include ../../gar.mk

# See also:
# 
#  http://www.orgmf.com.ar/condor/pytstuff.html
#  http://alldunn.com/python/

$(WORKDIR)/book:
	rmdir $(WORKDIR)/$(GARNAME)-$(GARVERSION)
	mkdir $(WORKDIR)/book
	-mv $(WORKDIR)/* $(WORKDIR)/book

$(WORKDIR)/book.devhelp: $(FILEDIR)/py2devhelp.py $(WORKDIR)/book
	cd $(WORKDIR)/book && python ../../$(FILEDIR)/py2devhelp.py ../book

$(WORKDIR)/$(GARNAME)-$(GARVERSION).tgz: $(WORKDIR)/book.devhelp $(WORKDIR)/book
	tar -czf $@  -C $(WORKDIR) book book.devhelp
