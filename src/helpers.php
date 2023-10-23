<?php

if (! function_exists('domos_plugin_url')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $abstract
     * @param  array  $parameters
     * @return mixed|\Illuminate\Contracts\Foundation\Application
     */
    function domos_plugin_url(?string $path = null): string
    {
        // ensure $path does not start with a slash
        $path = ltrim($path, '/');

        return DOMOS_CORE_URL . $path;
    }
}
