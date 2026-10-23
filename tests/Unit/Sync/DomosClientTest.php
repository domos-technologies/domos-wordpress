<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Sync;

use Domos\Core\Exceptions\CannotConnectToDomos;
use Domos\Core\Exceptions\CouldNotSync;
use Domos\Core\Exceptions\PluginNotConfigured;
use Domos\Core\Sync\DomosClient;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;
use WP_Error;

/**
 * The API client is the boundary between the plugin and the immocore API, so
 * every failure mode here turns into what the admin screen shows the user.
 * `composer audit` and a live sync both start with this class reading a
 * response correctly.
 */
final class DomosClientTest extends TestCase
{

	protected function setUp(): void
	{
		WpState::reset();
		$this->boot('https://example.immocore.app');

	}

	/**
	 * The client asks URLResolver for the base URL, which reads the option through
	 * the DOMOS singleton - and that instance caches option values for the whole
	 * process. So every test needs the option set *and* a fresh instance.
	 */
	private function boot(string $url): void
	{
		WpState::instance()->options['domos_url'] = $url;

		Fixtures::bootDomos();
	}


	/** @param array<string, mixed> $payload */
	private function respond(int $status, array $payload): void
	{
		WpState::instance()->httpResponse = [
			'headers' => [],
			'body' => json_encode($payload),
			'response' => ['code' => $status, 'message' => ''],
			'cookies' => [],
		];
	}

	private function client(string $host = 'example.immocore.app'): DomosClient
	{
		return new DomosClient($host);
	}

	/** Point both the option and the client's host at the same site. */
	private function clientFor(string $host): DomosClient
	{
		$this->boot("https://{$host}");

		return new DomosClient($host);
	}

	public function test_set_host_strips_the_scheme(): void
	{
		$client = $this->client();
		$client->setHost('https://anh-hausbesitz.immocore.app');

		self::assertSame('anh-hausbesitz.immocore.app', $client->host);
	}

	public function test_every_request_is_built_with_https(): void
	{
		$this->respond(200, ['data' => []]);

		$this->clientFor('anh-hausbesitz.immocore.app')->estateIds();

		// The API token travels in a header, so a plain http URL would leak it.
		self::assertStringStartsWith('https://anh-hausbesitz.immocore.app/', WpState::instance()->httpRequests[0]['url']);
	}

	public function test_it_sends_the_api_token_as_a_bearer_header(): void
	{
		WpState::instance()->options['domos_token'] = 'secret-token';
		$this->respond(200, ['data' => []]);

		$this->client()->estateIds();

		self::assertSame('Bearer secret-token', WpState::instance()->httpRequests[0]['args']['headers']['Authorization']);
	}

	public function test_it_sends_no_authorization_header_without_a_token(): void
	{
		$this->respond(200, ['data' => []]);

		$this->client()->estateIds();

		self::assertArrayNotHasKey('Authorization', WpState::instance()->httpRequests[0]['args']['headers']);
	}

	public function test_it_does_not_verify_tls_for_the_configured_test_host(): void
	{
		$this->respond(200, ['data' => []]);

		$this->clientFor('anh-hausbesitz.immocore.test')->estateIds();

		// Local development uses self-signed certificates, and they expire often.
		self::assertFalse(WpState::instance()->httpRequests[0]['args']['sslverify']);
	}

	public function test_it_verifies_tls_for_a_real_host(): void
	{
		$this->respond(200, ['data' => []]);

		$this->clientFor('anh-hausbesitz.immocore.app')->estateIds();

		self::assertTrue(WpState::instance()->httpRequests[0]['args']['sslverify']);
	}

	public function test_it_refuses_to_call_without_a_configured_host(): void
	{
		$client = new DomosClient(null);
		$client->host = null;

		$this->expectException(PluginNotConfigured::class);

		$client->estateIds();
	}

	public function test_an_unreachable_api_raises_could_not_sync(): void
	{
		WpState::instance()->httpResponse = new WP_Error('http_request_failed', 'cURL error 28: Operation timed out');

		$this->expectException(CouldNotSync::class);
		$this->expectExceptionMessage('cURL error 28: Operation timed out');

		$this->client()->estateIds();
	}

	public function test_an_error_response_carries_the_api_error_code_and_message(): void
	{
		$this->respond(403, ['error' => ['message' => 'Dieser Kunde ist nicht berechtigt.', 'code' => 'unauthorized']]);

		try {
			$this->client()->estateIds();
			self::fail('Expected CouldNotSync.');
		} catch (CouldNotSync $e) {
			self::assertSame('Dieser Kunde ist nicht berechtigt.', $e->getMessage());
			self::assertSame('unauthorized', $e->errorCode);
		}
	}

	public function test_an_error_response_without_a_message_still_reports_something(): void
	{
		$this->respond(500, []);

		try {
			$this->client()->estateIds();
			self::fail('Expected CouldNotSync.');
		} catch (CouldNotSync $e) {
			self::assertSame('Unknown error', $e->getMessage());
		}
	}

	public function test_a_response_that_is_not_json_is_reported_rather_than_crashing(): void
	{
		WpState::instance()->httpResponse = [
			'headers' => [],
			'body' => '<html>maintenance</html>',
			'response' => ['code' => 200, 'message' => 'OK'],
			'cookies' => [],
		];

		try {
			$this->client()->estateIds();
			self::fail('Expected CouldNotSync.');
		} catch (CouldNotSync $e) {
			self::assertNotSame('', $e->getMessage());
		}
	}

	public function test_estate_ids_returns_only_strings(): void
	{
		// A malformed payload must not put a nested array into the work list.
		$this->respond(200, ['data' => ['estate-1', 42, null, ['nested'], 'estate-2']]);

		self::assertSame(['estate-1', 'estate-2'], $this->client()->estateIds());
	}

	public function test_estate_ids_returns_an_empty_list_when_the_api_omits_data(): void
	{
		$this->respond(200, []);

		self::assertSame([], $this->client()->estateIds());
	}

	public function test_estate_returns_a_hydrated_estate(): void
	{
		$this->respond(200, ['data' => [
			'id' => 'estate-1',
			'slug' => 'test-estate',
			'name' => 'Test Estate',
			'address' => ['street' => 'Teststrasse', 'city' => 'Berlin'],
		]]);

		$estate = $this->client()->estate('estate-1');

		self::assertSame('estate-1', $estate?->id);
		self::assertSame('Test Estate', $estate?->name);
		self::assertSame('Berlin', $estate?->address->city);
	}

	public function test_estate_appends_the_language_to_the_request(): void
	{
		$this->respond(200, ['data' => [
			'id' => 'estate-1', 'slug' => 's', 'name' => 'n', 'address' => ['street' => 'x'],
		]]);

		$this->client()->estate('estate-1', language: 'en');

		self::assertStringContainsString('?language=en', WpState::instance()->httpRequests[0]['url']);
	}

	public function test_estate_reports_a_404_as_could_not_sync(): void
	{
		$this->respond(404, ['error' => ['message' => 'Not found', 'code' => 'not_found']]);

		$this->expectException(CouldNotSync::class);

		$this->client()->estate('gone');
	}

	/**
	 * whoami() is declared with a CannotConnectToDomos in its body, but its catch
	 * block is a bare Throwable and re-throws CouldNotSync. saveApiSettings()
	 * therefore reports a whoami failure as an 'unknown' error rather than a
	 * connection problem. The behaviour is pinned here because the settings
	 * screen depends on the message reaching the user.
	 */
	public function test_whoami_reports_failures_as_could_not_sync(): void
	{
		WpState::instance()->httpResponse = new WP_Error('http_request_failed', 'cURL error 6: Could not resolve host');

		try {
			$this->client()->whoami();
			self::fail('Expected an exception.');
		} catch (CannotConnectToDomos $e) {
			self::fail('whoami() does not surface CannotConnectToDomos, so saveApiSettings cannot map it.');
		} catch (CouldNotSync $e) {
			self::assertStringContainsString('Could not resolve host', $e->getMessage());
		}
	}

	public function test_whoami_returns_the_customer_payload(): void
	{
		$this->respond(200, ['data' => ['customer' => ['name' => 'ANH Hausbesitz', 'language' => 'DE']]]);

		self::assertSame('ANH Hausbesitz', $this->client()->whoami()['customer']['name']);
	}
}
