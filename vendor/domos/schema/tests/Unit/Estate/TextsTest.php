<?php

namespace Tests\Unit\Estate;

use SchemaImmo\Estate\Texts;
use Tests\EncodeDecode;

it('can encode and decode itself (complete)', function () {
    $data = [
        'slogan' => fake()->sentence(),
        'description' => fake()->paragraph(),
        'location_text' => fake()->paragraph()
    ];
    $result = EncodeDecode::encodeAndDecode($data, Texts::class);

     expect($result->instance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', $data['slogan'])
        ->toHaveProperty('description', $data['description'])
        ->toHaveProperty('location_text', $data['location_text']);

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'slogan' => $data['slogan'],
            'description' => $data['description'],
            'location_text' => $data['location_text'],
        ]);

    expect($result->json)
        ->toBeString()
        ->toEqual(json_encode($result->encoded));

    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', $data['slogan'])
        ->toHaveProperty('description', $data['description'])
        ->toHaveProperty('location_text', $data['location_text']);
});

it('can encode and decode itself (empty)', function () {
    $data = [];
    $result = EncodeDecode::encodeAndDecode($data, Texts::class);

     expect($result->instance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', null)
        ->toHaveProperty('description', null)
        ->toHaveProperty('location_text', null);

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'slogan' => null,
            'description' => null,
            'location_text' => null,
        ]);

    expect($result->json)
        ->toBeString()
        ->toEqual(json_encode($result->encoded));

    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', null)
        ->toHaveProperty('description', null)
        ->toHaveProperty('location_text', null);
});

it('can encode and decode itself (just slogan)', function () {
    $data = [
        'slogan' => fake()->text(),
    ];
    $result = EncodeDecode::encodeAndDecode($data, Texts::class);

     expect($result->instance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', $data['slogan'])
        ->toHaveProperty('description', null)
        ->toHaveProperty('location_text', null);

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'slogan' => $data['slogan'],
            'description' => null,
            'location_text' => null,
        ]);

    expect($result->json)
        ->toBeString()
        ->toEqual(json_encode($result->encoded));

    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', $data['slogan'])
        ->toHaveProperty('description', null)
        ->toHaveProperty('location_text', null);
});

it('can encode and decode itself (just description)', function () {
    $data = [
        'description' => fake()->text(),
    ];
    $result = EncodeDecode::encodeAndDecode($data, Texts::class);

     expect($result->instance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', null)
        ->toHaveProperty('description', $data['description'])
        ->toHaveProperty('location_text', null);

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'slogan' => null,
            'description' => $data['description'],
            'location_text' => null,
        ]);

    expect($result->json)
        ->toBeString()
        ->toEqual(json_encode($result->encoded));

    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', null)
        ->toHaveProperty('description', $data['description'])
        ->toHaveProperty('location_text', null);
});

it('can encode and decode itself (just location_text)', function () {
    $data = [
        'location_text' => fake()->text(),
    ];
    $result = EncodeDecode::encodeAndDecode($data, Texts::class);

     expect($result->instance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', null)
        ->toHaveProperty('description', null)
        ->toHaveProperty('location_text', $data['location_text']);

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'slogan' => null,
            'description' => null,
            'location_text' => $data['location_text'],
        ]);

    expect($result->json)
        ->toBeString()
        ->toEqual(json_encode($result->encoded));

    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf(Texts::class)
        ->toHaveProperty('slogan', null)
        ->toHaveProperty('description', null)
        ->toHaveProperty('location_text', $data['location_text']);
});