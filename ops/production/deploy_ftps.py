"""Publish the source delta over certificate- and public-key-pinned FTPS."""
import argparse
import os
from pathlib import Path
import subprocess
from urllib.parse import quote


ROOT = Path(__file__).resolve().parents[2]
PIN = "sha256//7LxfqpWQuC6EJX5sB1h2e3SOWKCEg1a7V1xfDIZ6ySY="


def publishable(path):
    return (
        path in (".htaccess", "robots.txt", "composer.json", "composer.lock")
        or path.startswith(("app/", "config/", "resources/", "vendor/ishop/core/"))
        or (path.startswith("public/") and not path.startswith("public/uploads/"))
    ) and path not in ("config/legacy-admin.php",) and ".." not in Path(path).parts


def plan(base):
    changed = subprocess.check_output(
        ["git", "diff", "--name-only", "--no-renames", "-z", base, "HEAD"], cwd=ROOT
    ).decode().split("\0")
    paths = sorted(set(filter(publishable, filter(None, changed))))
    uploads = [path for path in paths if (ROOT / path).is_file()]
    deletes = [path for path in paths if not (ROOT / path).exists()]
    if "composer.lock" in paths or "composer.json" in paths:
        # A dependency change requires the complete installed dependency tree.
        uploads.extend(p.relative_to(ROOT).as_posix() for p in (ROOT / "vendor").rglob("*") if p.is_file())
    elif any(path.endswith(".php") for path in paths):
        # Production uses an authoritative class map: ship regenerated maps
        # whenever PHP source changes, including newly added classes.
        uploads.append("vendor/autoload.php")
        uploads.extend(p.relative_to(ROOT).as_posix() for p in (ROOT / "vendor/composer").rglob("*") if p.is_file())
    return sorted(set(uploads)), deletes


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--base", required=True)
    parser.add_argument("--plan", action="store_true")
    args = parser.parse_args()
    uploads, deletes = plan(args.base)
    print(f"Upload {len(uploads)} files; remove {len(deletes)} files", flush=True)
    for path in uploads:
        print(f"UPLOAD {path}", flush=True)
    for path in deletes:
        print(f"DELETE {path}", flush=True)
    if args.plan:
        return
    username = os.environ["FTP_USERNAME"]
    password = os.environ["FTP_PASSWORD"]
    if not username or not password:
        raise SystemExit("Hosting credentials are missing")
    connection = [
        "curl", "--fail", "--silent", "--show-error", "--ssl-reqd", "--tlsv1.2",
        "--cacert", str(ROOT / "ops/production/grass-ftps.pem"),
        "--pinnedpubkey", PIN, "--ciphers", "DEFAULT:@SECLEVEL=1",
        "--connect-timeout", "20", "--max-time", "180", "--retry", "2",
        "--user", username + ":" + password,
    ]
    def transfer(options):
        # Do not use check=True: its exception would print credential arguments.
        if subprocess.run(connection + options, cwd=ROOT).returncode:
            raise SystemExit("FTPS transfer failed; production revision is not advanced")
    for path in uploads:
        transfer(["--ftp-create-dirs", "--upload-file", path,
                  "ftp://grass.gohost.ru:21//" + quote(path, safe="/")])
    for path in deletes:
        transfer(["--quote", "DELE /" + path, "ftp://grass.gohost.ru:21//"])


if __name__ == "__main__":
    main()
