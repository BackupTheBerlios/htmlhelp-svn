HTMLHELP = ~/projects/htmlhelp

BOOKS = $(HTMLHELP)/books

export PYTHONPATH := $(HTMLHELP)/python:$(PYTHONPATH)

all: \
	$(patsubst %.htb,%.sql,$(wildcard $(BOOKS)/*.htb)) \
	$(patsubst %.chm,%.sql,$(wildcard $(BOOKS)/*.chm)) \
	$(patsubst %.tgz,%.sql,$(wildcard $(BOOKS)/*.tgz))


%.sql: %.htb
	python $(HTMLHELP)/python/scripts/hhsqldump $< > $@

%.sql: %.chm
	python $(HTMLHELP)/python/scripts/hhsqldump $< > $@

%.sql: %.tgz
	python $(HTMLHELP)/python/scripts/hhsqldump $< > $@

clean:
	rm -f $(BOOKS)/*.sql
