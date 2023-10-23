<?php

namespace Domos\Core\Providers;

use Domos\Core\DOMOS;
use Domos\Core\Exceptions\CouldNotSync;

class ApiServiceProvider implements Provider
{
    public function register()
    {
        add_action('rest_api_init', function () {
            register_rest_route('domos/admin', '/sync', [
                'methods' => 'POST',
                'callback' => function () {
					return $this->sync();
				},
                'permission_callback' => function () {
                    // check if user can manage options
                    return current_user_can('manage_options');
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

	protected function sync()
	{
		try {
            [$created, $updated, $deleted] = DOMOS::instance()->sync->synchronize();

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

            return new \WP_Error('unknown',  $message, ['trace' => $th->getTrace()]);
        }
	}
}
