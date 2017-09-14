-- multi_query
CREATE TRIGGER `termusages_change_terms_count_usercount_on_insert` AFTER INSERT ON `termusages` FOR EACH ROW BEGIN
	IF(NEW.related = 'users') THEN
		UPDATE IGNORE terms SET terms.usercount = IF(terms.usercount IS NULL OR terms.usercount = '', 1, terms.usercount + 1) WHERE	terms.id = NEW.term_id LIMIT 1;
	ELSE
		UPDATE IGNORE terms SET terms.count = IF(terms.count IS NULL OR terms.count = '', 1, terms.count + 1) WHERE	terms.id = NEW.term_id LIMIT 1;
	END IF;
END