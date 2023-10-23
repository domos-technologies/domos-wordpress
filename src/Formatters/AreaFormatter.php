<?php

namespace Domos\Core\Formatters;

class AreaFormatter
{
    public static function number(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    public static function text(float $value, int $decimals = 1): string
    {
        return static::number($value, decimals: $decimals) . ' m²';
    }
}
