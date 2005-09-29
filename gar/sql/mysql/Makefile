GARNAME = mysql
GARVERSION = 4.1.10a
MASTER_SITES = http://mirrors.sunsite.dk/mysql/Downloads/MySQL-4.1/
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
