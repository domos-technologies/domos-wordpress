<?php

namespace Domos\Core\Sync;

use Domos\Core\DOMOS;
use Domos\Core\EstatePost;
use Domos\Core\Exceptions\CouldNotSync;

class SyncManager
{
	public function __construct(protected SyncQueue $queue)
	{
	}

	/**
	 * Start a chunked background sync of all estates in all configured
	 * languages. Returns immediately.
	 *
	 * @return array{already_running: bool, run: array|null}
	 *
	 * @throws CouldNotSync
	 */
	public function start(): array
	{
		if ($this->queue->is_active()) {
			return ['already_running' => true, 'run' => SyncRunStatus::get()];
		}

		// The queue is drained by a worker we reach through a loopback request
		// to our own site. wp-background-processing fires that request without
		// waiting for an answer, and its HTTP layer drops transport errors for
		// non-blocking requests, so an unreachable loopback would leave the run
		// stuck at zero forever with nothing to show for it. Probe it up front
		// and fail loudly instead.
		$this->assertWorkerIsReachable();

		$ids = DOMOS::instance()->api->estateIds();

		// null = default language; must come first so newly created posts
		// get their title/content from the default language payload.
		$languages = [null];

		foreach (DOMOS::instance()->options->languages->get() ?? [] as $language) {
			if (is_string($language) && strlen($language) === 2) {
				$languages[] = strtolower($language);
			}
		}

		$languages = array_values(array_unique($languages, SORT_REGULAR));

		$run = SyncRunStatus::start($ids, $languages);

		foreach ($languages as $language) {
			foreach ($ids as $id) {
				$this->queue->push_to_queue([
					'estate_id' => $id,
					'language' => $language,
					'attempt' => 1,
				]);
			}
		}

		$dispatched = $this->queue->save()->dispatch();

		if (is_wp_error($dispatched)) {
			// The worker is reached through a loopback request to our own
			// site. When that request never lands (expired TLS certificate,
			// blocked localhost traffic, ...) the queue would sit untouched
			// while the admin UI polls a run that never progresses. Drop the
			// queue so a retry starts clean, and report what went wrong.
			$this->queue->delete_all();
			SyncRunStatus::clear();

			throw new CouldNotSync(
				'Der Hintergrund-Sync konnte nicht gestartet werden: '.$dispatched->get_error_message(),
				'cannot_dispatch'
			);
		}

		return ['already_running' => false, 'run' => $run];
	}

	/**
	 * The background worker is started by requesting admin-ajax.php on the
	 * site itself. A call that never arrives (expired TLS certificate, blocked
	 * localhost traffic, ...) is invisible to the dispatcher, so check that the
	 * loopback works before queueing anything.
	 *
	 * @throws CouldNotSync
	 */
	protected function assertWorkerIsReachable(): void
	{
		$response = wp_remote_get(admin_url('admin-ajax.php'), [
			'blocking' => true,
			'timeout' => 10,
			'sslverify' => apply_filters('https_local_ssl_verify', false),
		]);

		if (is_wp_error($response)) {
			throw new CouldNotSync(
				'Der Hintergrund-Sync kann die eigene Website nicht erreichen: '.$response->get_error_message(),
				'cannot_reach_worker'
			);
		}
	}

	/**
	 * Runs once after the queue is drained: trash posts whose estates no
	 * longer exist and rebuild the city/usage filter options from post meta.
	 */
	public function reconcile(): void
	{
		$run = SyncRunStatus::get();

		if (!$run || $run['status'] !== 'running') {
			return;
		}

		$deleted = 0;

		foreach (EstatePost::findUnneeded($run['estate_ids']) as $post) {
			wp_trash_post($post->id);
			$deleted++;
		}

		$this->rebuildFilterOptions();

		SyncRunStatus::finish($deleted);
	}

	/**
	 * The legacy sync computed cities/usages from the full API payload in
	 * memory. The same values are persisted per post as searchable meta
	 * (estate_city, estate_usages) by EstatePost::setSearchableMeta(), so
	 * they can be rebuilt from the database at any time.
	 */
	protected function rebuildFilterOptions(): void
	{
		$query = new \WP_Query([
			'post_type' => EstatePost::POST_TYPE,
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
		]);

		$cities = [];
		$usages = [];

		foreach ($query->posts as $postId) {
			$city = get_post_meta($postId, 'estate_city', true);

			if (is_string($city) && $city !== '') {
				$cities[] = $city;
			}

			$usagesJson = get_post_meta($postId, 'estate_usages', true);
			$postUsages = is_string($usagesJson) ? json_decode($usagesJson, true) : null;

			if (is_array($postUsages)) {
				foreach ($postUsages as $usage) {
					if (is_string($usage) && $usage !== '') {
						$usages[] = $usage;
					}
				}
			}
		}

		DOMOS::instance()->options->cities->set(array_values(array_unique($cities)));
		DOMOS::instance()->options->usages->set(array_values(array_unique($usages)));
	}
}
