<?php

namespace Domos\Core\Sync;

use Domos\Core\DOMOS;
use Domos\Core\EstatePost;
use Domos\Core\Exceptions\CouldNotSync;
use SchemaImmo\Building;
use SchemaImmo\Estate;
use SchemaImmo\Rentable;

class SyncManager
{
    public string $host;

    public function __construct(string $host = 'umbrella.domos.test')
    {
		$this->host = str($host)
			->replace(['http://', 'https://'], '');

        add_filter( 'http_request_args', function( $params, $url ) {
            // find out if this is the request you are targeting and if not: abort
            if ( 0 !== strpos( $url, $this->host ) )
                return $params;

            add_filter( 'https_ssl_verify', '__return_false' );

            return $params;
        }, 10, 2 );
    }

    protected function fetchEstates(): array
    {
        try {
            $endpoint = "https://{$this->host}/api/sync/v1/estates";

            $response = wp_remote_get($endpoint, [
                'sslverify' => false,
            ]);

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            // status
            $status = wp_remote_retrieve_response_code($response);

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

            if ($status === 200) {
                $estates = [];

                foreach ($data['data'] as $estateData) {
                    $estates[] = Estate::from($estateData);
                }

                return $estates;
            } else {
                $error = 'Unknown error';
                $code = null;

                if (isset($data['error']) && isset($data['error']['message'])) {
                    $error = $data['error']['message'];

                    if (isset($data['error']['code'])) {
                        $code = $data['error']['code'];
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

    public function synchronize()
    {
		$domos = DOMOS::instance();

        $estates = $this->fetchEstates();

		$cities = array_map(function (Estate $estate) {
			return $estate->address->city;
		}, $estates);

		$usages = [];

		foreach ($estates as $estate) {
			foreach ($estate->buildings as $building) {
				foreach ($building->rentables as $rentable) {
					foreach ($rentable->spaces as $space) {
						$usages[] = $space->type->slug;
					}
				}
			}
		}

		$domos->options->cities->set($cities);
		$domos->options->usages->set($usages);

        $created = 0;
        $deleted = 0;
        $updated = 0;

        foreach ($estates as $estate) {
            $existingPost = EstatePost::find($estate->id);

            if ($existingPost) {
                EstatePost::update($estate->id, $estate);
                $updated++;
            } else {
                EstatePost::create($estate->id, $estate);
                $created++;
            }
        }

        $ids = array_map(function (Estate $estate) {
            return $estate->id;
        }, $estates);

        $postsToDelete = EstatePost::findUnneeded($ids);

        foreach ($postsToDelete as $post) {
            wp_delete_post($post->id, true);
            $deleted++;
        }

        return [$created, $updated, $deleted];
    }
}
