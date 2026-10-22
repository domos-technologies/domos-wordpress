<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use Domos\Core\Providers\ApiServiceProvider;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_REST_Request;

/**
 * The API service exposes three admin routes and one public route. Two things
 * have to hold, and both of them have been wrong at some point:
 *
 *  - the admin routes must not be reachable without the manage_options
 *    capability, and must require the REST nonce (no cookie-only requests, so
 *    no CSRF)
 *  - the public route runs on untrusted input, so it must answer with an error
 *    instead of a fatal, and must never write debug output into the response -
 *    a var_dump() there handed anonymous callers the server's file paths, the
 *    plugin's internals and a full stack trace
 */
final class RestApiTest extends TestCase
{
	/** @var array<string, array<string, mixed>> */
	private array $routes = [];

	private string|false $previousErrorLog = false;

	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://example.test';

		Fixtures::bootDomos();

		// The failure paths log on purpose; keep that out of the test output.
		$this->previousErrorLog = ini_get('error_log');
		ini_set('error_log', '/dev/null');

		(new ApiServiceProvider)->register();
		WpState::instance()->runAction('rest_api_init');

		$this->routes = [];
		foreach (WpState::instance()->restRoutes as $registered) {
			$this->routes[$registered['namespace'].$registered['route']] = $registered['args'];
		}
	}

	protected function tearDown(): void
	{
		ini_set('error_log', $this->previousErrorLog === false ? '' : $this->previousErrorLog);
	}

	/** @return array<string, array{0: string, 1: string, 2: string}> */
	public static function adminRoutes(): array
	{
		return [
			'sync' => ['domos/admin', '/sync', 'POST'],
			'sync status' => ['domos/admin', '/sync-status', 'GET'],
			'save api settings' => ['domos/admin', '/save-api-settings', 'POST'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('adminRoutes')]
	public function test_admin_routes_are_registered(string $namespace, string $route, string $method): void
	{
		$registered = WpState::instance()->restRoute($namespace, $route);

		self::assertNotNull($registered, "{$namespace}{$route} is not registered.");
		self::assertSame($method, $registered['methods']);
	}

	/**
	 * @param string[] $capabilities
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider('adminRoutes')]
	public function test_admin_routes_refuse_a_user_without_manage_options(string $namespace, string $route, string $method): void
	{
		$permission = $this->routes[$namespace.$route]['permission_callback'];

		WpState::instance()->capabilities = ['read', 'edit_posts'];

		self::assertFalse($permission(), "{$namespace}{$route} is reachable without manage_options.");
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('adminRoutes')]
	public function test_admin_routes_allow_an_administrator(string $namespace, string $route, string $method): void
	{
		$permission = $this->routes[$namespace.$route]['permission_callback'];

		WpState::instance()->capabilities = ['manage_options'];

		self::assertTrue($permission());
	}

	/**
	 * The inquiry route is public on purpose - it is the contact form on the
	 * front end - so the test documents that intent rather than asserting it
	 * away. Anything reachable there must treat its input as untrusted.
	 */
	public function test_inquiry_route_is_intentionally_public(): void
	{
		$permission = $this->routes['domos/inquiry/submit']['permission_callback'];

		WpState::instance()->capabilities = [];

		self::assertTrue($permission());
	}

	public function test_inquiry_route_rejects_a_missing_or_non_object_body(): void
	{
		$callback = $this->routes['domos/inquiry/submit']['callback'];

		foreach ([null, 'a string', 42, ['a', 'list', 'not', 'an', 'object']] as $body) {
			$result = $callback(new WP_REST_Request([], $body));

			self::assertInstanceOf(WP_Error::class, $result, 'A malformed body must return an error, not throw.');
			self::assertSame('validation', $result->get_error_code());
			self::assertSame(400, $result->get_error_data()['status'] ?? null);
		}
	}

	/**
	 * Guards the regression directly: a var_dump() in this path replied to an
	 * anonymous request with a 200 and the full exception object.
	 */
	public function test_inquiry_route_never_writes_debug_output_into_the_response(): void
	{
		$callback = $this->routes['domos/inquiry/submit']['callback'];

		// The client double cannot reach a host, so inquireRaw() fails and the
		// error branch runs - exactly the path that used to dump the throwable.
		ob_start();

		try {
			$result = $callback(new WP_REST_Request([], ['email' => 'someone@example.test']));
		} finally {
			$output = ob_get_clean();
		}

		self::assertSame('', trim((string) $output), 'The route wrote output into the response.');
		self::assertInstanceOf(WP_Error::class, $result);
		self::assertStringNotContainsString('wp-content', print_r($result, true));
		self::assertStringNotContainsString('#0 ', print_r($result, true), 'The error response must not carry a stack trace.');
	}

	public function test_save_api_settings_rejects_a_host_outside_the_immocore_domains(): void
	{
		$callback = $this->routes['domos/admin/save-api-settings']['callback'];

		$result = $callback(new WP_REST_Request([
			'url' => 'https://internal.example.com',
			'token' => null,
			'languages' => [],
		]));

		self::assertInstanceOf(WP_Error::class, $result);
		self::assertSame('invalid_url', $result->get_error_code());
	}

	public function test_save_api_settings_rejects_a_plain_http_url(): void
	{
		$callback = $this->routes['domos/admin/save-api-settings']['callback'];

		// A plain http URL would send the API token in the clear.
		$result = $callback(new WP_REST_Request([
			'url' => 'http://anh-hausbesitz.immocore.app',
			'token' => 'secret',
			'languages' => [],
		]));

		self::assertInstanceOf(WP_Error::class, $result);
		self::assertSame('invalid_url', $result->get_error_code());
	}

	public function test_save_api_settings_stores_no_url_when_validation_fails(): void
	{
		$callback = $this->routes['domos/admin/save-api-settings']['callback'];

		$callback(new WP_REST_Request([
			'url' => 'https://internal.example.com',
			'token' => 'secret',
			'languages' => [],
		]));

		self::assertSame('https://example.test', WpState::instance()->options['domos_url'] ?? null);
	}
}
