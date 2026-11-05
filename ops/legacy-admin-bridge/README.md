# Private Armour admin access

The TechTires header link is rendered only for the configured owner's admin
account. The launch endpoint rechecks the database role and sends a signed,
single-use POST ticket valid for 45 seconds. The Armour gateway consumes its
nonce atomically, regenerates the PHP session, and issues a session-bound
access cookie valid for one hour. PHP and static assets use the same gate.
Public visitors retain the domain redirect to TechTires.

The installation workflow accepts the owner's TechTires email and optionally
an existing Armour login. Accounts are verified before installing either site.
If the owner has no old account, creating a persistent administrator requires
explicit user approval. After that approval, `create_private_account=true`
creates `tt-owner-<ID>` with the verified standard administrator role (2),
hidden visibility, no subscription and a NULL password. Other users are not
changed. The dedicated account cannot log in through the password form.

Keys are generated on deployment, stored only in private server configuration,
and excluded from Git and workflow artifacts. The old `.htaccess` is backed
up on its hosting account. Integration tests verify owner access, replay
rejection, session binding, private CSS, and unchanged public redirects.

To stop new access, remove `config/legacy-admin.php` on TechTires. To revoke
existing bridge sessions immediately, also remove `.owner-bridge-config.php`
on Armour. The dedicated account has no usable password. Restore the saved
`.htaccess` or `ops/armour-shina-redirect/.htaccess` to remove the bridge route.
