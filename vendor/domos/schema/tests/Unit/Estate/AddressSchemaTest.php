<?php

namespace Tests\Unit\Estate;

use SchemaImmo\Estate;
use SchemaImmo\Exceptions\InvalidDataException;
use Tests\EncodeDecode;
use function Tests\fakeAddress;


function testEncodeAndDecodeAddress(array $data)
{
    $result = EncodeDecode::encodeAndDecode($data, Estate\Address::class);

    expect($result->instance)
        ->toBeInstanceOf(Estate\Address::class)
        ->toHaveProperty('street', $data['street'])
        ->toHaveProperty('number', $data['number'])
        ->toHaveProperty('postal_code', $data['postal_code'])
        ->toHaveProperty('city', $data['city'])
        ->toHaveProperty('country', $data['country'])
        ->toHaveProperty('coordinates', $data['coordinates']
            ? Estate\Coordinates::from($data['coordinates'])
            : null
        )
        ->toHaveProperty('label', $data['label']);

    expect($result->json)
        ->toBeString();

    expect($result->encoded)
        ->toBeArray()
        ->toEqual(array_filter($data));

    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf(Estate\Address::class)
        ->toEqual($result->instance);
}



it('can encode and decode itself (complete)', function () {
    $data = fakeAddress();

    testEncodeAndDecodeAddress($data);
});

it('can encode and decode itself (without label)', function () {
    $data = fakeAddress([
        'label' => null,
    ]);

    testEncodeAndDecodeAddress($data);
});

it('can encode and decode itself (without coordinates)', function () {
    $data = fakeAddress([
        'coordinates' => null,
    ]);

    testEncodeAndDecodeAddress($data);
});

it('can encode and decode itself (without coordinates & label)', function () {
    $data = fakeAddress([
        'coordinates' => null,
        'label' => null,
    ]);

    testEncodeAndDecodeAddress($data);
});

it('can encode and decode itself (without country)', function () {
    $data = fakeAddress([
        'country' => null,
    ]);

    testEncodeAndDecodeAddress($data);
});

it('can encode and decode itself (without country & label)', function () {
    $data = fakeAddress([
        'country' => null,
        'label' => null,
    ]);

    testEncodeAndDecodeAddress($data);
});

it('can encode and decode itself (without country & coordinates)', function () {
    $data = fakeAddress([
        'country' => null,
        'coordinates' => null,
    ]);

    testEncodeAndDecodeAddress($data);
});

it ('fails to encode itself (without street)', function () {
    $data = fakeAddress([
        'street' => null,
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Missing street');

it ('fails to encode itself (empty street)', function () {
    $data = fakeAddress([
        'street' => '',
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Street cannot be empty');

it ('fails to encode itself (without number)', function () {
    $data = fakeAddress([
        'number' => null,
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Missing street number');

it ('fails to encode itself (empty number)', function () {
    $data = fakeAddress([
        'number' => '',
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Street number cannot be empty');

it ('fails to encode itself (without postal code)', function () {
    $data = fakeAddress([
        'postal_code' => null,
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Missing postal code');

it ('fails to encode itself (empty postal code)', function () {
    $data = fakeAddress([
        'postal_code' => '',
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Postal code cannot be empty');

it ('fails to encode itself (without city)', function () {
    $data = fakeAddress([
        'city' => null,
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Missing city');

it('fails to encode itself (empty city)', function () {
    $data = fakeAddress([
        'city' => '',
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'City cannot be empty');

it('fails to encode itself (label is not a string)', function () {
    $data = fakeAddress([
        'label' => fake()->numberBetween(),
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Label must be a string');

it('fails to encode itself (empty label)', function () {
    $data = fakeAddress([
        'label' => '',
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Label cannot be empty');

it('fails to encode itself (country is not a string)', function () {
    $data = fakeAddress([
        'country' => fake()->numberBetween(),
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Country must be a string');

it('fails to encode itself (empty country)', function () {
    $data = fakeAddress([
        'country' => '',
    ]);

    Estate\Address::from($data);
})->throws(InvalidDataException::class, 'Country cannot be empty');


//it('can encode and decode itself', function () {
//    $data = [
//        'id' => '123',
//        'slug' => '123',
//        'name' => fake()->name(),
//        'address' => [
//            'street' => fake()->fakeAddress(),
//            'number' => fake()->buildingNumber(),
//            'postal_code' => fake()->postcode(),
//            'city' => fake()->city(),
//            'country' => fake()->country(),
//            'coordinates' => [
//                'longitude' => fake()->longitude(),
//                'latitude' => fake()->latitude(),
//            ],
//            'label' => 'Adresse'
//        ],
//        'features' => [],
//        'buildings' => [],
//        'texts' => (new Estate\Texts)->toArray(),
//        'media' => (new Estate\Media)->toArray(),
//        'location' => (new Estate\Location)->toArray(),
//        'certifications' => (new Estate\Certifications)->toArray(),
//        'social' => (new Estate\Social)->toArray(),
//        'expose' => null,
//    ];
//
//    $estate = new Estate(
//        id: '123',
//        slug: '123',
//        name: $data['name'],
//        address: Estate\Address::from($data['address']),
//        features: $data['features'],
//        buildings: $data['buildings'],
//        texts: Estate\Texts::from($data['texts']),
//        media: Estate\Media::from($data['media']),
//        location: Estate\Location::from($data['location']),
//        certifications: Estate\Certifications::from($data['certifications']),
//        social: Estate\Social::from($data['social']),
//        expose: $data['expose'],
//    );
//
//    expect($estate)
//        ->toBeInstanceOf(Estate::class)
//        ->toHaveProperty('id', '123')
//        ->toHaveProperty('slug', '123')
//        ->toHaveProperty('name', $data['name'])
//        ->toHaveProperty('address', new Estate\Address(
//            street: $data['address']['street'],
//            number: $data['address']['number'],
//            postal_code: $data['address']['postal_code'],
//            city: $data['address']['city'],
//            country: $data['address']['country'],
//            coordinates: new Estate\Coordinates(
//                latitude: $data['address']['coordinates']['latitude'],
//                longitude: $data['address']['coordinates']['longitude'],
//            ),
//            label: $data['address']['label'],
//        ))
//        ->toHaveProperty('features', $data['features'])
//        ->toHaveProperty('buildings', $data['buildings'])
//        ->toHaveProperty('texts', $data['texts'])
//        ->toHaveProperty('media', $data['media'])
//        ->toHaveProperty('location', $data['location'])
//        ->toHaveProperty('certifications', $data['certifications'])
//        ->toHaveProperty('social', $data['social'])
//        ->toHaveProperty('expose', $data['expose']);
//
//
//    $json = json_encode($estate);
//
//    expect($json)
//        ->toBeString();
//
//    $decoded = json_decode($json, true);
//    $parsedEstate = Estate::from($decoded);
//
//    expect($parsedEstate)
//        ->toBeInstanceOf(Estate::class)
//        ->toEqual($estate);
//});