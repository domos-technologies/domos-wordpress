<?php

namespace Domos\Core\Sync;

use Domos\Core\DOMOS;
use Domos\Core\EstatePost;
use Domos\Core\Exceptions\CannotConnectToDomos;
use Domos\Core\Exceptions\CouldNotSync;
use SchemaImmo\Building;
use SchemaImmo\Estate;
use SchemaImmo\Rentable;

class SyncManager
{
    public function synchronize()
    {
		$domos = DOMOS::instance();

        $estates = $domos->api->estates();

		$cities = array_map(function (Estate $estate) {
			return $estate->address->city;
		}, $estates);

		$usages = [];

		foreach ($estates as $estate) {
			foreach ($estate->buildings as $building) {
				foreach ($building->rentables as $rentable) {
					foreach ($rentable->spaces as $space) {
						$usages[] = $space->type->slug;
					}
				}
			}
		}

		$domos->options->cities->set($cities);
		$domos->options->usages->set($usages);

        $created = 0;
        $deleted = 0;
        $updated = 0;

        foreach ($estates as $estate) {
            $existingPost = EstatePost::find($estate->id);

            if ($existingPost) {
                EstatePost::update($estate->id, $estate);
                $updated++;
            } else {
                EstatePost::create($estate->id, $estate);
                $created++;
            }
        }

        $ids = array_map(function (Estate $estate) {
            return $estate->id;
        }, $estates);

        $postsToDelete = EstatePost::findUnneeded($ids);

        foreach ($postsToDelete as $post) {
            wp_delete_post($post->id, true);
            $deleted++;
        }

        return [$created, $updated, $deleted];
    }
}
