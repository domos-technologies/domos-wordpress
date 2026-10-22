<?php

declare(strict_types=1);

use Domos\Core\Tests\Support\WpState;

/**
 * Minimal WordPress function doubles, just enough to exercise the plugin's own
 * logic without a WordPress install. Each double delegates to WpState so tests
 * can arrange input and assert on output.
 *
 * This file is only ever loaded by the PHPUnit bootstrap (tests/bootstrap.php).
 * It is not autoloaded, and it must never be loaded alongside WordPress core -
 * these declarations are unconditional, so a real WordPress would collide with
 * them. If integration tests against a real install are ever added, they need
 * their own bootstrap.
 */

if (! defined('ABSPATH') && PHP_SAPI !== 'cli') {
	exit(1);
}

function get_option(string $option, mixed $default = false): mixed
{
	$state = WpState::instance();

	return array_key_exists($option, $state->options) ? $state->options[$option] : $default;
}

function add_option(string $option, mixed $value = ''): bool
{
	$state = WpState::instance();

	if (array_key_exists($option, $state->options)) {
		return false;
	}

	$state->options[$option] = $value;

	return true;
}

function update_option(string $option, mixed $value): bool
{
	WpState::instance()->options[$option] = $value;

	return true;
}

function delete_option(string $option): bool
{
	unset(WpState::instance()->options[$option]);

	return true;
}

function get_post_meta(int|string $postId, string $key = '', bool $single = false): mixed
{
	$state = WpState::instance();
	$postId = (int) $postId;

	if ($key === '') {
		return $state->postMeta[$postId] ?? [];
	}

	$value = $state->postMeta[$postId][$key] ?? null;

	if ($single) {
		return $value;
	}

	return $value === null ? [] : [$value];
}

function update_post_meta(int|string $postId, string $key, mixed $value): bool
{
	WpState::instance()->postMeta[(int) $postId][$key] = $value;

	return true;
}

function add_post_meta(int|string $postId, string $key, mixed $value, bool $unique = false): bool
{
	$postId = (int) $postId;
	$state = WpState::instance();

	if ($unique && array_key_exists($key, $state->postMeta[$postId] ?? [])) {
		return false;
	}

	$state->postMeta[$postId][$key] = $value;

	return true;
}

function delete_post_meta(int|string $postId, string $key): bool
{
	unset(WpState::instance()->postMeta[(int) $postId][$key]);

	return true;
}

/**
 * @param array<string, mixed> $data
 */
function wp_insert_post(array $data, bool $wpError = false): int
{
	$state = WpState::instance();
	$id = $state->addPost($data);

	$state->insertedPosts[] = $data;
	$state->savedPosts[] = $data;

	return $id;
}

/**
 * @param array<string, mixed> $data
 */
function wp_update_post(array $data, bool $wpError = false): int
{
	$state = WpState::instance();
	$id = (int) ($data['ID'] ?? 0);

	if (isset($state->posts[$id])) {
		foreach ($data as $key => $value) {
			$state->posts[$id][$key] = $value;
		}

		// The row identity stays an int, whatever the caller passed.
		$state->posts[$id]['ID'] = $id;
	}

	$state->updatedPosts[] = $data;
	$state->savedPosts[] = $data;

	return $id;
}

function get_post(int|string $id): ?WP_Post
{
	$post = WpState::instance()->post((int) $id);

	return $post === null ? null : new WP_Post($post);
}

/**
 * @param array<string, mixed> $args
 */
function wp_remote_get(string $url, array $args = []): mixed
{
	return wp_remote_request($url, $args);
}

/**
 * @param array<string, mixed> $args
 */
function wp_remote_post(string $url, array $args = []): mixed
{
	return wp_remote_request($url, $args);
}

/**
 * @param array<string, mixed> $args
 */
function wp_remote_request(string $url, array $args = []): mixed
{
	$state = WpState::instance();
	$state->httpRequests[] = ['url' => $url, 'args' => $args];

	if ($state->httpResponse !== null) {
		return $state->httpResponse;
	}

	return [
		'headers' => [],
		'body' => '',
		'response' => ['code' => 200, 'message' => 'OK'],
		'cookies' => [],
	];
}

function is_wp_error(mixed $thing): bool
{
	return $thing instanceof WP_Error;
}

function admin_url(string $path = ''): string
{
	return 'https://example.test/wp-admin/'.ltrim($path, '/');
}

function current_user_can(string $capability): bool
{
	return in_array($capability, WpState::instance()->capabilities, true);
}

/**
 * @param array<string, mixed> $args
 */
function register_rest_route(string $namespace, string $route, array $args = [], bool $override = false): bool
{
	WpState::instance()->restRoutes[] = ['namespace' => $namespace, 'route' => $route, 'args' => $args];

	return true;
}

function rest_url(string $path = ''): string
{
	return 'https://example.test/wp-json/'.ltrim($path, '/');
}

function wp_generate_uuid4(): string
{
	static $counter = 0;

	return sprintf('00000000-0000-4000-8000-%012d', ++$counter);
}

function wp_next_scheduled(string $hook): bool
{
	return in_array($hook, WpState::instance()->scheduledEvents, true);
}

function wp_schedule_event(int $timestamp, string $recurrence, string $hook, array $args = []): bool
{
	WpState::instance()->scheduledEvents[] = $hook;

	return true;
}

function wp_unschedule_hook(string $hook): int
{
	$state = WpState::instance();
	$before = count($state->scheduledEvents);

	$state->scheduledEvents = array_values(array_filter(
		$state->scheduledEvents,
		fn (string $scheduled) => $scheduled !== $hook,
	));

	return $before - count($state->scheduledEvents);
}

function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
{
	WpState::instance()->actions[$hook][] = $callback;

	return true;
}

/**
 * @param array<string, mixed> $args
 */
function register_post_type(string $postType, array $args = []): object
{
	WpState::instance()->registeredPostTypes[] = ['post_type' => $postType, 'args' => $args];

	return (object) ['name' => $postType];
}

function flush_rewrite_rules(bool $hard = true): void
{
	$state = WpState::instance();
	$state->rewriteRuleFlushes++;

	if ($state->onRewriteRuleFlush !== null) {
		($state->onRewriteRuleFlush)($hard);
	}
}

function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
{
	WpState::instance()->filters[$hook][] = $callback;

	return true;
}

function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
{
	foreach (WpState::instance()->filters[$hook] ?? [] as $callback) {
		$value = $callback($value, ...$args);
	}

	return $value;
}
