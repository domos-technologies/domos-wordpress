<?php

namespace Domos\Core\Support;

class View
{
    public static function render(string $view, array $data = []): string
    {
        $path = DOMOS_CORE_ROOT . '/resources/views/' . $view . '.php';

        ob_start();

        // create data keys as variables
        foreach ($data as $key => $value) {
            $$key = $value;
        }

        include $path;

        return ob_get_clean();
    }
}
