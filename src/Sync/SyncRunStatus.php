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

	public static function get(): ?array
	{
		$run = get_option(self::OPTION);

		return is_array($run) ? $run : null;
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
			'failures' => [],
			'started_at' => time(),
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

		update_option(self::OPTION, $run, false);
	}

	public static function finish(int $deleted): void
	{
		$run = self::get();

		if (!$run) {
			return;
		}

		$run['status'] = 'finished';
		$run['deleted'] = $deleted;
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
