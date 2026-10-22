#!/usr/bin/env bash
#
# Release the plugin.
#
# Usage:  ./release.sh 3.5.1
#         ./release.sh 3.5.1 --dry-run     # everything up to the archive, nothing published
#
# Does the whole release in one go, in the same order and with the same checks
# that were used by hand for 3.5.0:
#
#   1. verify the version is consistent and the tree is releasable
#   2. run the test suite
#   3. rebuild the front-end assets (and stop if that leaves uncommitted work)
#   4. build and verify the distribution archive
#   5. push main, tag and create the GitHub release as a PRE-RELEASE, so the
#      auto-updater cannot see it while the asset is being attached
#   6. upload the archive and verify the download contains the right version
#   7. flip the release to a full release and verify the updater resolves the
#      release asset (not the source zipball)
#
# Every step that talks to GitHub is checked; if anything fails the script stops
# before customers are affected.

set -euo pipefail

REPO="domos-technologies/domos-wordpress"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ZIP="$ROOT/domos-core.zip"

bold() { printf '\n\033[1m%s\033[0m\n' "$*"; }
ok()   { printf '  \033[32m✓\033[0m %s\n' "$*"; }
warn() { printf '  \033[33m!\033[0m %s\n' "$*"; }
die()  { printf '\n\033[31m✗ %s\033[0m\n' "$*" >&2; exit 1; }

DRY_RUN=false
VERSION=""
for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=true ;;
		-*) die "Unknown option: $arg" ;;
		*) VERSION="$arg" ;;
	esac
done

[ -n "$VERSION" ] || die "Usage: ./release.sh <version> [--dry-run]   e.g. ./release.sh 3.5.1"

cd "$ROOT"

bold "1. Preflight ($VERSION)"

command -v composer >/dev/null || die "composer is not installed."
command -v bun >/dev/null || die "bun is not installed."

[ -z "$(git status --porcelain)" ] || die "Working tree is dirty. Commit or stash first:
$(git status --short)"
git rev-parse -q --verify "refs/tags/$VERSION" >/dev/null && die "Tag $VERSION already exists."

if [ "$DRY_RUN" = false ]; then
	command -v gh >/dev/null || die "gh is not installed."
	[ "$(git branch --show-current)" = "main" ] || die "Not on main (on $(git branch --show-current))."
	gh release view "$VERSION" -R "$REPO" >/dev/null 2>&1 && die "Release $VERSION already exists."

	git fetch --quiet origin main
	[ "$(git rev-parse HEAD)" = "$(git rev-parse origin/main)" ] || die "Local main is not pushed. Push it first."

	ok "on main, clean tree, $VERSION is new, local == origin"
else
	warn "dry run: skipping the branch, remote and existing-release checks"
	ok "clean tree, $VERSION is not tagged yet"
fi

grep -q " \* Version: *$VERSION\$" domos-core.php \
	|| die "domos-core.php header does not say $VERSION."
grep -q "define('DOMOS_CORE_VERSION', '$VERSION');" domos-core.php \
	|| die "DOMOS_CORE_VERSION is not $VERSION."
grep -q "^## $VERSION\$" CHANGELOG.md \
	|| die "CHANGELOG.md has no '## $VERSION' section to use as release notes."
ok "version header, constant and changelog section agree"

bold "2. Tests"
composer test
ok "test suite passed"

bold "3. Assets"
bun run build
if [ -n "$(git status --porcelain)" ]; then
	if [ "$DRY_RUN" = true ]; then
		# A different toolchain can produce slightly different CSS (different
		# autoprefixer/caniuse data), so this is not a failure on CI.
		warn "the build changed tracked files here (toolchain differences):"
		git status --short | sed 's/^/      /'
	else
		die "The build changed tracked files. Commit them and re-run:
$(git status --short)"
	fi
else
	ok "assets rebuilt, nothing left to commit"
fi

bold "4. Package"
# package:clean first, so nothing from a previous build can survive into the archive.
bun run package >/dev/null
[ -f "$ZIP" ] || die "No archive at $ZIP."

php -r '
$pkg = "'"$ROOT"'/plugin/domos-core";
$version = "'"$VERSION"'";
$problems = [];

// Nothing from the test suite or the development tooling may ship.
foreach (["tests", "phpunit.xml", "vendor/bin", "vendor/phpunit", "vendor/laravel/pint",
          ".git", ".idea", "package.json", ".distignore", "playwright.config.ts"] as $path) {
    if (file_exists($pkg."/".$path)) { $problems[] = "leaked into the archive: ".$path; }
}

// Everything the plugin needs at runtime must be there, and the generated
// autoloader must not point at files that were left out.
foreach (["domos-core.php", "vendor/autoload.php", "src/Sync/SyncQueue.php",
          "resources/js/cdn/alpine-3.13.8.min.js", "public/css/admin.css",
          "storage/framework/views", "vendor/roots/acorn", "vendor/domos/schema"] as $path) {
    if (!file_exists($pkg."/".$path)) { $problems[] = "missing from the archive: ".$path; }
}

$targets = 0;
foreach (["autoload_psr4.php", "autoload_namespaces.php", "autoload_classmap.php", "autoload_files.php"] as $file) {
    $path = $pkg."/vendor/composer/".$file;
    if (!is_file($path)) { continue; }
    foreach ((array) require $path as $value) {
        foreach ((array) $value as $target) {
            $targets++;
            $isFile = in_array($file, ["autoload_classmap.php", "autoload_files.php"], true);
            if (!($isFile ? is_file((string) $target) : is_dir((string) $target))) {
                $problems[] = "autoloader points at a missing path: ".$target;
            }
        }
    }
}

$installed = json_decode(file_get_contents($pkg."/vendor/composer/installed.json"), true);
$names = array_column($installed["packages"] ?? [], "name");
foreach (["phpunit/phpunit", "laravel/pint", "myclabs/deep-copy", "nikic/php-parser"] as $dev) {
    if (in_array($dev, $names, true)) { $problems[] = "development package shipped: ".$dev; }
}

$header = file_get_contents($pkg."/domos-core.php");
if (!preg_match("/ \* Version: +([0-9.]+)/", $header, $m) || $m[1] !== $version) {
    $problems[] = "the archive does not contain version ".$version;
}

if ($problems) {
    fwrite(STDERR, "  package verification failed:\n    ".implode("\n    ", $problems)."\n");
    exit(1);
}

printf("  %d autoload targets resolve, %d runtime packages, version %s\n", $targets, count($names), $version);
' || die "The built archive did not pass verification."
ok "archive built and verified"

bold "5. Tag + pre-release"

if [ "$DRY_RUN" = true ]; then
	warn "dry run: stopping before git tag / git push / gh release"
	printf '\n\033[32mDry run complete\033[0m - %s is built and verified, nothing was published.\n' "$VERSION"
	printf 'Release it with: ./release.sh %s\n\n' "$VERSION"
	exit 0
fi

git tag "$VERSION"
git push origin main
git push origin "$VERSION"
python3 - "$VERSION" <<'PY' > /tmp/domos-release-notes.md
import re, sys, pathlib
version = sys.argv[1]
text = pathlib.Path('CHANGELOG.md').read_text()
m = re.search(r'^## ' + re.escape(version) + r'\s*$(.+?)(?=^## |\Z)', text, re.M | re.S)
sys.stdout.write(m.group(1).strip() + "\n")
PY
[ -s /tmp/domos-release-notes.md ] || die "Could not extract release notes from CHANGELOG.md."

# Pre-release first: /releases/latest excludes pre-releases, so the updater
# cannot offer this version while the archive is still being attached.
gh release create "$VERSION" -R "$REPO" \
	--title "$VERSION" \
	--notes-file /tmp/domos-release-notes.md \
	--prerelease \
	--verify-tag >/dev/null
ok "tag pushed, $VERSION exists as a pre-release"

LATEST="$(gh api "repos/$REPO/releases/latest" --jq '.tag_name')"
[ "$LATEST" != "$VERSION" ] || die "The pre-release is already visible as latest; the updater would pick it up."
ok "updater still sees $LATEST (not $VERSION), as intended"

bold "6. Upload assets"
gh release upload "$VERSION" "$ZIP" -R "$REPO" >/dev/null

ASSET_URL="https://github.com/$REPO/releases/download/$VERSION/domos-core.zip"
curl -sfL "$ASSET_URL" -o /tmp/domos-download-check.zip \
	|| die "The uploaded archive is not downloadable."
# shellcheck disable=SC2016
CHECK_VERSION="$(unzip -p /tmp/domos-download-check.zip domos-core/domos-core.php | grep -m1 ' \* Version:' | tr -dc '0-9.')"
[ "$CHECK_VERSION" = "$VERSION" ] || die "The uploaded archive contains $CHECK_VERSION, not $VERSION."
ok "asset uploaded, downloads, and contains $VERSION"

bold "7. Release"
gh release edit "$VERSION" -R "$REPO" --prerelease=false --latest >/dev/null

LATEST="$(gh api "repos/$REPO/releases/latest" --jq '.tag_name')"
[ "$LATEST" = "$VERSION" ] || die "releases/latest still reports $LATEST."
RESOLVED="$(gh api "repos/$REPO/releases/latest" --jq '[.assets[].name] | join(",")')"
[ "$RESOLVED" = "domos-core.zip" ] || die "releases/latest has unexpected assets: $RESOLVED"

# The updater prefers a release asset over the auto-generated source archive.
# Without the asset it silently falls back to the zipball, which ships the test
# suite and the development tooling - so this is the check that matters.
ok "releases/latest is $VERSION with the archive attached"
ok "updater will download: https://github.com/$REPO/releases/download/$VERSION/domos-core.zip"

printf '\n\033[32mReleased %s\033[0m\n' "$VERSION"
printf '  https://github.com/%s/releases/tag/%s\n\n' "$REPO" "$VERSION"
