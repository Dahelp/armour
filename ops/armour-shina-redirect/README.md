# armour-shina.ru cutover package

The `.htaccess` in this directory performs the domain-wide permanent redirect
from `armour-shina.ru` to `https://techtires.ru`, preserving the original path
and query string. Exact legacy-to-canonical mappings are then handled by the
verified TechTires redirect layer.

Use the manual GitHub Actions workflow `Activate armour-shina.ru domain
redirect` only for the final cutover. It requires the explicit confirmation
phrase `ACTIVATE-ARMOUR-REDIRECT`, checks TechTires first, backs up the old
`.htaccess` both on the FTP server and as a workflow artifact, installs the new
file through a temporary name, and verifies representative redirects.

The expected FTP document root is `/www/armour-shina.ru/`. Select `/` only when
the dedicated old-domain FTP account is chrooted directly into that directory.
The workflow does not open TechTires indexing; that is a separate release step.
