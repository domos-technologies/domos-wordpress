<?php

namespace Domos\Core\Sync;

use Domos\Core\DOMOS;
use Domos\Core\EstatePost;

/**
 * Background import queue. Each item imports ONE estate in ONE language
 * via its own small API request, so no single PHP request ever has to
 * process the whole portfolio.
 *
 * Items: ['estate_id' => string, 'language' => string|null, 'attempt' => int]
 * (language null = default language, mirroring the legacy sync semantics)
 */
class SyncQueue extends \WP_Background_Process
{
	protected $prefix = 'domos';

	protected $action = 'estate_sync';

	protected function task($item)
	{
		$estateId = $item['estate_id'] ?? null;
		$language = $item['language'] ?? null;

		if (!is_string($estateId) || $estateId === '') {
			return false;
		}

		try {
			$this->import($estateId, $language);
		} catch (\Throwable $th) {
			$attempt = (int) ($item['attempt'] ?? 1);

			if ($attempt < 3) {
				$item['attempt'] = $attempt + 1;

				// Re-run within the same queue; wp-background-processing has
				// no delayed retries. Three quick attempts cover transient
				// network errors; persistent failures are recorded and the
				// estate is picked up again by the next (nightly) run.
				return $item;
			}

			error_log("[domos-sync] Failed to import estate {$estateId} ({$language}): {$th->getMessage()}");
			SyncRunStatus::fail("{$estateId}:".($language ?? 'default'), $th->getMessage());
		}

		return false;
	}

	protected function import(string $estateId, ?string $language): void
	{
		$domos = DOMOS::instance();

		$estate = $domos->api->estate($estateId, language: $language);

		if ($estate === null) {
			// Estate disappeared between id fetch and import; reconcile will trash it.
			SyncRunStatus::record('processed');

			return;
		}

		$shouldReactivatePosts = $domos->shouldReuseTrashedPosts();
		$existingPost = EstatePost::find($estate->id, language: $language, trashed: $shouldReactivatePosts);

		if ($existingPost) {
			if ($shouldReactivatePosts && $existingPost->post?->post_status === 'trash') {
				wp_untrash_post($existingPost->post->ID);

				wp_update_post([
					'ID' => $existingPost->post->ID,
					'post_status' => 'publish',
				]);
			}

			EstatePost::update($estate->id, $estate, language: $language);
			SyncRunStatus::record('updated');
		} else {
			EstatePost::create($estate->id, $estate, language: $language);
			SyncRunStatus::record('created');
		}
	}

	protected function complete()
	{
		parent::complete();

		DOMOS::instance()->sync->reconcile();
	}
}