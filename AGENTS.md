# Working in this repository

This is the **immocore WordPress plugin** (`domos-core`). It is not the app: the
immocore application lives in the sibling repository (`../app`) and this plugin
only talks to its sync and inquiry API. Everything here ships to customer
WordPress sites, so changes are customer-facing by definition.

The repository is **public**. Anything committed is world-readable, and the tag
source archive is installable by anyone. Never commit a credential, a customer
name, or a real API token.

---

## 1. What you need installed

- **PHP 8.2+** - the locked dependencies require it (`roots/acorn` 5 declares
  `php >=8.2`, `illuminate/support` 12 declares `^8.2`). Composer will refuse
  anything lower, and CI runs 8.2, 8.3 and 8.4.
- **composer**, **bun**, **gh** (GitHub CLI, authenticated - the release script
  needs it).
- A WordPress to load the plugin into. See below.
- Optional: the app (`../app`) for sync testing against a real API. Herd serves
  it at `https://dev.immocore.test`.

There is **no `wp-cli`** on this machine, and MySQL is **DBngin**, not Homebrew,
so `mysql` is not on `PATH`:

```bash
/Users/Shared/DBngin/mysql/8.0.33/bin/mysql -h 127.0.0.1 -u root <db> -e "SELECT 1"
```

## 2. A WordPress to develop against

### Reuse the one that is already there

`~/dev/wordpress` is a WordPress install served by Herd at
**https://wordpress.test**, and its `wp-content/plugins/domos-core` is a
**symlink to this repository** - edits are live, no build step to copy files.

- Admin login: `Admin` / `dev`
- Database: MySQL database `wordpress`
- It is configured against a customer API (`https://anh-hausbesitz.immocore.app`)
  and has 39 estates imported.

Two more installs exist from earlier testing, if you want a clean or a
multi-language site:

| Site | Path | Database | Notes |
|---|---|---|---|
| https://wpfresh.test | `~/dev/wp-fresh` | `wpfresh` | clean install, plugin installed from the built archive |
| https://wpfresh2.test | `~/dev/wp-fresh2` | `wpfresh2` | points at `https://dev.immocore.test`, 307 estates in `de` + `en` |

Their admin logins are `admin` / `dev` and `admin` / `dev-dev-dev1`. These are
throwaway local installs.

### If the browser refuses to load the site

Herd's certificates expire, and the clock on this machine is well past the date
the installed certificates were issued for. Symptom: `ERR_CERT_DATE_INVALID`,
or PHP cURL failing with `certificate has expired`, which breaks the sync
because the plugin calls the site itself. Re-issue it:

```bash
herd secure wordpress      # or dev.immocore, wpfresh, ...
```

### Make a fresh one

```bash
curl -sL -o /tmp/wp.zip https://wordpress.org/latest.zip
unzip -q /tmp/wp.zip -d /tmp/wpx && mv /tmp/wpx/wordpress ~/dev/wp-fresh3
# wp-config.php: DB_NAME=wpfresh3, DB_USER=root, DB_PASSWORD='', DB_HOST=127.0.0.1
# plus salts from https://api.wordpress.org/secret-key/1.1/salt/
mysql -h 127.0.0.1 -u root -e "CREATE DATABASE wpfresh3 CHARACTER SET utf8mb4"
ln -sfn ~/dev/wp-fresh3 "~/Library/Application Support/Herd/config/valet/Sites/wpfresh3"
```

Then finish the installer in a browser (`http://wpfresh3.test/wp-admin/install.php`).
Note that the installer's JavaScript can swallow a form submit from a scripted
browser; posting `install.php?step=2` with `weblog_title`, `user_name`,
`admin_password`, `admin_password2`, `pw_weak=1` and `admin_email` is reliable.

To load the plugin, upload the built archive (see section 5) through
**Plugins -> Add New -> Upload Plugin**. That is the path a customer takes, and
it is worth using when you care about the archive being installable.

## 3. Tests

```bash
composer test        # or: vendor/bin/phpunit, bun run test
```

Fast (~50ms), no WordPress, no database, no network. `tests/Unit` runs the
plugin's own logic against the WordPress doubles in `tests/Support`.

- The doubles cover the WordPress functions and classes the plugin touches.
  Extend them there rather than reaching for a mocking library.
- `WP_Query` in the doubles **throws** on query arguments it does not implement,
  on purpose: a double that quietly matches nothing is how you get green tests
  over broken code.
- `WpState` is the in-memory WordPress; call `WpState::reset()` in `setUp()`.
- `Fixtures::bootDomos()` installs a fresh `DOMOS` singleton per test, which
  matters because the real one caches option values for the process.

### Check that your test can actually fail

When you add a test for a fix, revert the fix and watch the test fail. This has
already caught two bad assertions in this repository:

- a multi-language fallback test asserted on the estate name, which the degraded
  code path also fills in from the post title, so it passed with the fallback
  deleted
- a "the estate URL works" check matched the page title as a substring, which
  broke on WordPress' texturizing (`-` becomes `–`, `&` becomes `&#038;`) and
  reported 14 healthy pages as broken

An assertion that passes either way is worse than no test, because it buys
confidence it has not earned.

### What the unit suite cannot cover

These need a live WordPress, and every one of them has hidden a real defect:

1. **Fresh install.** Install the built archive on a clean WordPress, activate,
   configure, sync, then load an estate URL. WordPress does not regenerate
   rewrite rules on activation or update, so the `objekte` permastruct was
   missing and every estate URL served a 404 - or worse, the front page with a
   200. `PostServiceProvider` flushes once per plugin version; if you touch
   rewrite rules, re-check this.
2. **The admin screen.** Load it, save the settings, run a sync. Watch the
   browser console. It has already failed here twice: with plain permalinks the
   hardcoded `/wp-json/` path served the homepage and the JSON parse error was
   the only symptom, and the button below the fold makes scripted clicks do
   nothing (scroll it into view first, or check `window.__fetch`-style counters
   rather than assuming the click landed).
3. **The sync itself.** A sync that cannot start must fail loudly. The worker is
   reached by a request the site makes to itself, and WordPress' HTTP layer
   discards transport errors for non-blocking requests, so a dead loopback used
   to leave the admin screen at `0/N` forever. `SyncManager::assertWorkerIsReachable()`
   exists for exactly that.
4. **Multi-language.** Configure a second language, run a sync, then check the
   per-language meta (`estate_data` for the default language, `estate_data_<lang>`
   otherwise) and that the default-language post fields did not get overwritten.
   `https://dev.immocore.test` is the local app; the estate "Am Exerzierplatz 9,
   68167 Mannheim" already has an English name and slogan, and its translations
   can be edited in the app under *Objekt bearbeiten -> Vermarktung* (the
   language picker switches which language the fields write to).

## 4. Editing code

Layout: `src/Providers` are registered by `DomosCoreServiceProvider`, each with
`register()` (hooks) and `boot()` (runs on `init`). `src/Sync` is the sync engine
(`SyncManager` builds the work list, `SyncQueue` is the background worker,
`SyncRunStatus` is the run record the admin screen polls, `DomosClient` is the
API client). `src/EstatePost.php` is the WordPress bridge - the only place that
writes estate posts. `resources/views` holds the Blade templates, including the
front-end exposé which renders inside a shadow DOM.

Conventions worth knowing before you touch things:

- **`null` means "the default language"** throughout `EstatePost`, and
  `getEstateDataMetaKey()` reads it that way. The sync passes `null` for the
  default language; anything comparing a language against the configured default
  has to normalise first, or the comparison silently never matches.
- **Only the default language owns the WordPress post fields.** `post_title`,
  `post_content`, `post_excerpt` and the searchable meta (`estate_city`,
  `estate_usages`, ...) are written for the default language only; a translated
  payload goes to its own meta key and must not touch them.
- **Option names are inconsistent on purpose** - `domos_url`, `domos_token`,
  `domos_cities`, `domos_usages` are historical, `immocore_languages`,
  `immocore_default_language` and `immocore_rewrite_version` are current.
  `OptionTest` pins them, because renaming one orphans every customer's stored
  settings.
- **`vendor/` is committed.** It is a distribution channel (see section 5), so a
  dependency added only to `composer.json` is not actually present for anyone
  installing from a tag. After changing dependencies, commit the vendor changes
  too - and expect `composer install` to rewrite `vendor/composer/installed.php`
  with the current commit hash, which is normal, harmless, and must be committed
  before releasing (the release script refuses a dirty tree).
- **Do not run `composer lint` blindly.** Pint's default preset uses four
  spaces, while `.editorconfig` and most of `src/` use tabs, so it would
  reindent the repository. There is no lint job in CI for the same reason.
- **Anything that reaches the API is reachable without a token.** The sync
  endpoints are public on the immocore side, and the settings screen's `whoami`
  call is how the plugin learns the customer's default language.
- `WP_DEBUG` is **false** on the dev site, so PHP notices and anything the plugin
  writes through `error_log()` are invisible in the browser. When debugging the
  sync, read the plugin's own logging path (`ApiServiceProvider::logThrowable`)
  rather than assuming silence means success.

## 5. Building the distribution archive

```bash
bun run package      # rsync -> composer install --no-dev -> prune -> zip
```

`.distignore` decides what is left out: the test suite, the phpunit config,
build inputs, repository and editor metadata, and the working tree's `vendor/`.
That last one is the important one - `vendor/` is deliberately **not** copied,
because it contains phpunit and pint and its generated autoloader requires their
files on every request. The staging copy is built with `composer install
--no-dev`, which produces a runtime-only autoloader. Copying the working tree's
vendor and deleting the dev packages from it produces a fatal on every page load.

`tests/Unit/PackagingTest.php` fails if the filter list or the build step stops
keeping tests and dev tooling out, and CI runs the same build on every push.

## 6. Releasing

```bash
./release.sh 3.5.1              # or: bun run release 3.5.1
./release.sh 3.5.1 --dry-run    # build and verify, publish nothing
```

Before that, three places must agree: the `Version:` header and
`DOMOS_CORE_VERSION` in `domos-core.php`, and a `## <version>` section in
`CHANGELOG.md` that reads well on its own (it becomes the GitHub release notes).
`ReleaseMetadataTest` checks all three, and the script re-checks them.

The script then: runs the tests, rebuilds the assets (and refuses to continue if
that leaves uncommitted files - the built CSS and JS ship, nothing rebuilds them
on the customer's site), builds and verifies the archive, pushes `main`, tags,
creates the GitHub release **as a pre-release**, uploads the archive, downloads
it again to confirm the version inside, and finally flips it to a full release
and asserts that `releases/latest` carries the archive.

**Why the pre-release dance matters.** The plugin updates itself through
plugin-update-checker against this repository's releases, and GitHub's
`/releases/latest` excludes pre-releases. So while the release is a pre-release
the updater cannot see it - which is the point, because the updater falls back to
the **tag's source archive** when a release has no asset. The source archive is
the raw repository: it would ship `tests/`, `phpunit.xml`, `.idea/` and the
development dependencies to every customer. That is exactly what happened until
3.5.0; check the resolved URL if you are ever unsure:

```bash
gh api repos/domos-technologies/domos-wordpress/releases/latest \
  --jq '{tag: .tag_name, assets: [.assets[].name]}'
```

It must list `domos-core.zip`. If it does not, customers are installing the
source archive.

Other things to look out for:

- Pushing `main` publishes the commit messages. A security fix described in a
  commit message is public the moment it is pushed, so push and release
  together rather than leaving the hole documented and unpatched.
- The release is not reproducible from CI: the archive is built and uploaded
  from the machine running the script. CI only *verifies* the build.
- Nothing verifies that the plugin still works after an update on a customer
  site. To test that path locally, set an installed copy's `Version:` header
  back one release, clear the `external_updates-domos-core` option and the
  `_site_transient_update_plugins` transient, open
  `wp-admin/update-core.php`, and run the update - WordPress will fetch the
  release asset and replace the plugin in place.
- After a release, the version in the working tree is the released one. Bump it
  and add a changelog section when you start the next change, not at release
  time.

## 7. Housekeeping

- File deletion: prefer `trash` over `rm` so it stays recoverable.
- Do not commit `domos-core.zip`, `./plugin/`, or the phpunit cache; all three
  are ignored already.
- `.github/workflows/tests.yml` runs the suite on three PHP versions and the
  release build on every push to `main` and every pull request.
