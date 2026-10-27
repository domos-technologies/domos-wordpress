<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Support;

use Domos\Core\Sync\SyncQueue;
use WP_Error;

/**
 * SyncQueue with the queueing machinery replaced by recording stubs, so tests
 * can assert on what SyncManager asked the queue to do without a database or a
 * loopback HTTP request.
 *
 * Method signatures deliberately mirror WP_Background_Process (no parameter
 * types on push_to_queue/save, because the parent class has none).
 */
final class FakeSyncQueue extends SyncQueue
{
	public bool $active = false;

	public bool $saved = false;

	public bool $deleteAllCalled = false;

	public bool $abortCalled = false;

	/** @var array<int, array<string, mixed>> */
	public array $queued = [];

	public mixed $dispatchResult = null;

	/** @var mixed When set, dispatch() throws this instead of returning. */
	public mixed $dispatchThrows = null;

	public function is_active()
	{
		return $this->active;
	}

	public function push_to_queue($data)
	{
		$this->queued[] = $data;

		return $this;
	}

	public function save()
	{
		$this->saved = true;

		return $this;
	}

	public function dispatch()
	{
		if ($this->dispatchThrows !== null) {
			throw $this->dispatchThrows;
		}

		return $this->dispatchResult;
	}

	public function delete_all()
	{
		$this->deleteAllCalled = true;
		$this->queued = [];
	}

	/**
	 * Mirrors the real abort(): the queue is emptied and it stops reporting
	 * itself as active, which is what lets a stalled run be replaced.
	 */
	public function abort(): void
	{
		$this->abortCalled = true;
		$this->active = false;
		$this->delete_all();
	}

	/** Simulate the transport failing the way a dead loopback would. */
	public function failDispatch(string $message = 'cURL error 60: SSL certificate has expired'): self
	{
		$this->dispatchResult = new WP_Error('http_request_failed', $message);

		return $this;
	}
}
