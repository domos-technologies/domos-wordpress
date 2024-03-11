<?php

namespace Tests\Unit\Estate;

use SchemaImmo\Estate\Coordinates;
use Tests\EncodeDecode;
use function Tests\fakeCoordinates;

it('can encode and decode itself (complete)', function () {
    $data = fakeCoordinates();
    $result = EncodeDecode::encodeAndDecode($data, Coordinates::class);

    expect($result->instance)
        ->toBeInstanceOf(Coordinates::class)
        ->toHaveProperty('latitude', $data['latitude'])
        ->toHaveProperty('longitude', $data['longitude']);

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

    expect($result->json)
        ->toBeString()
        ->toEqual(json_encode($result->encoded));

    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf(Coordinates::class)
        ->toHaveProperty('latitude', $data['latitude'])
        ->toHaveProperty('longitude', $data['longitude']);
});

it('cannot encode and decode itself (missing latitude)', function () {
    $data = fakeCoordinates([
        'latitude' => null,
    ]);

    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Missing latitude');

it('cannot encode and decode itself (missing longitude)', function () {
    $data = fakeCoordinates([
        'longitude' => null,
    ]);
    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Missing longitude');

it('cannot encode and decode itself (latitude is not a float)', function () {
    $data = fakeCoordinates([
        'latitude' => fake()->word(),
    ]);
    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Latitude must be a float');

it('cannot encode and decode itself (longitude is not a float)', function () {
    $data = fakeCoordinates([
        'longitude' => fake()->word(),
    ]);
    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Longitude must be a float');

it('cannot encode and decode itself (latitude is bigger than 90)', function () {
    $data = fakeCoordinates([
        'latitude' => 91,
    ]);

    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Latitude must be between -90 and 90');

it('cannot encode and decode itself (latitude is smaller than -90)', function () {
    $data = fakeCoordinates([
        'latitude' => -91,
    ]);

    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Latitude must be between -90 and 90');

it('cannot encode and decode itself (longitude is bigger than 180)', function () {
    $data = fakeCoordinates([
        'longitude' => 181,
    ]);

    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Longitude must be between -180 and 180');

it('cannot encode and decode itself (longitude is smaller than -180)', function () {
    $data = fakeCoordinates([
        'longitude' => -181,
    ]);
    EncodeDecode::encodeAndDecode($data, Coordinates::class);
})->throws(\SchemaImmo\Exceptions\InvalidDataException::class, 'Longitude must be between -180 and 180');