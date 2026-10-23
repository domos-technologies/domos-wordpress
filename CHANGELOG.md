# Changelog

## 3.5.2
- security: upgrades the bundled dependencies, which clears 22 known advisories (5 high, 16 medium, 1 low) in `symfony/http-foundation`, `symfony/mime`, `symfony/routing`, `guzzlehttp/guzzle`, `guzzlehttp/psr7`, `league/flysystem` and `symfony/polyfill-intl-idn`. The plugin ships its dependencies, so those versions were installed on every site. Symfony moves to 7.4 and Guzzle to 7.16; PHP 8.2 remains supported.
- chore: removes the unused `dist/` directory, which was build output from the old Bud toolchain (including its hot-reload client chunks) and was only referenced by commented-out code.

## 3.5.1
- security: the public inquiry endpoint no longer discloses internals. A debug `var_dump()` was left in the error path, so an unauthenticated request against a site whose immocore URL is not configured yet received the whole exception object - server file paths, the plugin's class and method names and a full stack trace (229 KB of it).
- fix: a malformed inquiry body is answered with a 400 instead of a fatal error. The JSON body went to a typed argument without validation, and a JSON array or scalar produced an uncaught `TypeError` with the error message in the response.
- fix: the admin screen embeds the API URL, token, languages and default language with `@js()`, so a quote in one of them can no longer break out of the page's JavaScript. The HTML escaping was undone by the attribute parser, which left the settings screen unusable for such a site.

## 3.5.0
- change: rewritten estate sync engine. Sync now runs as a chunked background queue (one request per estate) with live progress, instead of one giant blocking request per language.
- added: `GET /wp-json/domos/admin/sync-status` endpoint to poll the current sync run.
- change: a single daily cron hook (`domos_cron_hook`) now syncs all languages instead of one hook per language.
- fix: the settings and sync requests no longer fail on sites without pretty permalinks. The admin screen resolves the REST endpoints through `rest_url()` instead of a hardcoded `/wp-json/...` path, which such a site serves as its homepage, so the responses were HTML and the screen failed with a JSON parse error.
- fix: the default language is written again. Post title, description and excerpt, and the city/usage filter data, stopped being updated after the first sync, because the sync passes `null` for the default language while `EstatePost::update()` compared against the configured language code.
- fix: a sync that cannot be started now reports the reason in the admin screen instead of showing progress that never moves. The background worker is reached through a request to the site itself; when that request does not arrive (expired TLS certificate, blocked localhost traffic, ...) the failure was invisible to the queue dispatcher.
- fix: estate URLs (`/objekte/...`) no longer fall back to the home page or a 404 on sites that never re-saved their permalink settings. The `objekte` rules were never written to the stored rewrite rules, because WordPress does not regenerate them when a plugin is activated and the plugin never asked for it. They are now flushed once per plugin version, so an update also repairs sites where they are missing.
- added: unit tests (`composer test`) for the plugin options, the sync run state, the sync work list, `EstatePost` language handling, multi-language imports, the sync API routes, the release metadata and the release build filter.
- fix: `EstatePost::hasLanguage()` accepts a post ID as WordPress provides it (`$post->ID`, an integer) instead of only a string, matching the documented usage.
- chore: `bun run package` now produces a distribution archive without the test suite, development dependencies or repository metadata (`.distignore` plus `composer install --no-dev`). Attach the generated `domos-core.zip` to the release so installs get it instead of the source archive; see the Development section in the README.

## 3.4.1
- added: sync now reuses trashed posts automatically. This prevents duplicate estates when a post is trashed and re-synced while keeps the Post-IDs the same.
- added: `domos_reuse_trashed_posts` filter to allow disabling this feature

## 3.4.0
- change: sync now uses `wp_trash_post` instead of `wp_delete_post`, allowing estates (and any internal links) to be restored in case of bugs or errors

## 3.3.1
- chore: update version of domos/schema to 1.3.0
- added: usage label for main estate usage when using PHP SDK

## 3.3.0
- added: multi-language support. Configure other languages in the plugin settings.
- added: optional `language` parameter to multiple `EstatePost` methods
- added: `EstatePost::hasLanguage($post_id, $language)` to check if data for a specific language is available
- fix: remove old domos logo

## 3.2.1
- Updated version of domos/schema to 1.2.2
- Make EstatePost::find fail gracefully to avoid sync-lock

## 3.2.0
- Updated version of domos/schema to 1.2

## 3.1.0
- Updated version of domos/schema to 1.0

## 3.0.0
- Removed image synchronization
- Improved estate synchronization speed
- Added API key support (which will become mandatory in future versions)

## 2.1.0
- Added support for dark mode brandings
- Added custom font support
- Privacy policy URL is now configurable
- Recommended Estates now correctly redirect to the WordPress pages, instead of domos-hosted pages

## 2.0.3
- Fixes script conflict with Elementor
- Loading admin scripts (Alpine) from plugin files instead of CDN

## 2.0.2
- Fixed Admin UI not saving properly in some WP configurations due to nonce issues

## 2.0.1
- Fixed sliders not rendering properly

## 2.0.0
- Changed block rendering from included block templates, to domos-server driven HTML rendering.
- Improved estate synchronization
