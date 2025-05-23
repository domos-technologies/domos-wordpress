<?php

namespace Domos\Core\Sync;

use Domos\Core\DOMOS;
use Domos\Core\Exceptions\CannotConnectToDomos;
use Domos\Core\Exceptions\CouldNotSync;
use Domos\Core\Exceptions\InvalidDomosResponse;
use Domos\Core\Exceptions\InvalidInquiry;
use Domos\Core\Exceptions\PluginNotConfigured;
use SchemaImmo\Estate;

class DomosClient
{
	public ?string $host = null;

	public function __construct(?string $host)
	{
		if ($host) {
			$this->setHost($host);

			add_filter(
				"https_ssl_verify",
				function ($params, $url) {
					return $this->shouldVerifySsl($url);
				},
				10,
				2
			);
		}
	}

	public function setHost(string $host)
	{
		$this->host = str($host)->replace(["http://", "https://"], "");
	}

	public function estate(string $id): ?Estate
	{
		$this->verifyHost();

		try {
			$endpoint = DOMOS::instance()->urlResolver->estateSyncOneUrl($id);
			$token = DOMOS::instance()->options->token->get();

			$response = wp_remote_get($endpoint, [
				"sslverify" => $this->shouldVerifySsl($endpoint),
				// Add API key to header
				"headers" => array_filter([
					"Authorization" => $token ? "Bearer {$token}" : null,
					"Content-Type" => "application/json",
					"Accept" => "application/json",
				]),
			]);

			if (is_wp_error($response)) {
				throw new \Exception($response->get_error_message());
			}

			// status
			$status = wp_remote_retrieve_response_code($response);

			$body = wp_remote_retrieve_body($response);
			$data = json_decode(
				$body,
				true,
				512,
				JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
			);

			if ($status === 200) {
				return Estate::from($data['data']);
			} else {
				$error = "Unknown error";
				$code = null;

				if (isset($data["error"]) && isset($data["error"]["message"])) {
					$error = $data["error"]["message"];

					if (isset($data["error"]["code"])) {
						$code = $data["error"]["code"];
					}
				}

				throw new CouldNotSync($error, $code);
			}
		} catch (CouldNotSync $e) {
			throw $e;
		} catch (\Throwable $th) {
			throw new CouldNotSync($th->getMessage());
		}
	}

	public function estates(): array
	{
		$this->verifyHost();

		try {
			$endpoint = DOMOS::instance()->urlResolver->estateSyncAllUrl();
			$token = DOMOS::instance()->options->token->get();

			$response = wp_remote_get($endpoint, [
				"sslverify" => $this->shouldVerifySsl($endpoint),
				// Add API key to header
				"headers" => array_filter([
					"Authorization" => $token ? "Bearer {$token}" : null,
					"Content-Type" => "application/json",
					"Accept" => "application/json",
				]),
			]);

			if (is_wp_error($response)) {
				throw new \Exception($response->get_error_message());
			}

			// status
			$status = wp_remote_retrieve_response_code($response);

			$body = wp_remote_retrieve_body($response);
			$data = json_decode(
				$body,
				true,
				512,
				JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
			);

			if ($status === 200) {
				$estates = [];

				foreach ($data["data"] as $estateData) {
					$estates[] = Estate::from($estateData);
				}

				return $estates;
			} else {
				$error = "Unknown error";
				$code = null;

				if (isset($data["error"]) && isset($data["error"]["message"])) {
					$error = $data["error"]["message"];

					if (isset($data["error"]["code"])) {
						$code = $data["error"]["code"];
					}
				}

				throw new CouldNotSync($error, $code);
			}
		} catch (CouldNotSync $e) {
			throw $e;
		} catch (\Throwable $th) {
			throw new CouldNotSync($th->getMessage());
		}
	}

	public function whoami(): array
	{
		$this->verifyHost();

		try {
			$endpoint = "https://{$this->host}/api/whoami/v1";

			$response = wp_remote_get($endpoint, [
				"sslverify" => $this->shouldVerifySsl($endpoint),
			]);

			// Error states:
			//  - invalid URL (cant connect / 404)
			//  - unauthorized (API not public)

			if (is_wp_error($response)) {
				throw new CannotConnectToDomos(
					$response->get_error_message(),
					"connection"
				);
			}

			// status
			$status = wp_remote_retrieve_response_code($response);

			$body = wp_remote_retrieve_body($response);

			$data = json_decode(
				$body,
				true,
				512,
				JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
			);

			if ($status === 200) {
				return $data["data"];
			} else {
				$error = "Unbekannter Fehler";
				$code = "unknown";

				if (isset($data["error"]) && isset($data["error"]["message"])) {
					$error = $data["error"]["message"];

					if (isset($data["error"]["code"])) {
						$code = $data["error"]["code"];
					}
				}

				throw new CannotConnectToDomos($error, $code);
			}
		} catch (\Throwable $th) {
			throw new CouldNotSync($th->getMessage());
		}
	}

	public function inquireRaw(array $data)
	{
		$this->verifyHost();

		try {
			$endpoint = "https://{$this->host}/api/inquiries/v1/submit";

			$response = wp_remote_post($endpoint, [
				"body" => wp_json_encode($data),
				"headers" => [
					"Content-Type" => "application/json",
				],
				"data_format" => "body",
				"sslverify" => $this->shouldVerifySsl($endpoint),
			]);

			// Error states:
			//  - invalid URL (cant connect / 404)
			//  - unauthorized (API not public)

			if (is_wp_error($response)) {
				throw new CannotConnectToDomos(
					$response->get_error_message(),
					"connection"
				);
			}

			// status
			$status = wp_remote_retrieve_response_code($response);

			$body = wp_remote_retrieve_body($response);

			$data = json_decode(
				$body,
				true,
				512,
				JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
			);

			if ($status === 200) {
				return $data;
			} else {
				$error = "Unbekannter Fehler";
				$code = "unknown";

				if (
					isset($data["error"]) &&
					isset($data["error"]["code"]) &&
					$data["error"]["code"] === "validation"
				) {
					throw new InvalidInquiry($data["error"]);
				} elseif (
					isset($data["error"]) &&
					isset($data["error"]["message"])
				) {
					$error = $data["error"]["message"];

					if (isset($data["error"]["code"])) {
						$code = $data["error"]["code"];
					}
				}

				throw new CannotConnectToDomos($error, $code);
			}
		} catch (\JsonException $exception) {
			throw new InvalidDomosResponse($exception->getMessage());
		}
	}

	protected function shouldVerifySsl($url): bool
	{
		$isOurRequest = parse_url($url, PHP_URL_HOST) === $this->host;
		$isTestDomain = str($this->host)->endsWith(".test");

		if ($isOurRequest && $isTestDomain) {
			return false;
		}

		return true;
	}

	protected function logThrowable(\Throwable $th)
	{
		error_log(
			print_r(
				[
					"message" => $th->getMessage(),
					"file" => $th->getFile(),
					"line" => $th->getLine(),
					"trace" => $th->getTrace(),
				],
				true
			)
		);
	}

	protected function verifyHost()
	{
		if (!$this->host) {
			throw new PluginNotConfigured(
				"Es wurde keine URL für DOMOS angegeben."
			);
		}
	}
}
