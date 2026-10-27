<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Sync;

use Domos\Core\Sync\SyncRunStatus;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;

/**
 * The run record is the only thing the admin UI polls, so a wrong counter or a
 * status that never leaves "running" shows up as an eternal spinner.
 */
final class SyncRunStatusTest extends TestCase
{
	protected function setUp(): void
	{
		WpState::reset();
	}

	public function test_get_returns_null_when_no_run_was_started(): void
	{
		self::assertNull(SyncRunStatus::get());
	}

	public function test_start_writes_a_running_run_with_zeroed_counters(): void
	{
		$run = SyncRunStatus::start(['a', 'b', 'c'], [null]);

		self::assertSame('running', $run['status']);
		self::assertNotSame('', $run['id']);
		self::assertSame(0, $run['processed']);
		self::assertSame(0, $run['created']);
		self::assertSame(0, $run['updated']);
		self::assertSame(0, $run['deleted']);
		self::assertSame([], $run['failures']);
		self::assertNull($run['finished_at']);
		self::assertSame($run, SyncRunStatus::get());
	}

	public function test_start_counts_one_item_per_estate_and_language(): void
	{
		$run = SyncRunStatus::start(['a', 'b'], [null, 'en', 'fr']);

		self::assertSame(6, $run['total']);
	}

	public function test_start_records_the_ids_and_languages_it_was_given(): void
	{
		$run = SyncRunStatus::start(['a'], [null, 'en']);

		self::assertSame(['a'], $run['estate_ids']);
		self::assertSame([null, 'en'], $run['languages']);
	}

	public function test_record_increments_the_processed_counter_and_the_named_counter(): void
	{
		SyncRunStatus::start(['a', 'b'], [null]);

		SyncRunStatus::record('created');
		SyncRunStatus::record('updated');

		$run = SyncRunStatus::get();

		self::assertSame(2, $run['processed']);
		self::assertSame(1, $run['created']);
		self::assertSame(1, $run['updated']);
		self::assertSame(0, $run['deleted']);
	}

	public function test_record_counts_processed_even_for_an_unknown_counter_name(): void
	{
		SyncRunStatus::start(['a'], [null]);

		SyncRunStatus::record('processed');
		SyncRunStatus::record('something-else');

		$run = SyncRunStatus::get();

		self::assertSame(2, $run['processed']);
		self::assertSame(0, $run['created']);
	}

	public function test_record_is_ignored_once_the_run_is_no_longer_running(): void
	{
		SyncRunStatus::start(['a'], [null]);
		SyncRunStatus::finish(0);

		SyncRunStatus::record('created');

		$run = SyncRunStatus::get();

		self::assertSame(0, $run['processed']);
		self::assertSame(0, $run['created']);
	}

	public function test_fail_records_the_message_against_the_item_key(): void
	{
		SyncRunStatus::start(['a', 'b'], [null]);

		SyncRunStatus::fail('a:default', 'Connection reset');

		$run = SyncRunStatus::get();

		self::assertSame(1, $run['processed']);
		self::assertSame(['a:default' => 'Connection reset'], $run['failures']);
		self::assertSame('running', $run['status']);
	}

	public function test_finish_marks_the_run_finished_and_stores_the_deleted_count(): void
	{
		SyncRunStatus::start(['a'], [null]);

		SyncRunStatus::finish(3);

		$run = SyncRunStatus::get();

		self::assertSame('finished', $run['status']);
		self::assertSame(3, $run['deleted']);
		self::assertNotNull($run['finished_at']);
	}

	public function test_clear_removes_the_run_so_the_ui_stops_polling(): void
	{
		SyncRunStatus::start(['a'], [null]);

		SyncRunStatus::clear();

		self::assertNull(SyncRunStatus::get());
		self::assertArrayNotHasKey(SyncRunStatus::OPTION, WpState::instance()->options);
	}

	public function test_a_fresh_run_is_not_stalled(): void
	{
		self::assertFalse(SyncRunStatus::is_stalled(SyncRunStatus::start(['a'], [null])));
	}

	public function test_a_run_that_stopped_reporting_progress_is_stalled_until_it_reports_again(): void
	{
		SyncRunStatus::start(['a'], [null]);
		$this->pretendNoProgressFor(SyncRunStatus::STALL_AFTER + 1);

		self::assertTrue(SyncRunStatus::is_stalled(SyncRunStatus::get()));

		SyncRunStatus::record('processed');

		self::assertFalse(SyncRunStatus::is_stalled(SyncRunStatus::get()));
	}

	public function test_a_failed_estate_counts_as_progress(): void
	{
		SyncRunStatus::start(['a'], [null]);
		$this->pretendNoProgressFor(SyncRunStatus::STALL_AFTER + 1);

		SyncRunStatus::fail('a:default', 'boom');

		// The worker is alive and working through the queue, even when estates fail.
		self::assertFalse(SyncRunStatus::is_stalled(SyncRunStatus::get()));
	}

	public function test_a_run_written_before_progress_existed_falls_back_to_its_start_time(): void
	{
		$run = SyncRunStatus::start(['a'], [null]);
		unset($run['progress_at']);
		$run['started_at'] = time() - SyncRunStatus::STALL_AFTER - 1;

		update_option(SyncRunStatus::OPTION, $run);

		self::assertTrue(SyncRunStatus::is_stalled(SyncRunStatus::get()));
	}

	public function test_missing_and_finished_runs_are_never_stalled(): void
	{
		self::assertFalse(SyncRunStatus::is_stalled(null));

		SyncRunStatus::start(['a'], [null]);
		SyncRunStatus::finish(0, skipped: 'no_estates');

		self::assertFalse(SyncRunStatus::is_stalled(SyncRunStatus::get()));
	}

	public function test_cancel_ends_the_run_and_keeps_the_counters(): void
	{
		SyncRunStatus::start(['a', 'b'], [null, 'en']);
		SyncRunStatus::record('updated');

		SyncRunStatus::cancel();

		$run = SyncRunStatus::get();

		self::assertSame('finished', $run['status']);
		self::assertTrue($run['cancelled']);
		self::assertNotNull($run['finished_at']);
		self::assertSame(1, $run['processed'], 'What ran before the cancel stays visible.');
		self::assertSame(1, $run['updated']);
	}

	public function test_cancel_without_a_run_does_nothing(): void
	{
		SyncRunStatus::cancel();

		self::assertNull(SyncRunStatus::get());
	}

	public function test_cancel_leaves_a_run_that_already_ended_alone(): void
	{
		SyncRunStatus::start(['a'], [null]);
		SyncRunStatus::finish(1);

		$before = SyncRunStatus::get();

		SyncRunStatus::cancel();

		// A click after the run finished must not turn a completed sync into a
		// cancelled one.
		self::assertSame($before, SyncRunStatus::get());
		self::assertFalse(SyncRunStatus::get()['cancelled']);
	}

	/** Move the run's last progress into the past, as a dead worker would leave it. */
	private function pretendNoProgressFor(int $seconds): void
	{
		$run = SyncRunStatus::get();
		$run['progress_at'] = time() - $seconds;

		update_option(SyncRunStatus::OPTION, $run);
	}
}
