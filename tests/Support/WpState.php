<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Support;

/**
 * In-memory stand-in for the parts of WordPress this plugin touches.
 *
 * The global function doubles in tests/Support/wp-functions.php delegate here,
 * so tests can arrange state and assert on what the plugin tried to do. One
 * instance exists per test process; call reset() in setUp() so state cannot
 * leak between tests.
 */
class WpState
{
	protected static ?self $instance = null;

	/** @var array<string, mixed> */
	public array $options = [];

	/** @var array<int, array<string, mixed>> */
	public array $posts = [];

	/** @var array<int, array<string, mixed>> post id => meta key => value */
	public array $postMeta = [];

	/** @var array<string, array<int, callable>> */
	public array $filters = [];

	/** @var array<string, array<int, callable>> */
	public array $actions = [];

	/** @var array<int, array<string, mixed>> recorded wp_insert_post() argument lists */
	public array $insertedPosts = [];

	/** @var array<int, array<string, mixed>> recorded wp_update_post() argument lists */
	public array $updatedPosts = [];

	/** @var array<int, array<string, mixed>> every wp_insert_post()/wp_update_post() call, in order */
	public array $savedPosts = [];

	/** @var array<int, array{url: string, args: array<string, mixed>}> */
	public array $httpRequests = [];

	/** @var mixed What the next wp_remote_*() call returns. */
	public mixed $httpResponse = null;

	/** @var array<int, string> */
	public array $scheduledEvents = [];

	/** @var array<int, array{post_type: string, args: array<string, mixed>}> */
	public array $registeredPostTypes = [];

	public int $rewriteRuleFlushes = 0;

	/** @var (callable(bool): void)|null Called on every flush_rewrite_rules(), with the $hard flag. */
	public $onRewriteRuleFlush = null;

	public int $nextPostId = 100;

	public static function instance(): self
	{
		return self::$instance ??= new self;
	}

	public static function reset(): void
	{
		self::$instance = new self;
	}

	/**
	 * Register a post so it shows up in WP_Query doubles.
	 *
	 * @param array<string, mixed> $fields
	 */
	public function addPost(array $fields, array $meta = []): int
	{
		$id = (int) ($fields['ID'] ?? $this->nextPostId++);
		$this->nextPostId = max($this->nextPostId, $id + 1);

		$post = array_merge([
			'ID' => $id,
			'post_type' => 'post',
			'post_status' => 'publish',
			'post_title' => '',
			'post_name' => '',
			'post_content' => '',
			'post_excerpt' => '',
			'post_author' => 1,
		], $fields, ['ID' => $id]);

		$this->posts[$id] = $post;

		foreach ($meta as $key => $value) {
			$this->postMeta[$id][$key] = $value;
		}

		return $id;
	}

	/** @return array<string, mixed>|null */
	public function post(int $id): ?array
	{
		return $this->posts[$id] ?? null;
	}

	public function metaValue(int $postId, string $key, mixed $default = null): mixed
	{
		return $this->postMeta[$postId][$key] ?? $default;
	}

	/**
	 * Fire a registered WP hook, the way WordPress would.
	 */
	public function runAction(string $hook): void
	{
		foreach ($this->actions[$hook] ?? [] as $callback) {
			$callback();
		}
	}

	/** @return array<int, string> */
	public function actionHooks(): array
	{
		return array_keys($this->actions);
	}

	/** @return array<string, mixed> The most recent wp_insert_post()/wp_update_post() argument list. */
	public function lastSavedPost(): array
	{
		$saved = end($this->savedPosts);

		return is_array($saved) ? $saved : [];
	}
}
