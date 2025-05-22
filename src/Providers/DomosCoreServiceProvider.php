<?php

namespace Domos\Core\Providers;

use Domos\Core\EstatePost;
use Roots\Acorn\Application;

class DomosCoreServiceProvider implements Provider
{
    protected $services = [];

    protected function providers()
    {
        return [
            PostServiceProvider::class,
            ApiServiceProvider::class,
            BlockServiceProvider::class,
            AdminServiceProvider::class,
            FrontendServiceProvider::class,
	        UpdateServiceProvider::class,
	        CronServiceProvider::class
        ];
    }

    public function register()
    {
		$app = Application::configure()->boot();
		$app->useNamespace('Domos\\Core\\');
		$app['view']->addNamespace('adler', __DIR__ . '/../../resources/views/frontend/adler');

        foreach ($this->providers() as $service) {
            $instance = new $service;
            $this->services[$service] = $instance;

            $instance->register();
        }
    }

    public function boot()
    {
        foreach ($this->providers() as $service) {
            $instance = $this->services[$service];

            $instance->boot();
        }
    }
}
