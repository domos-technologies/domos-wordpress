<?php

use OzdemirBurak\Iris\Color\Hex;

if (! function_exists('domos_hex_to_oklch')) {
    /**
     * Convert a HEX color string to an OKLCH CSS color string.
     *
     * This matches Tailwind CSS v4's native color format so that CSS custom
     * properties can be used directly as color values (e.g. text-primary-600).
     */
    function domos_hex_to_oklch(?string $hex): string
    {
        if (empty($hex)) {
            return 'oklch(0% 0 0)';
        }

        try {
            return (string) (new Hex($hex))->toOklch();
        } catch (Throwable $e) {
            return $hex;
        }
    }
}

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

        return DOMOS_CORE_URL.$path;
    }
}
