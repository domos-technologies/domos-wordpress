<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Support;

use Domos\Core\Sync\SyncQueue;

/**
 * SyncQueue with the protected task handler exposed, so the retry and failure
 * handling can be driven directly instead of through a full queue run.
 */
final class TestableSyncQueue extends SyncQueue
{
	/** @param array<string, mixed> $item */
	public function runTask(array $item): mixed
	{
		return $this->task($item);
	}

	public function runImport(string $estateId, ?string $language): void
	{
		$this->import($estateId, $language);
	}
}
