<?php
/**
 * Plugin Name:     domos für WordPress
 * Plugin URI:      https://domos.de
 * Description:     Die domos WordPress-Integration
 * Version:         2.0.0
 * Author:          domos GmbH
 * Author URI:      https://domos.de
 * Text Domain:     domos
 * Domain Path:     /resources/lang
 */
require_once __DIR__.'/vendor/autoload.php';

define('DOMOS_CORE_ROOT', __DIR__);
define('DOMOS_CORE_ROOT_FILE', __FILE__);
define('DOMOS_CORE_URL', plugin_dir_url(__FILE__));
define('DOMOS_CORE_VERSION', '2.0.0');
define('DOMOS_CORE_SLUG', 'domos-core');

$plugin = new Domos\Core\Providers\DomosCoreServiceProvider;
$plugin->register();

add_action('init', [$plugin, 'boot']);

$app = new \Roots\Acorn\Application(__DIR__);
$app->useNamespace('Domos\\Core\\');

$bootloader = new \Roots\Acorn\Bootloader($app);
$bootloader->boot();

$app['view']->addNamespace('adler', __DIR__.'/resources/views/frontend/adler');
