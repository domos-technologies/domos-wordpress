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

	/** @var mixed Returned by estate(), or thrown when it is a Throwable. */
	public mixed $estateResult = null;

	/** When true, estate() answers null: the estate vanished between the id list and the import. */
	public bool $estateMissing = false;

	/** @var string[] Estate ids passed to estate(), in order. */
	public array $estateCalls = [];

	/** @var mixed Thrown by estateIds() when set. */
	public mixed $estateIdsThrows = null;

	/** @var mixed Thrown by whoami() when set. */
	public mixed $whoamiThrows = null;

	public function estateIds(): array
	{
		$this->estateIdsCalls++;

		if ($this->estateIdsThrows !== null) {
			throw $this->estateIdsThrows;
		}

		return $this->estateIdsResponse;
	}

	public function estate(string $id, ?string $language = null): ?\SchemaImmo\Estate
	{
		$this->estateCalls[] = $id . ':' . ($language ?? 'default');

		if ($this->estateResult instanceof \Throwable) {
			throw $this->estateResult;
		}

		if ($this->estateMissing) {
			return null;
		}

		if ($this->estateResult === null) {
			return Fixtures::estate(['id' => $id]);
		}

		return $this->estateResult;
	}

	/** @var array<string, mixed> Returned by whoami(). */
	public array $whoamiResponse = ['customer' => ['name' => 'Test Kunde', 'language' => 'de']];

	/** @var mixed Returned by inquireRaw(), or thrown when it is a Throwable. */
	public mixed $inquireResult = ['data' => ['ok' => true]];

	/** @var array<int, array<string, mixed>> Payloads passed to inquireRaw(). */
	public array $inquireCalls = [];

	public function whoami(): array
	{
		if ($this->whoamiThrows !== null) {
			throw $this->whoamiThrows;
		}

		return $this->whoamiResponse;
	}

	public function inquireRaw(array $data)
	{
		$this->inquireCalls[] = $data;

		if ($this->inquireResult instanceof \Throwable) {
			throw $this->inquireResult;
		}

		return $this->inquireResult;
	}
}
