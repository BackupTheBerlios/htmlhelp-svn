#!/usr/bin/env python

import sys

sys.stdout.write(
"""CREATE TEMPORARY TABLE temp_book_tag (
  name varchar(31) NOT NULL,
  tag varchar(31) NOT NULL
);
""")

sys.stdout.write(
"""INSERT
INTO temp_book_tag (name, tag)
VALUES """)
sep = ''
for line in sys.stdin:
	line = line.rstrip()
	name, tags = line.split()
	tags = [tag.strip() for tag in tags.split(',')]

	if name:
		for tag in tags:
			sys.stdout.write(sep)
			sys.stdout.write('("%s", "%s")' % (name, tag))
			sep = ',\n\t'
sys.stdout.write(""";

""")

sys.stdout.write(
"""INSERT IGNORE
INTO book_tag (tag_id, book_id)
SELECT tag.id, book_id
FROM tag
LEFT JOIN temp_book_tag USING(tag)
LEFT JOIN metadata ON value = temp_book_tag.name
WHERE metadata.name = 'name';

""")
sys.stdout.write(
"""DROP /*!40000 TEMPORARY */ TABLE temp_book_tag;

""")
