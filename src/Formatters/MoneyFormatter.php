<?php

namespace Domos\Core\Formatters;

class MoneyFormatter
{
    public static function number(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    public static function text(float $value, int $decimals = 2): string
    {
        return static::number($value, decimals: $decimals) . ' €';
    }
}
