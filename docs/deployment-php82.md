# PHP 8.2 deployment checklist

The application no longer keeps database, SMTP or FTP credentials in tracked
PHP files. Configure them as server environment variables or copy
`.env.example` to an ignored `.env` file and replace every placeholder.

Required for the storefront:

- `APP_URL`
- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Required for outgoing mail:

- `SMTP_HOST`
- `SMTP_PORT`
- `SMTP_PROTOCOL`
- `SMTP_USERNAME`
- `SMTP_PASSWORD`

FTP variables are only required by the administrative FTP import.

Outgoing mail is delivered by Symfony Mailer 7.4. Existing order, callback,
password recovery and newsletter call sites use a compatibility bridge during
the migration, so their public behaviour remains unchanged. SMTP credentials
are URL-encoded before the transport DSN is created. Use `ssl` for implicit TLS
(normally port 465) or `tls` for required STARTTLS (normally port 587).

`CRON_TOKEN` is required for scheduled HTTP jobs. Prefer sending it in the
`X-Cron-Token` header. A `token` query parameter is supported for schedulers
that cannot send custom headers, but headers avoid leaking the token into URLs
and access logs.

Before switching traffic:

1. Rotate the old DB, SMTP and FTP passwords because they existed in Git.
2. Apply `database/migrations/20260828_001_create_legacy_url_redirect.sql` and
   all following migrations in filename order. Migration `002` keeps root-level
   slug routing fast; migration `003` adds indexes for catalogue filters,
   product recommendations, reviews and galleries.
3. Run `composer install --no-dev --classmap-authoritative` on PHP 8.2 or newer.
4. Keep `APP_DEBUG=0` in production.
5. Apply database migrations on staging first.
6. Leave `LEGACY_REDIRECTS_ENABLED=0` until the redirect map is verified. The
   production map was verified and enabled on 2026-09-04; do not disable it
   while legacy URLs or the old domain remain indexed.
