<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Sync;

use Domos\Core\EstatePost;
use Domos\Core\Exceptions\CouldNotSync;
use Domos\Core\Sync\SyncRunStatus;
use Domos\Core\Tests\Support\FakeDomosClient;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\TestableSyncQueue;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;

/**
 * Retry and failure handling for the background worker. These paths matter
 * because the sync has no human watching it: a swallowed failure means an
 * estate silently keeps stale data, and a swallowed "already handled" means the
 * queue retries forever.
 */
final class SyncQueueTest extends TestCase
{
	private TestableSyncQueue $queue;

	private FakeDomosClient $client;


	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://example.test';
		WpState::instance()->options['immocore_default_language'] = 'de';

		$this->client = Fixtures::bootDomos();
		$this->queue = new TestableSyncQueue;

	}


	public function test_a_successful_item_is_removed_from_the_queue(): void
	{
		SyncRunStatus::start(['estate-1'], [null]);

		$result = $this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 1]);

		self::assertFalse($result, 'Returning false is what removes the item from the queue.');
		self::assertSame(1, $this->counterOf('created'));
		self::assertCount(1, WpState::instance()->insertedPosts);
	}

	public function test_a_failing_item_is_retried_before_it_is_given_up_on(): void
	{
		SyncRunStatus::start(['estate-1'], [null]);
		$this->client->estateResult = new CouldNotSync('Connection reset');

		$retried = $this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 1]);

		self::assertSame(
			['estate_id' => 'estate-1', 'language' => null, 'attempt' => 2],
			$retried,
			'A transient failure must come back with a bumped attempt counter.'
		);
		self::assertSame([], SyncRunStatus::get()['failures'], 'Nothing is recorded as failed while retries remain.');
	}

	public function test_an_item_is_recorded_as_failed_after_the_last_attempt(): void
	{
		SyncRunStatus::start(['estate-1'], [null]);
		$this->client->estateResult = new CouldNotSync('Connection reset');

		$result = $this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 3]);

		self::assertFalse($result, 'The third attempt must drop the item instead of retrying forever.');
		self::assertSame(
			['estate-1:default' => 'Connection reset'],
			SyncRunStatus::get()['failures'],
			'The failure is recorded against the item so the admin screen can report it.'
		);
	}

	public function test_a_failed_translation_is_recorded_against_its_language(): void
	{
		SyncRunStatus::start(['estate-1'], [null, 'en']);
		$this->client->estateResult = new CouldNotSync('boom');

		$this->queue->runTask(['estate_id' => 'estate-1', 'language' => 'en', 'attempt' => 3]);

		self::assertArrayHasKey('estate-1:en', SyncRunStatus::get()['failures']);
	}

	public function test_a_failure_still_counts_as_processed(): void
	{
		SyncRunStatus::start(['estate-1', 'estate-2'], [null]);
		$this->client->estateResult = new CouldNotSync('boom');

		$this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 3]);

		// Otherwise the progress bar would never reach the total and the run
		// would look like it hung.
		self::assertSame(1, SyncRunStatus::get()['processed']);
	}

	public function test_an_item_without_an_estate_id_is_dropped_without_a_request(): void
	{
		SyncRunStatus::start(['estate-1'], [null]);

		foreach ([['language' => null], ['estate_id' => '', 'language' => null], ['estate_id' => 42, 'language' => null]] as $item) {
			self::assertFalse($this->queue->runTask($item));
		}

		self::assertSame([], $this->client->estateCalls, 'A malformed queue item must not reach the API.');
	}

	public function test_an_estate_that_disappeared_upstream_counts_as_processed(): void
	{
		SyncRunStatus::start(['estate-1'], [null]);
		$this->client->estateMissing = true;

		$result = $this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 1]);

		// reconcile() is what trashes the post; the item itself is not a failure.
		self::assertFalse($result);
		self::assertSame(1, $this->counterOf('processed'));
		self::assertSame([], SyncRunStatus::get()['failures']);
		self::assertSame([], WpState::instance()->insertedPosts);
	}

	public function test_a_translation_is_stored_next_to_the_default_language(): void
	{
		SyncRunStatus::start(['estate-1'], [null, 'en']);

		$this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 1]);
		$this->queue->runTask(['estate_id' => 'estate-1', 'language' => 'en', 'attempt' => 1]);

		$postId = (int) array_key_first(WpState::instance()->posts);

		self::assertArrayHasKey('estate_data', WpState::instance()->postMeta[$postId]);
		self::assertArrayHasKey('estate_data_en', WpState::instance()->postMeta[$postId]);
		self::assertSame(2, $this->counterOf('processed'));
	}

	public function test_a_trashed_estate_is_reactivated_instead_of_duplicated(): void
	{
		$postId = WpState::instance()->addPost(
			['post_type' => EstatePost::POST_TYPE, 'post_status' => 'trash', 'post_title' => 'Old title'],
			['domos_id' => 'estate-1'],
		);

		SyncRunStatus::start(['estate-1'], [null]);
		$this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 1]);

		self::assertSame([$postId], WpState::instance()->untrashedPosts, 'The trashed post is reused to keep its permalink.');
		self::assertSame('publish', WpState::instance()->post($postId)['post_status']);
		self::assertCount(1, WpState::instance()->posts, 'No duplicate post may be created.');
	}

	/**
	 * Reuse is on by default, and it is what keeps a re-published estate on the
	 * same WordPress post (and therefore the same permalink). Disabling it via
	 * the documented filter is what creates the duplicate instead.
	 */
	public function test_reuse_can_be_disabled_through_the_documented_filter(): void
	{
		add_filter('domos_reuse_trashed_posts', fn () => false);

		$postId = WpState::instance()->addPost(
			['post_type' => EstatePost::POST_TYPE, 'post_status' => 'trash', 'post_title' => 'Old title'],
			['domos_id' => 'estate-1'],
		);

		SyncRunStatus::start(['estate-1'], [null]);
		$this->queue->runTask(['estate_id' => 'estate-1', 'language' => null, 'attempt' => 1]);

		self::assertSame([], WpState::instance()->untrashedPosts);
		self::assertSame('trash', WpState::instance()->post($postId)['post_status']);
		self::assertCount(2, WpState::instance()->posts, 'Without reuse the sync creates a second post.');
	}

	public function test_the_import_reads_the_payload_for_the_item_language(): void
	{
		SyncRunStatus::start(['estate-1'], [null, 'en']);

		$this->queue->runImport('estate-1', null);
		$this->queue->runImport('estate-1', 'en');

		self::assertSame(['estate-1:default', 'estate-1:en'], $this->client->estateCalls);
	}

	private function counterOf(string $key): int
	{
		$run = SyncRunStatus::get();

		self::assertIsArray($run);

		return $run[$key];
	}
}
