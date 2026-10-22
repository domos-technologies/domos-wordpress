<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Support;

use Domos\Core\Sync\DomosClient;

/**
 * DomosClient with the estate ID lookup replaced by a fixed list, so tests
 * never touch the network.
 */
final class FakeDomosClient extends DomosClient
{
	/** @var string[] */
	public array $estateIdsResponse = [];

	public int $estateIdsCalls = 0;

	public function __construct()
	{
		// Deliberately skips the parent constructor: no host means no
		// https_ssl_verify filter gets registered.
		$this->host = 'example.test';
	}

	public function estateIds(): array
	{
		$this->estateIdsCalls++;

		return $this->estateIdsResponse;
	}
}
