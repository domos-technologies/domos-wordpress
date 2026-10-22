<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Sync;

use Domos\Core\Exceptions\CouldNotSync;
use Domos\Core\Sync\SyncManager;
use Domos\Core\Sync\SyncRunStatus;
use Domos\Core\Tests\Support\FakeSyncQueue;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;
use WP_Error;

/**
 * SyncManager builds the work list and hands it to the background queue. The
 * queue itself is reached over a loopback HTTP request that cannot report
 * transport failures, which is why start() probes first and throws instead of
 * leaving a run that never progresses.
 */
final class SyncManagerTest extends TestCase
{
	protected function setUp(): void
	{
		WpState::reset();
	}

	public function test_start_returns_already_running_without_queueing_when_the_queue_is_active(): void
	{
		$queue = new FakeSyncQueue;
		$queue->active = true;
		$client = Fixtures::bootDomos(['estate-1']);

		$result = (new SyncManager($queue))->start();

		self::assertTrue($result['already_running']);
		self::assertFalse($queue->saved);
		self::assertSame([], $queue->queued);
		// An in-flight run is reported as-is, without asking the API again.
		self::assertSame(0, $client->estateIdsCalls);
		self::assertNull(SyncRunStatus::get());
	}

	public function test_start_queues_one_item_per_estate_and_language_with_the_default_language_first(): void
	{
		$queue = new FakeSyncQueue;
		$client = Fixtures::bootDomos(['estate-1', 'estate-2']);
		WpState::instance()->options['immocore_languages'] = ['en', 'fr'];

		$result = (new SyncManager($queue))->start();

		self::assertFalse($result['already_running']);
		self::assertSame(6, $result['run']['total']);
		self::assertSame([
			['estate_id' => 'estate-1', 'language' => null, 'attempt' => 1],
			['estate_id' => 'estate-2', 'language' => null, 'attempt' => 1],
			['estate_id' => 'estate-1', 'language' => 'en', 'attempt' => 1],
			['estate_id' => 'estate-2', 'language' => 'en', 'attempt' => 1],
			['estate_id' => 'estate-1', 'language' => 'fr', 'attempt' => 1],
			['estate_id' => 'estate-2', 'language' => 'fr', 'attempt' => 1],
		], $queue->queued);
		self::assertSame([null, 'en', 'fr'], $result['run']['languages']);
		self::assertTrue($queue->saved);
		self::assertSame(2, count($client->estateIdsResponse));
	}

	public function test_start_skips_language_values_that_are_not_language_codes(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);
		// Anything a customer could have typed into the settings field:
		// nulls, empty rows and the two-letter pattern is all we accept.
		WpState::instance()->options['immocore_languages'] = ['en', '', 'deutschland', 'F'];

		$result = (new SyncManager($queue))->start();

		self::assertSame([null, 'en'], $result['run']['languages']);
		self::assertSame(2, count($queue->queued));
	}

	public function test_start_throws_and_queues_nothing_when_the_worker_cannot_be_reached(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);
		WpState::instance()->httpResponse = new WP_Error(
			'http_request_failed',
			'cURL error 60: SSL certificate OpenSSL verify result: certificate has expired (10)'
		);

		try {
			(new SyncManager($queue))->start();
			self::fail('start() should have thrown when the loopback is unreachable');
		} catch (CouldNotSync $e) {
			self::assertSame('cannot_reach_worker', $e->errorCode);
			self::assertStringContainsString('certificate has expired', $e->getMessage());
		}

		self::assertSame([], $queue->queued);
		self::assertFalse($queue->saved);
		// Nothing may be left behind for the admin UI to poll.
		self::assertNull(SyncRunStatus::get());
	}

	public function test_start_probes_the_worker_with_a_blocking_request_to_admin_ajax(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);

		(new SyncManager($queue))->start();

		self::assertCount(1, WpState::instance()->httpRequests);
		self::assertSame('https://example.test/wp-admin/admin-ajax.php', WpState::instance()->httpRequests[0]['url']);
		self::assertTrue(WpState::instance()->httpRequests[0]['args']['blocking']);
	}

	public function test_start_throws_and_clears_queued_work_when_the_dispatch_fails(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1', 'estate-2']);
		$queue->failDispatch('cURL error 60: SSL certificate has expired');

		try {
			(new SyncManager($queue))->start();
			self::fail('start() should have thrown when the dispatch failed');
		} catch (CouldNotSync $e) {
			self::assertSame('cannot_dispatch', $e->errorCode);
		}

		self::assertTrue($queue->deleteAllCalled);
		self::assertSame([], $queue->queued);
		self::assertNull(SyncRunStatus::get(), 'a failed dispatch must not leave a running run behind');
	}

	public function test_start_keeps_the_queued_work_and_the_run_on_success(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);
		$queue->dispatchResult = ['response' => ['code' => 200, 'message' => 'OK']];

		$result = (new SyncManager($queue))->start();

		self::assertFalse($queue->deleteAllCalled);
		self::assertCount(1, $queue->queued);
		self::assertSame('running', $result['run']['status']);
		self::assertSame('running', SyncRunStatus::get()['status']);
	}
}
