<?php

namespace Tests\Unit;

use SchemaImmo\Contact;
use SchemaImmo\Estate\Address;
use SchemaImmo\Image;
use Tests\EncodeDecode;
use function Tests\fakeContact;



it('decodes and encodes (complete)', function () {
    $data = fakeContact();

    $result = EncodeDecode::encodeAndDecode($data, Contact::class);

    expect($result->instance)
        ->toBeInstanceOf(Contact::class)
        ->toHaveProperty('name', $data['name'])
        ->toHaveProperty('role', $data['role'])
        ->toHaveProperty('email', $data['email'])
        ->toHaveProperty('phone', $data['phone'])
        ->toHaveProperty('mobile', $data['mobile'])
        ->toHaveProperty('avatar', Image::from($data['avatar']))
        ->toHaveProperty('address', Address::from($data['address']));

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'name' => $data['name'],
            'role' => $data['role'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'mobile' => $data['mobile'],
            'avatar' => $data['avatar'],
            'address' => $data['address']
        ]);

    testDecodedSameAsEncoded($result, Contact::class);
});

it('decodes and encodes (just name)', function () {
    $data = fakeContact([
        'name' => fake()->name(),
        'role' => null,
        'email' => null,
        'phone' => null,
        'mobile' => null,
        'avatar' => null,
        'address' => null
    ]);

    $result = EncodeDecode::encodeAndDecode($data, Contact::class);

    expect($result->instance)
        ->toBeInstanceOf(Contact::class)
        ->toHaveProperty('name', $data['name'])
        ->toHaveProperty('role', null)
        ->toHaveProperty('email', null)
        ->toHaveProperty('phone', null)
        ->toHaveProperty('mobile', null)
        ->toHaveProperty('avatar', null)
        ->toHaveProperty('address', null);

    expect($result->encoded)
        ->toBeArray()
        ->toEqual([
            'name' => $data['name'],
            'role' => null,
            'email' => null,
            'phone' => null,
            'mobile' => null,
            'avatar' => null,
            'address' => null
        ]);

    testDecodedSameAsEncoded($result, Contact::class);
});

it('cannot be created without a name', function ($key, $message) {
    $data = fakeContact();
    unset($data[$key]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        "contact.{$key}",
        $message
    );
})->with([
    ['name', 'Missing name'],
]);

it('cannot be created with an invalid name', function ($name, $message) {
    $data = fakeContact([
        'name' => $name,
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        'contact.name',
        $message
    );
})->with([
    [null, 'Missing name'],
    ['', 'Name cannot be empty'],
    [fake()->numberBetween(), 'Name must be a string'],
    [fake()->boolean(), 'Name must be a string'],
]);

it('cannot be created with an invalid role', function ($role, $message) {
    $data = fakeContact([
        'role' => $role,
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        'contact.role',
        $message
    );
})->with([
    [fake()->numberBetween(), 'Role must be a string'],
    [fake()->boolean(), 'Role must be a string'],
]);

it('cannot be created with an invalid email', function ($email, $message) {
    $data = fakeContact([
        'email' => $email,
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        'contact.email',
        $message
    );
})->with([
    [fake()->numberBetween(), 'Email must be a string'],
    [fake()->boolean(), 'Email must be a string'],
]);

it('cannot be created with an invalid phone', function ($phone, $message) {
    $data = fakeContact([
        'phone' => $phone,
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        'contact.phone',
        $message
    );
})->with([
    [fake()->numberBetween(), 'Phone must be a string'],
    [fake()->boolean(), 'Phone must be a string'],
]);

it('cannot be created with an invalid mobile', function ($mobile, $message) {
    $data = fakeContact([
        'mobile' => $mobile,
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        'contact.mobile',
        $message
    );
})->with([
    [fake()->numberBetween(), 'Mobile must be a string'],
    [fake()->boolean(), 'Mobile must be a string'],
]);

it('avatar', function ($avatar, $message) {
    $data = fakeContact([
        'avatar' => $avatar,
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        'contact.avatar',
        $message
    );
})->with([
    [fake()->numberBetween(), 'Avatar must be an array'],
    [fake()->boolean(), 'Avatar must be an array'],
]);

it('cannot be created with an invalid address', function ($address, $message) {
    $data = fakeContact([
        'address' => $address,
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        'contact.address',
        $message
    );
})->with([
    [fake()->numberBetween(), 'Address must be an array'],
    [fake()->boolean(), 'Address must be an array'],
]);

it('cannot be created with an invalid property', function ($property, $message, $value) {
    $data = fakeContact([
        $property => $value
    ]);

    expectInvalidDataException(
        fn() => EncodeDecode::encodeAndDecode($data, Contact::class),
        "contact.{$property}",
        $message
    );
})->with([
    ['name', 'Name must be a string', fake()->numberBetween()],
    ['role', 'Role must be a string', fake()->numberBetween()],
    ['email', 'Email must be a string', fake()->numberBetween()],
    ['phone', 'Phone must be a string', fake()->numberBetween()],
    ['mobile', 'Mobile must be a string', fake()->numberBetween()],
    ['avatar', 'Avatar must be an array', fake()->numberBetween()],
    ['address', 'Address must be an array', fake()->numberBetween()],
]);

it('empty property is sanitized to null', function ($property) {
    $data = fakeContact([
        $property => '',
    ]);

    $result = EncodeDecode::encodeAndDecode($data, Contact::class);

    expect($result->instance)
        ->toBeInstanceOf(Contact::class)
        ->toHaveProperty($property, null);

    expect($result->encoded[$property])
        ->toBeNull();

    testDecodedSameAsEncoded($result, Contact::class);
})->with([
    'role',
    'email',
    'phone',
    'mobile',
]);