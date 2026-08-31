-- Existing attribute-group aliases were historically stored with urlid = 0.
-- Link each unambiguous alias to its real group so future edits update the right row.
UPDATE url_alias AS ua
INNER JOIN attribute_group AS ag ON ag.url_params = ua.sef
SET ua.urlid = ag.id
WHERE ua.urlid = 0
  AND ag.url_params <> '';
