<?php

declare(strict_types=1);

use Domos\Core\Tests\Support\WpState;

/**
 * Minimal WordPress class doubles. These are global classes on purpose, so the
 * plugin's type hints (WP_Post, WP_Query) resolve without a WordPress install.
 *
 * Only the members the plugin actually touches are implemented, and
 * WP_Query throws on query arguments it does not understand rather than
 * quietly matching nothing - a test double that silently returns no rows is
 * how you get green tests over broken code.
 */

if (! defined('ABSPATH') && PHP_SAPI !== 'cli') {
	exit(1);
}

if (! class_exists('WP_Error')) {
	class WP_Error
	{
		public function __construct(
			protected string $code = '',
			protected string $message = '',
			protected mixed $data = '',
		) {
		}

		public function get_error_code(): string
		{
			return $this->code;
		}

		public function get_error_message(): string
		{
			return $this->message;
		}

		public function get_error_data(): mixed
		{
			return $this->data;
		}
	}
}

if (! class_exists('WP_Post')) {
	class WP_Post
	{
		public int $ID = 0;
		public string $post_type = 'post';
		public string $post_status = 'publish';
		public string $post_title = '';
		public string $post_name = '';
		public string $post_content = '';
		public string $post_excerpt = '';
		public int $post_author = 1;

		/** @param array<string, mixed> $fields */
		public function __construct(array $fields = [])
		{
			foreach ($fields as $key => $value) {
				if (property_exists($this, $key)) {
					$this->{$key} = $value;
				}
			}
		}

		/** @return array<string, mixed> */
		public function to_array(): array
		{
			return get_object_vars($this);
		}
	}
}

if (! class_exists('WP_Query')) {
	class WP_Query
	{
		/** @var array<int, WP_Post|int> */
		public array $posts = [];

		public int $post_count = 0;

		/** @var array<string, mixed> */
		private array $args;

		/** @param array<string, mixed> $query */
		public function __construct(array $query = [])
		{
			$unsupported = array_diff(array_keys($query), [
				'post_type',
				'post_status',
				'posts_per_page',
				'meta_key',
				'meta_value',
				'fields',
			]);

			if ($unsupported !== []) {
				throw new RuntimeException(
					'WP_Query double does not support: '.implode(', ', $unsupported)
					.'. Extend tests/Support/wp-classes.php instead of writing a test that passes for the wrong reason.'
				);
			}

			$this->args = $query;
			$this->posts = $this->match();
			$this->post_count = count($this->posts);
		}

		public function have_posts(): bool
		{
			return $this->posts !== [];
		}

		public function the_post(): void
		{
		}

		/** @return array<int, WP_Post|int> */
		private function match(): array
		{
			$state = WpState::instance();
			$wantedType = $this->args['post_type'] ?? null;
			$wantedStatuses = $this->args['post_status'] ?? null;
			$metaKey = $this->args['meta_key'] ?? null;
			$metaValue = $this->args['meta_value'] ?? null;
			$idsOnly = ($this->args['fields'] ?? null) === 'ids';

			if (is_string($wantedStatuses)) {
				$wantedStatuses = [$wantedStatuses];
			}

			$matched = [];

			foreach ($state->posts as $id => $post) {
				if ($wantedType !== null && ($post['post_type'] ?? null) !== $wantedType) {
					continue;
				}

				if (is_array($wantedStatuses) && ! in_array($post['post_status'] ?? null, $wantedStatuses, true)) {
					continue;
				}

				if ($metaKey !== null) {
					$actual = $state->postMeta[$id][$metaKey] ?? null;
					$isArrayMatch = is_array($metaValue)
						? in_array($actual, $metaValue, true)
						: $actual == $metaValue;

					if (! $isArrayMatch) {
						continue;
					}
				}

				$matched[] = $idsOnly ? $id : new WP_Post($post);
			}

			$limit = (int) ($this->args['posts_per_page'] ?? -1);

			return $limit > 0 ? array_slice($matched, 0, $limit) : $matched;
		}
	}
}

if (! class_exists('WP_REST_Request')) {
	/**
	 * Enough of a REST request for the route callbacks under test: query and body
	 * parameters plus the JSON body WordPress would have decoded.
	 */
	class WP_REST_Request
	{
		/** @var array<string, mixed> */
		private array $params = [];

		private mixed $json = null;

		/** @param array<string, mixed> $params */
		public function __construct(array $params = [], mixed $json = null)
		{
			$this->params = $params;
			$this->json = $json;
		}

		public function get_param(string $key): mixed
		{
			return $this->params[$key] ?? null;
		}

		public function get_json_params(): mixed
		{
			return $this->json;
		}
	}
}
