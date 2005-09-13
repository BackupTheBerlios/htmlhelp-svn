# convert a tag list to SQL statements

# values
s/'/\\'/g
s/.*/	('&'),/

# insert statement
1i\
REPLACE\
INTO tag\
	(tag)\
VALUES

# replace last comma by a semi-colon
$s/,$/;/
