# wxWindows HTB books generation
#
# See also:
# 
#   http://www.wxwindows.org/help.htm
#   http://www.wxwindows.org/manuals/2.4.0/wx499.htm#helpformat


all: htb


# htb	- Generate Compiled Html Help books.

HTB_TARGETS = $(addsuffix .htb,$(basename $(BOOKS)))
HTMLHELP_TARGETS += $(HTB_TARGETS)

htb: build pre-htb $(HTB_TARGETS) post-htb
	$(DONADA)

# returns true if the Compiled Html Help books have completed successfully, false otherwise
htb-p:
	@$(foreach COOKIEFILE,$(HTB_TARGETS), test -e $(COOKIEDIR)/$(COOKIEFILE) ;)


##################### HTB RULES ###################

%.htb: %.mshh
	cd $<d && zip -r ../$(@F) .
	@$(MAKECOOKIE)

%.htb: empty-%.htb
	@$(MAKECOOKIE)


include $(GARDIR)/mshh.lib.mk
