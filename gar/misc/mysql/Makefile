GARNAME = mysql
GARVERSION = 4.0.15a
MASTER_SITES = http://mirrors.sunsite.dk/mysql/Downloads/MySQL-4.0/
DISTFILES = $(GARNAME)-$(GARVERSION).tar.gz

# See also:
#
# http://phlab.net/myman/
# http://phlab.net/html2hpp/

BOOKS = \
	$(WORKSRC)/Docs/mysql.texi

include ../../gar.mk
	
$(WORKSRC)/Docs/mysql.texi: $(WORKSRC)/Docs/manual.texi extract
	ln -s $(<F) $@
