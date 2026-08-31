# Database migrations

Apply migrations manually to a staging database before enabling the related
feature in production.

## Legacy URL redirects

1. Apply `20260828_001_create_legacy_url_redirect.sql`.
2. Import the verified `source_path` to `target_path` map.
3. Test every source URL and its final canonical URL.
4. Set `LEGACY_REDIRECTS_ENABLED=1` only after those checks pass.

Both paths are stored without a leading slash. Redirect targets must be local
canonical paths; the application deliberately rejects empty paths, loops and
path traversal attempts.

## Attribute-group aliases

Apply `20260831_004_fix_attribute_group_url_alias_ids.sql` once when upgrading
an existing database. It replaces the historical `urlid = 0` value with the
real attribute-group ID, allowing subsequent slug edits to update only the
intended alias.
