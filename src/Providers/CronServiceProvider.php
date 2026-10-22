<?php

namespace Domos\Core\Providers;

use Domos\Core\DOMOS;

class CronServiceProvider implements Provider
{
	/**
	 * Bumped when the cron layout changes; triggers a one-time reschedule.
	 */
	protected const CRON_VERSION = '2';

	protected const CRON_VERSION_OPTION = 'domos_cron_version';

	public function register()
	{
		add_action('domos_cron_hook', [$this, 'exec']);
	}

	public function boot()
	{
		$this->migrateLegacyCron();

		if (!wp_next_scheduled('domos_cron_hook')) {
			wp_schedule_event(strtotime('today 23:50'), 'daily', 'domos_cron_hook');
		}
	}

	public function exec()
	{
		try {
			DOMOS::instance()->sync->start();
		} catch (\Throwable $th) {
			error_log(print_r([
				'message' => $th->getMessage(),
				'file' => $th->getFile(),
				'line' => $th->getLine(),
				'trace' => $th->getTrace(),
			], true));
		}
	}

	/**
	 * v1 scheduled domos_cron_hook with ['language' => null] args plus one
	 * domos_cron_hook_{lang} event per language. Clear all of them once so
	 * the new arg-less event can be scheduled cleanly.
	 */
	protected function migrateLegacyCron(): void
	{
		if (get_option(self::CRON_VERSION_OPTION) === self::CRON_VERSION) {
			return;
		}

		wp_unschedule_hook('domos_cron_hook');

		foreach (DOMOS::instance()->options->languages->get() ?? [] as $language) {
			wp_unschedule_hook("domos_cron_hook_{$language}");
		}

		update_option(self::CRON_VERSION_OPTION, self::CRON_VERSION, true);
	}
}