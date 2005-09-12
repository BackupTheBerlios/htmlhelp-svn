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
	export-tags.sql

export-htmlhelp.sql: MYSQLDUMP_FLAGS += --add-drop-table --no-data

export-tags.sql: MYSQLDUMP_FLAGS += --no-create-info
export-tags.sql: TABLES = tag alias_tag

export-%:
	$(MYSQLDUMP) \
		$(MYSQLDUMP_FLAGS) \
		$(DATABASE) $(TABLES) \
	| sed -e '/^\/\*!.*\*\/;$$/d' > $*

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

