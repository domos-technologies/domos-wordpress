<?php

namespace Tests;

function fakeAddress(array $more = []): array
{
    return [
        ...[
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'coordinates' => fakeCoordinates(),
            'label' => 'Adresse'
        ],
        ...$more
    ];
}

function fakeCoordinates(array $more = []): array
{
    return [
        ...[
            'longitude' => fake()->longitude(),
            'latitude' => fake()->latitude(),
        ],
        ...$more
    ];
}

function fakePlace(array $more = []): array
{
    return [
        ...[
            'id' => fake()->uuid(),
            'type' => 'restaurant',
            'name' => fake()->name(),
            'coordinates' => fakeCoordinates(),
            'address' => fakeAddress(),
            'directions_from_estate' => null,
        ],
        ...$more
    ];
}

function fakeDirections(array $more = []): array
{
    return [
        ...[
            'from' => fakeCoordinates(),
            'to'   => fakeCoordinates(),
            'distance_air' => (float) fake()->numberBetween(1, 1000),
            'walking' => [
                'duration' => (float) fake()->numberBetween(1, 1000),
                'distance' => (float) fake()->numberBetween(1, 1000),
            ],
            'public_transport' => [
                'duration' => (float) fake()->numberBetween(1, 1000),
                'distance' => (float) fake()->numberBetween(1, 1000),
            ],
            'driving' => [
                'duration' => (float) fake()->numberBetween(1, 1000),
                'distance' => (float) fake()->numberBetween(1, 1000),
            ],
            'cycling' => [
                'duration' => (float) fake()->numberBetween(1, 1000),
                'distance' => (float) fake()->numberBetween(1, 1000),
            ],
        ],
    ...$more
    ];
}

function fakeImage(array $more = []): array
{
    return [
        ...[
            'src' => fake()->url(),
            'alt' => fake()->sentence(),
        ],
        ...$more
    ];
}

function fakeContact(array $more = []): array
{
    return [
        ...[
            'name' => fake()->name(),
            'role' => fake()->jobTitle(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'avatar' => fakeImage(),
            'address' => fakeAddress()
        ],
        ...$more
    ];
}

