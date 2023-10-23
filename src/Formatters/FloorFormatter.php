<?php

namespace Domos\Core\Formatters;

class FloorFormatter
{
    public static function text(int|string|null $floor): ?string
    {
        $intFloor = intval($floor);

        if ($floor === null) {
            return null;
        } else if ($intFloor === 0) {
            return 'EG';
        } else if ($intFloor < 0) {
            return abs($intFloor) . '. UG';
        } else {
            return $intFloor . '. OG';
        }
    }
}
