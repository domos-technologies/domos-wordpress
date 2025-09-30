<?php

namespace Domos\Core\Providers;

use Domos\Core\DOMOS;
use Domos\Core\Exceptions\CannotConnectToDomos;
use Domos\Core\Exceptions\CouldNotSync;
use Domos\Core\Exceptions\InvalidInquiry;
use Domos\Core\Exceptions\PluginNotConfigured;

class ApiServiceProvider implements Provider
{
    public function register()
    {
        add_action('rest_api_init', function () {
            register_rest_route('domos/admin', '/sync', [
                'methods' => 'POST',
                'callback' => function (\WP_REST_Request $request) {
					$language = $request->get_param('language');

					// While we don't validate language codes (to not have to update the plugin every time we add a new language)
					// we only allow 2-letter language codes.
					if (!$language || strlen($language) !== 2) {
						$language = null;
					} else {
						$language = strtolower($language);
					}

					return $this->sync(language: $language);
				},
                'permission_callback' => function () {
                    // check if user can manage options
                    if (current_user_can('manage_options')) {
			            return true;
			        }

					// Disable for now
					return false;
                },
            ]);

			register_rest_route('domos/admin', '/save-api-settings', [
                'methods' => 'POST',
                'callback' => function (\WP_REST_Request $request) {
					$url = $request->get_param('url');
					$token = $request->get_param('token');
					$languages = $request->get_param('languages') ?? [];

					if (!is_array($languages)) {
						$languages = [];
					}

					return $this->saveApiSettings($url, $token, $languages ?? []);
                },
                'permission_callback' => function () {
                    // check if user can manage options
                    return current_user_can('manage_options');
                },
            ]);

			register_rest_route('domos/inquiry', '/submit', [
                'methods' => 'POST',
                'callback' => function (\WP_REST_Request $request) {
					$jsonBody = $request->get_json_params();

					return $this->passInquiryToDomos($jsonBody);
                },
                'permission_callback' => function () {
                    return true;
                },
            ]);
        });
    }

    protected function logThrowable(\Throwable $th)
    {
        error_log(print_r([
            'message' => $th->getMessage(),
            'file' => $th->getFile(),
            'line' => $th->getLine(),
            'trace' => $th->getTrace(),
        ], true));
    }

    public function boot()
    {
    }

	protected function sync(?string $language = null)
	{
		try {
			if (DOMOS::instance()->url() === null) {
				return new \WP_Error('no_url', 'Es wurde noch keine URL angegeben.');
			}

            [$created, $updated, $deleted] = DOMOS::instance()->sync->synchronize(language: $language);

            return [
                'created' => $created,
                'updated' => $updated,
                'deleted' => $deleted,
            ];
        } catch (CouldNotSync $th) {
            $this->logThrowable($th);

            // set status code
            return new \WP_Error($th->errorCode, $th->getMessage());
        } catch (\Throwable $th) {
            $this->logThrowable($th);

            // set status code
            $fileAndLine = "{$th->getFile()}:{$th->getLine()}";

            if (WP_DEBUG) {
                $message = $th->getMessage();
                $message .= " [$fileAndLine]";
            } else {
                $message = 'Ein unbekannter Fehler ist aufgetreten.';
            }

            return new \WP_Error('unknown', $message);
        }
	}

	protected function saveApiSettings(string $url, ?string $token = null, array $languages = [])
	{
		$options = DOMOS::instance()->options;

		// Starts with https://
		if (substr($url, 0, 8) !== 'https://') {
			return new \WP_Error('invalid_url', 'Die URL muss mit https:// beginnen.');
		}

		// Ends with .domos.test or .domos.app or .immocore.test or .immocore.app
		if (!preg_match('/\.domos\.(test|app)$/', $url) && !preg_match('/\.immocore\.(test|app)$/', $url)) {
			return new \WP_Error('invalid_url', 'Die URL muss folgende Endungen haben: .domos.test, .domos.app, .immocore.test, .immocore.app');
		}

		// sanitize
		$url = filter_var($url, FILTER_SANITIZE_URL);

		// Remove trailing slash
		$url = rtrim($url, '/');

		try {
			$options->url->set($url);
		} catch (\Exception $th) {
			return new \WP_Error('invalid_url', 'Die URL ist ungültig.');
		}

		try {
			$options->token->set($token);
		} catch (\Exception $th) {
			return new \WP_Error('invalid_token', 'Der Token ist ungültig.');
		}

		try {
			DOMOS::instance()->api->setHost($url);
			$data = DOMOS::instance()->api->whoami();

			if (isset($data['customer']) && isset($data['customer']['name'])) {
				$message = "Hallo, {$data['customer']['name']}!";
			} else {
				$message = 'Verbindung hergestellt.';
			}

			if (isset($data['customer']) && isset($data['customer']['language'])) {
				$language = $data['customer']['language'];

				$language = strtolower($language);

				if (strlen($language) !== 2) {
					$language = 'de';
				}
			} else {
				$language = 'de';
			}


			$languages = array_filter($languages, fn ($lang) => strlen($lang) === 2 && $lang !== $language);
			$languages = array_map(fn ($language) => strtolower($language), $languages);
			$languages = array_unique($languages);
			$languages = array_values($languages);

			$options->languages->set($languages);
			$options->default_language->set($language);

			return [
				'success' => true,
				'message' => $message,
				'languages' => $languages,
				'default_language' => $language,
			];
		} catch (CannotConnectToDomos $th) {
			$this->logThrowable($th);

			return new \WP_Error($th->errorCode, $th->getMessage());
		} catch (\Throwable $th) {
			$this->logThrowable($th);

			return new \WP_Error('unknown', WP_DEBUG
				? $th->getMessage()
				: 'Ein unbekannter Fehler ist aufgetreten.'
			);
		}
	}

	protected function passInquiryToDomos(array $json)
	{
		try {
			return DOMOS::instance()->api->inquireRaw($json);
		} catch (InvalidInquiry $exception) {
			return new \WP_Error('validation', $exception->getMessage(), $exception->errorData);
		} catch (CannotConnectToDomos $exception) {
			return new \WP_Error('cannot_connect_to_domos', $exception->getMessage());
		} catch (\Throwable $exception) {
			var_dump($exception);
			return new \WP_Error('unknown', $exception->getMessage());
		}
	}
}
