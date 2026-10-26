<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Sync;

use Domos\Core\EstatePost;
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

	/**
	 * The languages option is read back as whatever is stored in the database, not
	 * as what we wrote. A value from an older plugin version, a wp-cli edit or a
	 * stray string used to be iterated directly: PHP raised a warning and the loop
	 * silently produced no translations. On a site with a strict error handler
	 * (which is what Acorn installs when WP_DEBUG is on) that warning becomes a
	 * thrown exception and the whole sync aborts with a generic error.
	 *
	 * The strict phpunit.xml settings fail this test on the warning alone.
	 */
	public function test_start_tolerates_a_languages_option_that_is_not_an_array(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);
		WpState::instance()->options['immocore_languages'] = 'en';

		$result = (new SyncManager($queue))->start();

		self::assertSame([null, 'en'], $result['run']['languages'], 'A single stored language code is still honoured.');
		self::assertCount(2, $queue->queued);
	}

	public function test_start_ignores_unusable_entries_in_the_languages_option(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);
		WpState::instance()->options['immocore_languages'] = ['en', '', 'deutschland', ['nested'], 42, null];

		$result = (new SyncManager($queue))->start();

		self::assertSame([null, 'en'], $result['run']['languages']);
	}

	/**
	 * The dispatch schedules a cron healthcheck, and WordPress runs every
	 * third-party cron_schedules callback while doing that. A callback with a
	 * wrong signature aborts the sync; without the probe the admin sees only
	 * "Ein unbekannter Fehler ist aufgetreten." and nobody can tell why. Verified
	 * against a real site before it was handled here.
	 */
	public function test_start_names_a_broken_cron_schedules_callback_from_another_plugin(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);

		// A plugin registering a two-argument callback while core passes one.
		add_filter('cron_schedules', function ($schedules, $unexpectedSecondArgument) {
			return $schedules;
		}, 10, 2);

		try {
			(new SyncManager($queue))->start();
			self::fail('A broken cron_schedules callback must be reported, not swallowed.');
		} catch (CouldNotSync $e) {
			self::assertSame('broken_cron_schedules', $e->errorCode);
			self::assertStringContainsString('Cron-Zeitpläne', $e->getMessage());
			self::assertStringContainsString('Too few arguments', $e->getMessage(), 'The original error survives so the plugin can be identified.');
		}

		self::assertSame([], $queue->queued, 'Nothing may be queued when the schedules are broken.');
		self::assertNull(SyncRunStatus::get());
	}

	/**
	 * A filter returning something other than an array is the second shape of the
	 * same bug: WordPress' own wp_get_schedules() merges the filtered value with
	 * array_merge(), so it throws a TypeError inside core. Reported the same way.
	 */
	public function test_start_reports_a_cron_schedules_filter_that_returns_nonsense(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1']);
		add_filter('cron_schedules', fn () => 'not an array');

		try {
			(new SyncManager($queue))->start();
			self::fail('A non-array schedule list must be reported.');
		} catch (CouldNotSync $e) {
			self::assertSame('broken_cron_schedules', $e->errorCode);
			self::assertStringContainsString('must be of type array', $e->getMessage());
		}
	}

	/**
	 * A throw during the dispatch means the worker request was never sent, so the
	 * queue must not survive: it would make every later attempt answer "already
	 * running" - a progress bar stuck at zero - instead of the actual error.
	 */
	public function test_start_clears_queued_work_when_the_dispatch_throws(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos(['estate-1', 'estate-2']);
		$queue->dispatchThrows = new \Error('scheduling blew up');

		try {
			(new SyncManager($queue))->start();
			self::fail('The throw must propagate.');
		} catch (\Error $e) {
			self::assertSame('scheduling blew up', $e->getMessage());
		}

		self::assertTrue($queue->deleteAllCalled);
		self::assertSame([], $queue->queued);
		self::assertNull(SyncRunStatus::get(), 'A failed dispatch must not leave a running run behind.');
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

	/**
	 * An empty work list must not end in a run that nobody can finish, and it must
	 * not be treated as "every estate disappeared".
	 */
	public function test_start_finishes_the_run_without_queueing_or_trashing_when_there_are_no_estates(): void
	{
		$queue = new FakeSyncQueue;
		Fixtures::bootDomos([]);

		$published = WpState::instance()->addPost(
			['post_type' => EstatePost::POST_TYPE, 'post_status' => 'publish'],
			['domos_id' => 'estate-no-longer-listed'],
		);

		$result = (new SyncManager($queue))->start();

		self::assertFalse($result['already_running']);
		self::assertSame('finished', $result['run']['status']);
		self::assertSame('no_estates', $result['run']['skipped']);
		self::assertSame(0, $result['run']['total']);
		self::assertSame('finished', SyncRunStatus::get()['status']);

		// An empty queue is never written and never processed: the worker returns
		// before complete(), so dispatching would leave the run at "0/0" forever.
		self::assertSame([], $queue->queued);
		self::assertFalse($queue->saved);

		// Reconcile would trash this post - which is why it must not run on an
		// empty list.
		self::assertSame([$published], array_map(fn (EstatePost $post) => (int) $post->id, EstatePost::findUnneeded([])));
		self::assertSame([], WpState::instance()->trashedPosts);
	}
}
