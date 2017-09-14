-- multi_query
CREATE TRIGGER `logs_change_logitems_count_on_insert` AFTER INSERT ON `logs` FOR EACH ROW BEGIN
  UPDATE logitems SET logitems.count = IF(logitems.count IS NULL OR logitems.count = '', 1, logitems.count + 1) WHERE logitems.id = NEW.logitem_id LIMIT 1;
END