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

The HTTP audit performs step 5 automatically and writes both CSV and JSON:

`php bin/audit_legacy_redirects.php path/to/legacy-urls.csv --base-url=https://techtires.ru`

Run it only after the redirect table has been imported and redirects are enabled
on staging or production. A row passes only when the old path returns one 301/308,
the final response is 200, its URL and canonical equal `target_path`, and neither
the HTML nor `X-Robots-Tag` contains `noindex`. The command exits with code 1 when
at least one URL fails, making it suitable for deployment checks.

To build a first map from an old sitemap and a database dump without executing
the dump, run:

`php bin/build_legacy_url_map.php old-sitemap.xml database.sql tmp/reports/legacy-url-map`

Only public `url_alias` rows are considered. Exact paths are written to
`legacy-urls-ready.csv`; missing and ambiguous paths go to
`legacy-urls-review.csv` and are never activated automatically.
Legacy `crossing-*` paths are marked `deferred_crossing`: keep them reserved
until cross-number data is migrated, then publish the same paths or map them to
their exact replacements instead of redirecting them to generic categories.

## Legacy articles and news

Build a reviewed JSON package first. Import is dry-run by default:

`php bin/import_legacy_content.php tmp/reports/legacy-content/legacy-content-ready.json`

After reviewing the cleaned HTML and migrating referenced images, use `--apply`.
Imported rows are always hidden drafts. The transaction is rolled back if any
content or `url_alias` slug already exists; publishing remains a separate manual
SEO/editorial decision.

Migrate verified legacy article images before building the final package:

`php bin/migrate_legacy_content_images.php legacy-content.json legacy-content-local.json`

The command accepts images only from armour-shina.ru and validates their real
MIME type and dimensions. Merge content redirects with the product/category map:

`php bin/merge_legacy_redirect_maps.php legacy-urls-final.csv legacy-urls-stage3.csv legacy-content-redirects.csv`

Both paths are stored without a leading slash. Redirect targets must be local
canonical paths; the application deliberately rejects empty paths, loops and
path traversal attempts.

## Attribute-group aliases

Apply `20260831_004_fix_attribute_group_url_alias_ids.sql` once when upgrading
an existing database. It replaces the historical `urlid = 0` value with the
real attribute-group ID, allowing subsequent slug edits to update only the
intended alias.
