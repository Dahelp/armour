# Permanent production deployment

Changes pushed to `main` are published by **Deploy production over FTPS**
(`.github/workflows/deploy-production-ftps.yml`). A normal deployment needs only
`git push origin main`; no new deployment workflow or local GitHub token is needed.
GitHub Actions uses the existing `production` environment and hosting secrets.

The workflow validates PHP, runs compatibility tests, installs production
dependencies and uploads the source delta since its last successful run.
After a failed deployment, rerun the workflow: the baseline remains the previous
successful run, so failed or skipped changes are included again. Concurrent
production deployments are serialized. Manual runs are restricted to `main`.

Transfers require encrypted FTPS (control and data channels), TLS 1.2 or newer,
the checked-in hosting certificate and the SHA-256 public-key pin. There is no
plaintext fallback or temporary HTTP PHP runner. The hosting certificate uses
a legacy RSA key, requiring the cipher security level exception in the script.
When the host changes its certificate, verify the replacement through the hosting
provider before updating `ops/production/grass-ftps.pem` and the pin in
`ops/production/deploy_ftps.py`. A mismatched key stops deployment.

The server keeps its existing `.env`, user uploads and database. Only tracked
application/configuration/static files and installed Composer dependencies are
published. Deleted source files are removed. Authoritative Composer maps are
regenerated for PHP changes. Dependency changes publish the installed vendor tree.
Database migrations and environment changes require a separate explicit operation.

The final check requests the public home, category, product and size pages.
PHP cache revalidation is allowed time to finish without a public reset endpoint.
GitHub marks the deployment successful only after these checks pass. Failed
transfers can leave a partial source update; rerun promptly after fixing the cause.

The older manual FTP workflows remain historical and are not used by this process.
For local GitHub diagnostics on this Windows setup, network sandbox restrictions
can misleadingly appear as invalid CLI authentication; check outside the sandbox
before attempting to replace credentials.
