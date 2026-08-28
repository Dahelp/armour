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

`CRON_TOKEN` is required for scheduled HTTP jobs. Prefer sending it in the
`X-Cron-Token` header. A `token` query parameter is supported for schedulers
that cannot send custom headers, but headers avoid leaking the token into URLs
and access logs.

Before switching traffic:

1. Rotate the old DB, SMTP and FTP passwords because they existed in Git.
2. Run `composer install --no-dev --classmap-authoritative` on PHP 8.2 or newer.
3. Keep `APP_DEBUG=0` in production.
4. Apply database migrations on staging first.
5. Leave `LEGACY_REDIRECTS_ENABLED=0` until the redirect map is verified.
