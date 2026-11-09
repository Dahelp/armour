"""Checks for production deployment scope and retry planning; no network access."""
import importlib.util
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch

spec = importlib.util.spec_from_file_location(
    "deploy_ftps", Path(__file__).resolve().parents[1] / "ops/production/deploy_ftps.py"
)
deploy = importlib.util.module_from_spec(spec)
spec.loader.exec_module(deploy)


class DeploymentTest(unittest.TestCase):
    def test_private_files_and_uploads_are_excluded(self):
        for path in (".env", "public/uploads/photo.jpg", "config/legacy-admin.php",
                     "tests/ExampleTest.php", "docs/readme.md", "app/../.env"):
            self.assertFalse(deploy.publishable(path), path)

    def test_retry_uses_requested_successful_baseline_and_keeps_deletions(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "app").mkdir()
            (root / "app/new.php").write_text("<?php", encoding="utf-8")
            (root / "vendor/composer").mkdir(parents=True)
            (root / "vendor/composer/autoload_classmap.php").write_text("<?php", encoding="utf-8")
            with patch.object(deploy, "ROOT", root), patch.object(
                deploy.subprocess, "check_output",
                return_value=b"app/new.php\0app/removed.php\0.env\0public/uploads/a.jpg\0"
            ) as git:
                uploads, deletes = deploy.plan("last-successful-sha")
            self.assertIn("last-successful-sha", git.call_args.args[0])
            self.assertEqual(deletes, ["app/removed.php"])
            self.assertEqual(uploads, ["app/new.php", "vendor/autoload.php",
                                       "vendor/composer/autoload_classmap.php"])

    def test_dependency_change_includes_new_package_files(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "composer.lock").write_text("{}", encoding="utf-8")
            (root / "vendor/package").mkdir(parents=True)
            (root / "vendor/package/new.php").write_text("<?php", encoding="utf-8")
            with patch.object(deploy, "ROOT", root), patch.object(
                deploy.subprocess, "check_output", return_value=b"composer.lock\0"
            ):
                uploads, deletes = deploy.plan("base")
            self.assertEqual(uploads, ["composer.lock", "vendor/package/new.php"])
            self.assertEqual(deletes, [])


if __name__ == "__main__":
    unittest.main()
