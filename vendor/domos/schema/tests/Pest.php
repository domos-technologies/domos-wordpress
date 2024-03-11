<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function fake() {
    return \Faker\Factory::create();
}

function expectInvalidDataException(\Closure $parse, string $property, string $message)
{
    $exception = null;

    try {
        $parse();
    } catch (\Exception $e) {
        expect($e)
            ->toBeInstanceOf(\SchemaImmo\Exceptions\InvalidDataException::class)
            ->toHaveProperty('key', $property);

        expect($e->getMessage())->toBe($message);

        $exception = $e;
    }

    expect($exception)->not->toBeNull('Expected an exception to be thrown');
}

/**
 * @param \Tests\EncodeDecode $result
 * @param class-string $class
 */
function testDecodedSameAsEncoded(\Tests\EncodeDecode $result, string $class)
{
    expect($result->decoded)
        ->toBeArray()
        ->toEqual($result->encoded);

    expect($result->decodedInstance)
        ->toBeInstanceOf($class)
        ->toEqual($result->instance);
}