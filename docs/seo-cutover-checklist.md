# SEO cutover: armour-shina.ru → techtires.ru

## Current verified state

- TechTires runs on PHP 8.2 and HTTPS.
- The production sitemap contains 8,159 canonical URLs, including 7,181 unique
  cross-number paths.
- The imported legacy page map contains 764 active permanent redirects.
- The cross audit found 7,150 old `crossing-*.html` URLs that are safe for
  cutover. All 7,150 rows passed structural validation and a distributed live
  sample passed `301 → 200` with matching canonicals.
- Another 1,320 crossing URLs remain deferred: 1,286 are not present on
  TechTires, 26 require a web-server rule because the old path contains spaces,
  seven contain unsupported route aliases and one alias is invalid.
- Product pages expose Product/Offer JSON-LD.
- TechTires remains intentionally closed in `robots.txt` until cutover.
- armour-shina.ru still returns its own pages with HTTP 200 and therefore does
  not yet transfer ranking signals to TechTires.

## Cutover order

1. Run the GitHub Actions workflow `Production smoke test` and require success.
2. Submit a real test order/callback using an agreed test telephone and email;
   verify the order in admin and receipt of both SMTP messages, then mark or
   remove the test order. Automated smoke tests deliberately do not create a
   production order.
3. Run the manual workflow `Activate armour-shina.ru domain redirect`, enter
   `ACTIVATE-ARMOUR-REDIRECT` and select the verified FTP document root. The
   workflow backs up the current `.htaccess`, installs
   `ops/armour-shina-redirect/.htaccess` and verifies representative redirects.
   An equivalent hosting-panel rule may be used instead, but it must preserve
   the complete request path and query string.
4. Verify the old-domain homepage and at least 20 old URLs. Each must produce
   exactly one permanent redirect to the same path on techtires.ru; the
   TechTires legacy layer may then make one additional `.html` → canonical
   redirect. Do not redirect every request to the TechTires homepage.
5. Replace both `robots.txt` files on TechTires with
   `ops/production/robots.open.txt`, update the smoke-test expectation in the
   same release commit and run that production smoke test.
6. Add both domains and all protocol/`www` variants to Yandex Webmaster and
   Google Search Console. Submit `https://techtires.ru/sitemap.xml` and use the
   official site-move/change-of-address tool where available.
7. Keep armour-shina.ru, its TLS certificate and all redirects active for at
   least 12 months; longer is preferable.
8. During the first month, monitor 404/5xx responses, indexed pages, sitemap
   processing, crawl statistics and traffic daily. Add missing one-to-one
   redirects; never mask genuine missing pages by redirecting them to `/`.

## Acceptance criteria before opening indexing

- Production smoke test is green.
- Test checkout and SMTP delivery are confirmed manually.
- Old-domain redirects are active and preserve paths.
- `sitemap.xml` is valid and contains more than the homepage.
- Canonical URLs contain only `https://techtires.ru/...`.
- No redirect loops, cross-host chains or unexpected 404/5xx responses exist.
