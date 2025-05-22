<?php
/**
 * Plugin Name:     immocore for WordPress
 * Plugin URI:      https://immocore.com
 * Description:     The immocore integration for WordPress
 * Version:         3.0.0
 * Author:          immocore GmbH
 * Author URI:      https://immocore.com
 * Text Domain:     domos
 * Domain Path:     /resources/lang
 */
require_once __DIR__.'/vendor/autoload.php';

define('DOMOS_CORE_ROOT', __DIR__);
define('DOMOS_CORE_ROOT_FILE', __FILE__);
define('DOMOS_CORE_URL', plugin_dir_url(__FILE__));
define('DOMOS_CORE_VERSION', '2.0.3');
define('DOMOS_CORE_SLUG', 'domos-core');

//$plugin = new Domos\Core\Providers\DomosCoreServiceProvider;
//$plugin->register();
//
//add_action('init', [$plugin, 'boot']);

//$app = new \Roots\Acorn\Application(__DIR__);
//$app->useNamespace('Domos\\Core\\');
//
//$bootloader = new \Roots\Acorn\Bootloader($app);
//$bootloader->boot();

//$app['view']->addNamespace('adler', __DIR__.'/resources/views/frontend/adler');
