-- multi_query
CREATE TRIGGER `termusages_change_terms_count_usercount_on_delete` AFTER DELETE ON `termusages` FOR EACH ROW BEGIN
	IF(OLD.related = 'users') THEN
		UPDATE IGNORE terms SET terms.usercount = IF(terms.usercount IS NULL OR terms.usercount = '' OR terms.usercount < 1, 0, terms.usercount - 1) WHERE	terms.id = OLD.term_id LIMIT 1;
	ELSE
		UPDATE IGNORE terms SET terms.count = IF(terms.count IS NULL OR terms.count = '' OR terms.count < 1, 0, terms.count - 1) WHERE	terms.id = OLD.term_id LIMIT 1;
	END IF;
END