<?php

namespace Domos\Core\Formatters;

use SchemaImmo\Estate\Address;

class AddressFormatter
{
    public static function line(Address $address, bool $with_country = false): string
    {
        return sprintf(
            '%s %s, %s %s',
            $address->street,
            $address->number,
            $address->postal_code,
            $address->city
        ) . ($with_country ? ', ' . $address->country : '');
    }
}
