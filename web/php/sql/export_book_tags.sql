SELECT 
	metadata.value AS name,
	GROUP_CONCAT(DISTINCT tag SEPARATOR ",") AS tags
FROM tag 
	LEFT JOIN book_tag ON tag.id = tag_id 
	LEFT JOIN metadata USING (book_id) 
WHERE metadata.name='name' 
GROUP BY metadata.value 
ORDER BY metadata.value
