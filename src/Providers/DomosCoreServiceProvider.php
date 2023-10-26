<?php

namespace Domos\Core\Providers;

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
        \Roots\bootloader()->boot();

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
