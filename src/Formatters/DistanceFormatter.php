<?php

namespace Domos\Core\Formatters;

use SchemaImmo\Estate\Address;

class DistanceFormatter
{
    public static function number(float $kilometers, int $decimals = 1): string
    {
		return number_format($kilometers, $decimals, ',', '.');
    }

    public static function text(float $distance_km, int $decimals = 1, Unit $unit = Unit::Kilometer): string
    {
        return static::number($distance_km, $decimals) . ' km';
    }
}
