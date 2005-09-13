# Makefile


# Variables

DATABASE = htmlhelp
USERNAME = htmlhelp
# TODO: deal with passwords (securely?)

MYSQL = mysql
MYSQL_FLAGS = \
	--user=$(USERNAME)

MYSQLDUMP = mysqldump
MYSQLDUMP_FLAGS = \
	$(MYSQL_FLAGS) \
	--skip-lock-tables \
	--compatible=mysql323


# Default targets

default: $(patsubst %.txt,%.sql,$(wildcard *.txt))

.PHONY: default


# Export database

export: \
	export-htmlhelp.sql \
	export-tags.txt

export-htmlhelp.sql: MYSQLDUMP_FLAGS += --add-drop-table --no-data

export-tags.txt:
	$(MYSQL) \
		$(MYSQL_FLAGS) \
		-e 'SELECT tag FROM tag ORDER BY tag' \
		$(DATABASE) \
	| tail +2 > tags.txt

export-%.sql:
	$(MYSQLDUMP) \
		$(MYSQLDUMP_FLAGS) \
		$(DATABASE) $(TABLES) \
	| sed -e '/^\/\*!.*\*\/;$$/d' > $*.sql

.PHONY: export export-%


# Import initial database

import: \
	import-htmlhelp.sql \
	import-stop_words.sql \
	import-tags.sql

import-%: %
	$(MYSQL) $(MYSQL_FLAGS) $(DATABASE) < $<

.PHONY: import import-%


# Implicit rules

%.sql: %.sed %.txt
	sed -f $^ > $@


# Clean

clean:
	-rm -f $(wildcard *.sql)

.PHONY: clean

