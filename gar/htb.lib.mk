# wxWindows HTB books generation
#
# See also:
# 
#   http://www.wxwindows.org/help.htm

all: htb

# htb	- Generate Compiled Html Help books.

HTB_TARGETS = $(addsuffix .htb,$(basename $(filter %.texi %.texinfo %.txi %.xml,$(BOOKS)))) $(HTB_EXTRA_TARGETS)

htb: build pre-htb $(HTB_TARGETS) post-htb
	$(DONADA)

# returns true if the Compiled Html Help books have completed successfully, false otherwise
htb-p:
	@$(foreach COOKIEFILE,$(HTB_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)

post-install: htb-post-install
	
# install	- Install Compiled Html Help books
htb-post-install:
ifdef GARVERSION
	$(foreach FILE,$(HTB_TARGETS), \
		cp -a $(FILE) $(DESTDIR)/$(addsuffix -$(GARVERSION)$(suffix $(FILE)),$(basename $(FILE))) ;)
else
	$(foreach FILE,$(HTB_TARGETS), \
		cp -a $(FILE) $(DESTDIR) ;)
endif


%.htb: htmlhelp.%
	cd $< && zip ../$(@F) *


include $(GARDIR)/htmlhelp.lib.mk
