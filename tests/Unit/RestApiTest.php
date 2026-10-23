<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use Domos\Core\Providers\ApiServiceProvider;
use Domos\Core\Tests\Support\FakeDomosClient;
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

	private FakeDomosClient $client;


	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://example.test';

		$this->client = Fixtures::bootDomos();

		// The failure paths log on purpose; keep that out of the test output.

		(new ApiServiceProvider)->register();
		WpState::instance()->runAction('rest_api_init');

		$this->routes = [];
		foreach (WpState::instance()->restRoutes as $registered) {
			$this->routes[$registered['namespace'].$registered['route']] = $registered['args'];
		}
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

		// Force the generic failure branch of passInquiryToDomos(), which is the
		// one that used to var_dump() the throwable.
		$this->client->inquireResult = new \RuntimeException('Der Sync-Dienst ist nicht erreichbar.');

		ob_start();

		try {
			$result = $callback(new WP_REST_Request([], ['email' => 'someone@example.test']));
		} finally {
			$output = ob_get_clean();
		}

		self::assertSame('', trim((string) $output), 'The route wrote output into the response.');
		self::assertInstanceOf(WP_Error::class, $result);
		self::assertStringContainsString('nicht erreichbar', print_r($result, true), 'The message still reaches the visitor.');
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

	public function test_sync_reports_a_missing_api_url_instead_of_starting(): void
	{
		$callback = $this->routes['domos/admin/sync']['callback'];

		// A site that has the plugin but has not configured it yet.
		WpState::instance()->options['domos_url'] = '';
		Fixtures::bootDomos();

		$result = $callback(new WP_REST_Request);

		self::assertInstanceOf(WP_Error::class, $result);
		self::assertSame('no_url', $result->get_error_code());
	}

	public function test_sync_surfaces_the_error_code_from_a_failed_start(): void
	{
		$callback = $this->routes['domos/admin/sync']['callback'];
		Fixtures::useFakeQueue();

		// The probe fails, so SyncManager::start() throws before queueing.
		WpState::instance()->httpResponse = new WP_Error('http_request_failed', 'cURL error 60: certificate has expired');

		$result = $callback(new WP_REST_Request);

		self::assertInstanceOf(WP_Error::class, $result);
		self::assertSame('cannot_reach_worker', $result->get_error_code());
		self::assertStringContainsString('certificate has expired', $result->get_error_message());
	}

	public function test_sync_reports_an_unexpected_failure_without_leaking_internals(): void
	{
		$callback = $this->routes['domos/admin/sync']['callback'];
		Fixtures::useFakeQueue();

		// estateIds() blows up with something that is not a CouldNotSync.
		$this->client->estateIdsThrows = new \TypeError('Argument #1 must be of type array, string given in /wp-content/plugins/domos-core/src/...');

		$result = $callback(new WP_REST_Request);

		self::assertInstanceOf(WP_Error::class, $result);
		self::assertSame('unknown', $result->get_error_code());
		self::assertSame(
			'Ein unbekannter Fehler ist aufgetreten.',
			$result->get_error_message(),
			'With WP_DEBUG off (the production setting) the message must not carry internals.'
		);
	}

	public function test_sync_starts_a_run_when_the_api_answers(): void
	{
		$callback = $this->routes['domos/admin/sync']['callback'];
		$queue = Fixtures::useFakeQueue();
		$this->client->estateIdsResponse = ['estate-1', 'estate-2'];

		$result = $callback(new WP_REST_Request);

		self::assertIsArray($result);
		self::assertTrue($result['started'], 'started is !already_running, so a fresh run reports true.');
		self::assertSame(2, $result['run']['total']);
		self::assertSame('running', $result['run']['status']);
		self::assertCount(2, $queue->queued, 'Both estates are queued for the worker.');
		self::assertTrue($queue->saved);
	}

	public function test_save_api_settings_stores_the_url_and_the_languages_from_whoami(): void
	{
		$callback = $this->routes['domos/admin/save-api-settings']['callback'];
		$this->client->whoamiResponse = ['customer' => ['name' => 'ANH Hausbesitz', 'language' => 'DE']];

		$result = $callback(new WP_REST_Request([
			'url' => 'https://anh-hausbesitz.immocore.app/',
			'token' => 'secret',
			'languages' => ['EN', 'de', 'fr', 'deutschland'],
		]));

		self::assertSame('Hallo, ANH Hausbesitz!', $result['message']);
		// The trailing slash is trimmed, the default language is detected and
		// lowercased, the default is excluded from the translation list and
		// anything that is not a two-letter code is dropped.
		self::assertSame('https://anh-hausbesitz.immocore.app', WpState::instance()->options['domos_url']);
		self::assertSame('de', $result['default_language']);
		self::assertSame(['en', 'fr'], $result['languages']);
		self::assertSame('secret', WpState::instance()->options['domos_token']);
	}

	public function test_save_api_settings_says_hello_generically_when_whoami_has_no_customer(): void
	{
		$callback = $this->routes['domos/admin/save-api-settings']['callback'];
		$this->client->whoamiResponse = [];

		$result = $callback(new WP_REST_Request([
			'url' => 'https://anh-hausbesitz.immocore.app',
			'token' => null,
			'languages' => [],
		]));

		self::assertSame('Verbindung hergestellt.', $result['message']);
		self::assertSame('de', $result['default_language'], 'An unknown customer language falls back to German.');
	}

	public function test_save_api_settings_reports_a_whoami_failure_without_leaking_internals(): void
	{
		$callback = $this->routes['domos/admin/save-api-settings']['callback'];
		$this->client->whoamiThrows = new \RuntimeException('cURL error 6: Could not resolve host');

		$result = $callback(new WP_REST_Request([
			'url' => 'https://anh-hausbesitz.immocore.app',
			'token' => null,
			'languages' => [],
		]));

		self::assertInstanceOf(WP_Error::class, $result);
		self::assertSame('unknown', $result->get_error_code());
		self::assertSame('Ein unbekannter Fehler ist aufgetreten.', $result->get_error_message());
	}

	public function test_inquiry_route_forwards_the_payload_and_returns_the_response(): void
	{
		$callback = $this->routes['domos/inquiry/submit']['callback'];
		$payload = ['email' => 'someone@example.test', 'message' => 'Bitte um Rückruf'];
		$this->client->inquireResult = ['data' => ['received' => true]];

		$result = $callback(new WP_REST_Request([], $payload));

		self::assertSame([$payload], $this->client->inquireCalls);
		self::assertSame(['data' => ['received' => true]], $result);
	}
}
