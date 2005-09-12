# convert a

# values
s/'/\\'/g
s/.*/	('&'),/

# insert statement
1i\
REPLACE\
INTO stop_word\
	(lexeme)\
VALUES

# replace last comma by a semi-colon
$s/,$/;/
