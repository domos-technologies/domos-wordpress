<?php

namespace Domos\Core\Providers;

use Domos\Core\DOMOS;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class CronServiceProvider implements Provider
{
	public function register()
	{
		add_action('domos_cron_hook', [$this, 'exec']);
	}

	public function boot()
	{
		if (!wp_next_scheduled( 'domos_cron_hook' ) ) {
		    wp_schedule_event(time(), 'daily', 'domos_cron_hook');
		}
	}

	public function exec()
	{
		try {
			DOMOS::instance()->sync->synchronize();
		} catch (\Exception $e) {
			error_log($e->getMessage());
		}
	}
}
