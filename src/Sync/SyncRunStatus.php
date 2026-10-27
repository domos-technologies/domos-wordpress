<?php

namespace Domos\Core\Sync;

/**
 * Durable state of the current/last sync run, stored in a single WP option.
 * Written only by the (single, sequential) background queue and the
 * orchestrator, so no locking is needed.
 */
class SyncRunStatus
{
	public const OPTION = 'domos_sync_run';

	/**
	 * A run counts as stalled when it is still marked running but has not recorded
	 * any progress for this long. The queue's own healthcheck cron retries every
	 * five minutes, so being three times that far behind means neither the worker
	 * request nor the cron is coming back on its own.
	 */
	public const STALL_AFTER = 15 * MINUTE_IN_SECONDS;

	public static function get(): ?array
	{
		$run = get_option(self::OPTION);

		return is_array($run) ? $run : null;
	}

	/**
	 * Has a run stopped making progress without ending? Used by the orchestrator
	 * to clear its own way before starting a new run, and by the admin screen to
	 * say so instead of showing counters that never move.
	 */
	public static function is_stalled(?array $run): bool
	{
		if (!$run || ($run['status'] ?? null) !== 'running') {
			return false;
		}

		// Runs written before progress_at existed fall back to their start time.
		$lastProgress = (int) ($run['progress_at'] ?? $run['started_at'] ?? 0);

		return $lastProgress > 0 && (time() - $lastProgress) > self::STALL_AFTER;
	}

	/**
	 * @param string[] $estateIds
	 * @param array<int, string|null> $languages null = default language
	 */
	public static function start(array $estateIds, array $languages): array
	{
		$run = [
			'id' => wp_generate_uuid4(),
			'status' => 'running',
			'estate_ids' => array_values($estateIds),
			'languages' => array_values($languages),
			'total' => count($estateIds) * count($languages),
			'processed' => 0,
			'created' => 0,
			'updated' => 0,
			'deleted' => 0,
			// Set when a run was stopped before it built a queue, so the admin
			// screen can explain why nothing happened instead of showing counters.
			// See SyncManager::start().
			'skipped' => null,
			// Set when an administrator stopped the run; see SyncManager::cancel().
			'cancelled' => false,
			'failures' => [],
			'started_at' => time(),
			// Refreshed on every imported estate; SyncRunStatus::is_stalled() reads it.
			'progress_at' => time(),
			'finished_at' => null,
		];

		update_option(self::OPTION, $run, false);

		return $run;
	}

	public static function record(string $counter): void
	{
		$run = self::get();

		if (!$run || $run['status'] !== 'running') {
			return;
		}

		$run['processed']++;

		if ($counter !== 'processed' && array_key_exists($counter, $run) && is_int($run[$counter])) {
			$run[$counter]++;
		}

		$run['progress_at'] = time();

		update_option(self::OPTION, $run, false);
	}

	public static function fail(string $itemKey, string $message): void
	{
		$run = self::get();

		if (!$run || $run['status'] !== 'running') {
			return;
		}

		$run['processed']++;
		$run['failures'][$itemKey] = $message;
		// A failed estate is still progress: the worker is alive and working
		// through the queue.
		$run['progress_at'] = time();

		update_option(self::OPTION, $run, false);
	}

	/**
	 * Record that an administrator stopped the run. The counters are kept so the
	 * admin screen can show how far it got. Marking the run finished is what keeps
	 * reconcile() from trashing the estates that were simply not reached yet.
	 *
	 * Only a running run can be cancelled: a click after the run already finished
	 * must not turn a completed sync into a cancelled one in the record.
	 */
	public static function cancel(): void
	{
		$run = self::get();

		if (!$run || ($run['status'] ?? null) !== 'running') {
			return;
		}

		$run['status'] = 'finished';
		$run['cancelled'] = true;
		$run['finished_at'] = time();

		update_option(self::OPTION, $run, false);
	}

	/**
	 * @param int $deleted Number of posts the reconcile step trashed.
	 * @param string|null $skipped Reason the run imported nothing, when it was
	 *                             stopped before the queue was built.
	 */
	public static function finish(int $deleted, ?string $skipped = null): void
	{
		$run = self::get();

		if (!$run) {
			return;
		}

		$run['status'] = 'finished';
		$run['deleted'] = $deleted;
		$run['skipped'] = $skipped;
		$run['finished_at'] = time();

		update_option(self::OPTION, $run, false);
	}

	/**
	 * Forget the current run. Used when a run never made it to the queue, so
	 * the admin UI does not poll a run that can never progress.
	 */
	public static function clear(): void
	{
		delete_option(self::OPTION);
	}
}
