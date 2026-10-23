<?php

declare(strict_types=1);

/**
 * PHPUnit entry point for the plugin's unit tests.
 *
 * These tests run the plugin's own logic against the WordPress doubles in
 * tests/Support - no WordPress install, no database, no network. Anything that
 * depends on real WordPress behaviour (post writes, WP_Query, the background
 * worker loopback) is covered by the live checks described in README.md
 * instead of being faked here.
 */

if (! defined('ABSPATH') && PHP_SAPI !== 'cli') {
	exit(1);
}

require __DIR__.'/../vendor/autoload.php';

/*
 * domos-core.php defines these, but it needs WordPress. Read the version out of
 * it rather than hardcoding a second copy, and fail loudly if the file stops
 * defining it in a way we can parse.
 */
$pluginSource = (string) file_get_contents(__DIR__.'/../domos-core.php');

if (! preg_match("/define\\('DOMOS_CORE_VERSION',\s*'([^']+)'\\)/", $pluginSource, $version)) {
	throw new RuntimeException('Could not read DOMOS_CORE_VERSION from domos-core.php');
}

define('DOMOS_CORE_VERSION', $version[1]);
define('DOMOS_CORE_ROOT', dirname(__DIR__));
define('DOMOS_CORE_ROOT_FILE', dirname(__DIR__).'/domos-core.php');
define('DOMOS_CORE_URL', 'https://example.test/wp-content/plugins/domos-core/');
define('DOMOS_CORE_SLUG', 'domos-core');

/*
 * WordPress defines this in wp-includes/default-constants.php, and the plugin
 * reads it to decide whether an error message may contain internals. The tests
 * run with the production value, so the "does not leak" assertions mean
 * something.
 */
define('WP_DEBUG', false);

/*
 * The plugin logs expected conditions through error_log() (a failing import, a
 * post whose payload is missing). Those are covered by assertions instead, so
 * send them to /dev/null rather than into the test output. PHP's own warnings
 * and notices still surface, because PHPUnit reads them from the error handler.
 */
ini_set('error_log', '/dev/null');

require __DIR__.'/Support/wp-functions.php';
require __DIR__.'/Support/wp-classes.php';
