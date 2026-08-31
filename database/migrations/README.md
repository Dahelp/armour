# Database migrations

Apply migrations manually to a staging database before enabling the related
feature in production.

## Legacy URL redirects

1. Apply `20260828_001_create_legacy_url_redirect.sql`.
2. Prepare a UTF-8 CSV with `source_path,target_path,status_code,is_active` columns.
   Full source URLs are accepted only for armour-shina.ru and techtires.ru;
   targets must be local techtires.ru canonical URLs. Query strings and fragments
   are intentionally rejected because they are not part of the redirect key.
3. Validate it without changing the database:
   `php bin/import_legacy_redirects.php path/to/legacy-urls.csv`.
4. Resolve every reported duplicate, self-redirect, chain or cycle, then import:
   `php bin/import_legacy_redirects.php path/to/legacy-urls.csv --apply`.
5. Test every source URL and its final canonical URL.
6. Set `LEGACY_REDIRECTS_ENABLED=1` only after those checks pass.

Both paths are stored without a leading slash. Redirect targets must be local
canonical paths; the application deliberately rejects empty paths, loops and
path traversal attempts.

## Attribute-group aliases

Apply `20260831_004_fix_attribute_group_url_alias_ids.sql` once when upgrading
an existing database. It replaces the historical `urlid = 0` value with the
real attribute-group ID, allowing subsequent slug edits to update only the
intended alias.
