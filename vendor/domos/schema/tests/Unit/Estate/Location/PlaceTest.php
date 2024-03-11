<?php

namespace Tests\Unit\Estate\Location;

use SchemaImmo\Estate\Location\Place;
use function Tests\fakeAddress;
use function Tests\fakeCoordinates;
use function Tests\fakeDirections;
use function Tests\fakePlace;

it('can be created from and converted to an array (complete)', function () {
    $data = [
        'id'               => fake()->uuid(),
        'type'             => 'restaurant',
        'name'             => fake()->company(),
        'coordinates'      => fakeCoordinates(),
        'address'          => fakeAddress(),
        'directions_from_estate' => fakeDirections(),
    ];

    $place = Place::from($data);

    expect($place)->toBeInstanceOf(Place::class);
    expect($place->toArray())
        ->toEqual([
            'id'               => $data['id'],
            'type'             => $data['type'],
            'name'             => $data['name'],
            'coordinates'      => $data['coordinates'],
            'address'          => $data['address'],
            'directions_from_estate' => $data['directions_from_estate'],
        ]);
});


it('can be created from and converted to an array (without address)', function () {
    $data = [
        'id'               => fake()->uuid(),
        'type'             => 'restaurant',
        'name'             => fake()->company(),
        'coordinates'      => fakeCoordinates(),
    ];

    $place = Place::from($data);

    expect($place)->toBeInstanceOf(Place::class);
    expect($place->toArray())
        ->toEqual([
            'id'               => $data['id'],
            'type'             => $data['type'],
            'name'             => $data['name'],
            'coordinates'      => $data['coordinates'],
            'address'          => null,
            'directions_from_estate' => null,
        ]);
});

it('throws an exception for missing required properties', function ($property, $message) {
    $data = fakePlace();
    unset($data[$property]);

    expectInvalidDataException(fn() => Place::from($data), "place.{$property}", $message);
})->with([
    ['type', 'Missing type'],
    ['name', 'Missing name'],
    ['coordinates', 'Missing coordinates'],
]);

it('throws for invalid properties', function ($property, $data, $message) {
    $data = fakePlace($data);

    expectInvalidDataException(fn() => Place::from($data), "place.{$property}", $message);
})->with([
    ['type', ['type' => 'asd'], 'Invalid type \'asd\''],
    ['coordinates', ['coordinates' => 'asd'], 'Coordinates must be an array'],
    ['address', ['address' => 'asd'], 'Address must be an array'],
    ['directions_from_estate', ['directions_from_estate' => 'asd'], 'Directions must be an array'],
]);