<?php

namespace Domos\Core\Providers;

use Domos\Core\DOMOS;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class CronServiceProvider implements Provider
{
	public function register()
	{
		$languages = DOMOS::instance()->options->languages->get();

		// Sync default language
		add_action('domos_cron_hook', [$this, 'exec']);

		foreach ($languages as $language) {
			add_action("domos_cron_hook_{$language}", [$this, 'exec'], 10, 1);
		}
	}

	public function boot()
	{
		// Run "tonight" at 23:59:59
		$time = strtotime('today 23:50');

		$languages = DOMOS::instance()->options->languages->get();

		if (!wp_next_scheduled('domos_cron_hook')) {
		    wp_schedule_event($time, 'daily', 'domos_cron_hook', [
				'language' => null,
			]);
		}

		foreach ($languages as $language) {
			$hook = "domos_cron_hook_{$language}";

			// We add 2 minutes to the time of the default language.
			// We do this to offset each language by 2 minutes.
			$time = $time + (2 * 60);

			if (!wp_next_scheduled($hook)) {
				wp_schedule_event($time, 'daily', $hook, [
					'language' => $language,
				]);
			}
		}
	}

	public function exec(?string $language = null)
	{
		try {
			DOMOS::instance()->sync->synchronize(language: $language);
		} catch (\Throwable $th) {
			error_log(print_r([
				'message' => $th->getMessage(),
				'file' => $th->getFile(),
				'line' => $th->getLine(),
				'trace' => $th->getTrace(),
			], true));
		}
	}
}
