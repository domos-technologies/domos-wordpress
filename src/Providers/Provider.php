<?php

namespace Domos\Core\Providers;

interface Provider
{
    public function register();
    public function boot();
}
