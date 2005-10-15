SELECT
	alias AS name,
	GROUP_CONCAT(DISTINCT tag SEPARATOR ",") AS tags
FROM tag 
	INNER JOIN alias_tag ON tag_id = tag.id
	INNER JOIN alias ON alias.id = alias_id
GROUP BY alias
ORDER BY alias
