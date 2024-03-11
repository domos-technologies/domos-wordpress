<?php

namespace Tests;

/**
 * @template T
 */
class EncodeDecode
{
    /**
     * @param T $instance
     * @param array $encoded
     * @param string $json
     * @param array $decoded
     * @param T $decodedInstance
     */
    public function __construct(
        public $instance,
        public $encoded,
        public $json,
        public $decoded,
        public $decodedInstance,
    ) {}

    /**
     * @param array $data
     * @param class-string<T> $class
     *
     * @return self<T>
     * @template T
     */
    public static function encodeAndDecode(array $data, string $class): self
    {
        $instance = $class::from($data);
        $encoded = $instance->toArray();
        $json = json_encode($encoded);
        $decoded = json_decode($json, true);
        $decodedInstance = $class::from($decoded);

        return new self(
            instance: $instance,
            encoded: $encoded,
            json: $json,
            decoded: $decoded,
            decodedInstance: $decodedInstance,
        );
    }
}