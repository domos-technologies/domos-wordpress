<?php

namespace Domos\Core\Sync;

use Domos\Core\DOMOS;
use Domos\Core\EstatePost;
use SchemaImmo\Estate;

class SyncManager
{
	public const MAX_EXECUTION_TIME_FILTER = "domos_sync_max_execution_time";

	public function __construct()
	{
	}

	public function synchronize(?string $language = null)
	{
		$maxExecutionTime = apply_filters(self::MAX_EXECUTION_TIME_FILTER, 600);

		if (!is_numeric($maxExecutionTime) || $maxExecutionTime < 600) {
			$maxExecutionTime = 600;
		}

		// Set maxium execution time to 10 minutes
		set_time_limit($maxExecutionTime);

		$domos = DOMOS::instance();
		$estates = $domos->api->estates(language: $language);

		$cities = array_map(function (Estate $estate) {
			return $estate->address->city;
		}, $estates);

		$usages = [];

		foreach ($estates as $estate) {
			foreach ($estate->buildings as $building) {
				foreach ($building->rentables as $rentable) {
					foreach ($rentable->spaces as $space) {
						$usages[] = $space->type->value;
					}
				}
			}
		}

		// Make cities unique
		$cities = array_unique($cities);
		$usages = array_unique($usages);

		$domos->options->cities->set($cities);
		$domos->options->usages->set($usages);

		$created = 0;
		$deleted = 0;
		$updated = 0;

		foreach ($estates as $estate) {
			$existingPost = EstatePost::find($estate->id, language: $language);

			if ($existingPost) {
				EstatePost::update($estate->id, $estate, language: $language);
				$updated++;
			} else {
				EstatePost::create($estate->id, $estate, language: $language);
				$created++;
			}
		}

		$ids = array_map(function (Estate $estate) {
			return $estate->id;
		}, $estates);

		$postsToDelete = EstatePost::findUnneeded($ids);

		foreach ($postsToDelete as $post) {
			wp_trash_post($post->id);
			$deleted++;
		}

		return [$created, $updated, $deleted];
	}
}
