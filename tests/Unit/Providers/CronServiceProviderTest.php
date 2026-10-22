<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Providers;

use Domos\Core\Providers\CronServiceProvider;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;

/**
 * The nightly sync is the only thing that keeps a site up to date without a
 * human clicking a button, so both the schedule and the one-time migration away
 * from the old per-language hooks need to stay correct.
 */
final class CronServiceProviderTest extends TestCase
{
	protected function setUp(): void
	{
		WpState::reset();
		Fixtures::bootDomos();
	}

	public function test_boot_schedules_one_daily_hook(): void
	{
		(new CronServiceProvider)->boot();

		self::assertSame(['domos_cron_hook'], WpState::instance()->scheduledEvents);
	}

	public function test_boot_does_not_schedule_a_second_hook_when_one_exists(): void
	{
		$provider = new CronServiceProvider;
		$provider->boot();
		$provider->boot();

		self::assertSame(['domos_cron_hook'], WpState::instance()->scheduledEvents);
	}

	public function test_boot_removes_the_legacy_per_language_hooks_once(): void
	{
		$state = WpState::instance();
		$state->scheduledEvents = ['domos_cron_hook', 'domos_cron_hook_en', 'domos_cron_hook_fr'];
		$state->options['immocore_languages'] = ['en', 'fr'];

		(new CronServiceProvider)->boot();

		self::assertSame(['domos_cron_hook'], $state->scheduledEvents);
		self::assertSame('2', $state->options['domos_cron_version']);
	}

	public function test_boot_keeps_the_new_schedule_when_the_migration_already_ran(): void
	{
		$state = WpState::instance();
		$state->options['domos_cron_version'] = '2';
		// A stray legacy hook that shows up after the migration (for example a
		// language added by an older plugin version) must not be cleaned up
		// again on every request.
		$state->scheduledEvents = ['domos_cron_hook', 'domos_cron_hook_en'];

		(new CronServiceProvider)->boot();

		self::assertSame(['domos_cron_hook', 'domos_cron_hook_en'], $state->scheduledEvents);
	}
}
