<?php
/**
 * Plugin Name:     immocore for WordPress
 * Plugin URI:      https://immocore.com
 * Description:     The immocore integration for WordPress
 * Version:         3.5.2
 * Author:          immocore GmbH
 * Author URI:      https://immocore.com
 * Text Domain:     domos
 * Domain Path:     /resources/lang
 */
require_once __DIR__.'/vendor/autoload.php';

/*
 * Scoped copy of the background processing library (`composer strauss`, see
 * extra.strauss in composer.json). Its classes carry the DOMOS_ prefix instead
 * of the library's global WP_Background_Process, so another plugin bundling the
 * same library can no longer win the class name while we extend it.
 */
require_once __DIR__.'/src/Dependencies/autoload.php';

define('DOMOS_CORE_ROOT', __DIR__);
define('DOMOS_CORE_ROOT_FILE', __FILE__);
define('DOMOS_CORE_URL', plugin_dir_url(__FILE__));
define('DOMOS_CORE_VERSION', '3.5.2');
define('DOMOS_CORE_SLUG', 'domos-core');

$plugin = new Domos\Core\Providers\DomosCoreServiceProvider;
$plugin->register();

add_action('init', [$plugin, 'boot']);
