<?php

namespace Tests\Unit\Estate\Location;

use SchemaImmo\Estate\Location\Place\Type;

it('can be created from a string', function ($value) {
    $type = Type::from($value);
    expect($type->value)->toBe($value);
})->with(['public-transport', 'subway-station']);