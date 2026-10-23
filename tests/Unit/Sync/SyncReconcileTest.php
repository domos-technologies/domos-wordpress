<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Sync;

use Domos\Core\DOMOS;
use Domos\Core\EstatePost;
use Domos\Core\Options\CitiesOption;
use Domos\Core\Options\UsagesOption;
use Domos\Core\Sync\SyncManager;
use Domos\Core\Sync\SyncRunStatus;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;

/**
 * reconcile() runs once the queue drains, and it is the only place that removes
 * content: it trashes the posts whose estates no longer exist upstream, then
 * rebuilds the filter options from what is left. Getting this wrong deletes
 * customer content, and getting the guard wrong means a stale run record could
 * trash a portfolio that was never synced.
 */
final class SyncReconcileTest extends TestCase
{
	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://example.test';
		WpState::instance()->options['immocore_default_language'] = 'de';

		Fixtures::bootDomos();
	}

	/** @param array<string, mixed> $meta */
	private function estatePost(string $domosId, string $city, array $usages, string $status = 'publish'): int
	{
		return WpState::instance()->addPost(
			['post_type' => EstatePost::POST_TYPE, 'post_status' => $status],
			array_merge([
				'domos_id' => $domosId,
				'estate_city' => $city,
				'estate_usages' => json_encode($usages),
			], []),
		);
	}

	private function manager(): SyncManager
	{
		return DOMOS::instance()->sync;
	}

	public function test_find_unneeded_finds_estates_missing_from_the_remote_list(): void
	{
		$this->estatePost('kept', 'Berlin', ['office']);
		$this->estatePost('gone', 'Hamburg', ['retail']);

		$unneeded = EstatePost::findUnneeded(['kept']);

		self::assertCount(1, $unneeded);
		self::assertSame('gone', $unneeded[0]->domosID);
	}

	/**
	 * A post whose meta never arrived (an interrupted import) has no external id,
	 * so it can never be matched against the remote list and would otherwise
	 * accumulate forever.
	 */
	public function test_find_unneeded_finds_posts_without_an_external_id(): void
	{
		$this->estatePost('kept', 'Berlin', ['office']);
		WpState::instance()->addPost(['post_type' => EstatePost::POST_TYPE, 'post_status' => 'publish']);

		$unneeded = EstatePost::findUnneeded(['kept']);

		self::assertCount(1, $unneeded);
	}

	public function test_find_unneeded_ignores_already_trashed_posts(): void
	{
		$this->estatePost('kept', 'Berlin', ['office']);
		$this->estatePost('gone', 'Hamburg', ['retail'], status: 'trash');

		self::assertSame([], EstatePost::findUnneeded(['kept']));
	}

	public function test_reconcile_trashes_only_the_estates_that_disappeared(): void
	{
		$keptId = $this->estatePost('kept', 'Berlin', ['office']);
		$goneId = $this->estatePost('gone', 'Hamburg', ['retail']);

		SyncRunStatus::start(['kept'], [null]);

		$this->manager()->reconcile();

		self::assertSame([$goneId], WpState::instance()->trashedPosts);
		self::assertSame('publish', WpState::instance()->post($keptId)['post_status']);

		$run = SyncRunStatus::get();
		self::assertSame('finished', $run['status']);
		self::assertSame(1, $run['deleted']);
		self::assertLessThanOrEqual(time(), $run['finished_at']);
	}

	public function test_reconcile_rebuilds_the_filter_options_from_what_is_left(): void
	{
		$this->estatePost('a', 'Berlin', ['office']);
		$this->estatePost('b', 'Berlin', ['retail']);
		$this->estatePost('c', 'Hamburg', ['office', 'storage']);
		$goneId = $this->estatePost('gone', 'Munich', ['gastronomy']);

		SyncRunStatus::start(['a', 'b', 'c'], [null]);

		$this->manager()->reconcile();

		$options = DOMOS::instance()->options;

		self::assertSame([$goneId], WpState::instance()->trashedPosts);

		// Unique, and no trace of the trashed estate's city or usage.
		self::assertEqualsCanonicalizing(['Berlin', 'Hamburg'], $options->cities->fresh());
		self::assertEqualsCanonicalizing(['office', 'retail', 'storage'], $options->usages->fresh());
		self::assertNotContains('Munich', $options->cities->fresh());
		self::assertNotContains('gastronomy', $options->usages->fresh());
	}

	public function test_reconcile_ignores_the_trash_when_it_rebuilds_the_options(): void
	{
		$this->estatePost('a', 'Berlin', ['office']);
		$this->estatePost('gone', 'Munich', ['gastronomy'], status: 'trash');

		SyncRunStatus::start(['a'], [null]);

		$this->manager()->reconcile();

		self::assertSame(['Berlin'], DOMOS::instance()->options->cities->fresh());
		self::assertSame(['office'], DOMOS::instance()->options->usages->fresh());
	}

	/**
	 * The guard that stops a stale or finished run from deleting anything: only
	 * a run that is still marked running may reconcile.
	 */
	public function test_reconcile_does_nothing_without_a_running_run(): void
	{
		$this->estatePost('kept', 'Berlin', ['office']);
		$goneId = $this->estatePost('gone', 'Hamburg', ['retail']);

		// No run at all.
		$this->manager()->reconcile();

		self::assertSame([], WpState::instance()->trashedPosts, 'Without a run record nothing may be trashed.');
		self::assertSame('publish', WpState::instance()->post($goneId)['post_status']);

		// A run that already finished.
		SyncRunStatus::start(['kept'], [null]);
		SyncRunStatus::finish(0);

		$this->manager()->reconcile();

		self::assertSame([], WpState::instance()->trashedPosts, 'A finished run must not trash anything on a second pass.');
	}

	public function test_reconcile_keeps_everything_when_the_remote_list_is_complete(): void
	{
		$this->estatePost('a', 'Berlin', ['office']);
		$this->estatePost('b', 'Hamburg', ['retail']);

		SyncRunStatus::start(['a', 'b'], [null]);

		$this->manager()->reconcile();

		self::assertSame([], WpState::instance()->trashedPosts);
		self::assertSame(0, SyncRunStatus::get()['deleted']);
	}

	public function test_the_options_start_empty_when_every_estate_is_gone(): void
	{
		$this->estatePost('gone', 'Munich', ['gastronomy']);

		SyncRunStatus::start([], [null]);

		$this->manager()->reconcile();

		// An emptied list is stored, and reads back as "empty": Option::fresh()
		// falls back to the option's default for any falsy stored value, and
		// CitiesOption/UsagesOption have no default, so that is null rather than
		// []. Both consumers already read it as `->get() ?? []`, which is why
		// this is pinned as "empty" instead of as an exact value.
		self::assertEmpty(DOMOS::instance()->options->cities->fresh());
		self::assertEmpty(DOMOS::instance()->options->usages->fresh());
		self::assertInstanceOf(CitiesOption::class, DOMOS::instance()->options->cities);
		self::assertInstanceOf(UsagesOption::class, DOMOS::instance()->options->usages);
	}
}
