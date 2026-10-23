<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Providers;

use Domos\Core\DOMOS;
use Domos\Core\Exceptions\CouldNotSync;
use Domos\Core\Providers\CronServiceProvider;
use Domos\Core\Sync\SyncManager;
use Domos\Core\Sync\SyncQueue;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;

/**
 * The nightly sync runs from WP-Cron with nobody watching. If a failure escaped
 * exec() it would be an uncaught exception inside cron, which on some hosts
 * takes down the request that triggered cron - very likely the visitor who
 * happened to load a page.
 */
final class CronExecTest extends TestCase
{

	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://example.test';

		Fixtures::bootDomos();

	}


	/** Replace the sync manager with one whose start() always throws. */
	private function makeStartFail(\Throwable $error): void
	{
		$domos = DOMOS::instance();

		$domos->sync = new class($error, new SyncQueue) extends SyncManager {
			public function __construct(private \Throwable $error, SyncQueue $queue)
			{
				parent::__construct($queue);
			}

			public function start(): array
			{
				throw $this->error;
			}
		};
	}

	public function test_a_failing_sync_does_not_escape_the_cron_handler(): void
	{
		$this->makeStartFail(new CouldNotSync('Der Hintergrund-Sync konnte nicht gestartet werden.', 'cannot_dispatch'));

		// No expectation of an exception: the handler has to swallow it.
		(new CronServiceProvider)->exec();

		self::assertTrue(true, 'exec() returned instead of letting the failure reach WP-Cron.');
	}

	public function test_an_unexpected_failure_is_swallowed_too(): void
	{
		$this->makeStartFail(new \TypeError('something internal went wrong'));

		(new CronServiceProvider)->exec();

		self::assertTrue(true);
	}

	public function test_a_working_sync_is_left_alone(): void
	{
		$queue = Fixtures::useFakeQueue();
		$queue->active = true; // start() returns already_running without touching the API

		(new CronServiceProvider)->exec();

		self::assertTrue($queue->active);
	}
}
