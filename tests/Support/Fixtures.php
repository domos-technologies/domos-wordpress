<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Support;

use Domos\Core\DOMOS;
use Domos\Core\Sync\SyncManager;
use SchemaImmo\Estate;

/**
 * Small builders for the objects tests need, so each test can state only the
 * part it cares about.
 */
final class Fixtures
{
	/**
	 * Install a DOMOS instance whose API client is a double, and hand that
	 * client back so the test can drive it.
	 *
	 * @param string[] $estateIds
	 */
	public static function bootDomos(array $estateIds = []): FakeDomosClient
	{
		$client = new FakeDomosClient;
		$client->estateIdsResponse = $estateIds;

		$domos = new DOMOS;
		$domos->api = $client;

		DOMOS::$instance = $domos;

		return $client;
	}

	/**
	 * Point the DOMOS singleton at a queue double, so a test that goes through
	 * the REST route does not need $wpdb (the real queue talks to the options
	 * table to store batches).
	 */
	public static function useFakeQueue(): FakeSyncQueue
	{
		$queue = new FakeSyncQueue;
		$domos = DOMOS::instance();
		$domos->queue = $queue;
		$domos->sync = new SyncManager($queue);

		return $queue;
	}

	/**
	 * A minimal but valid estate, built through the same SchemaImmo::from()
	 * entry point the sync uses for real API payloads.
	 *
	 * @param array<string, mixed> $overrides
	 */
	public static function estate(array $overrides = []): Estate
	{
		return Estate::from(array_merge([
			'id' => '11111111-2222-4333-8444-555555555555',
			'slug' => 'test-estate',
			'name' => 'Test Estate',
			'address' => [
				'street' => 'Teststrasse',
				'number' => '1',
				'postal_code' => '10115',
				'city' => 'Berlin',
				'country' => 'Deutschland',
			],
			'texts' => [
				'description' => '<p>Description</p>',
				'slogan' => 'Slogan',
			],
		], $overrides));
	}
}
