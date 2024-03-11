<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Exceptions\InvalidDataException;

class Coordinates implements Arrayable
{
    public float $latitude;
    public float $longitude;

    public function __construct(
        float $latitude,
        float $longitude,
    )
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function from(array $data): self
    {
        if (!isset($data['latitude'])) {
            throw new InvalidDataException('coordinates.latitude', 'Missing latitude');
        }

        if (!isset($data['longitude'])) {
            throw new InvalidDataException('coordinates.longitude', 'Missing longitude');
        }

        if (!is_numeric($data['latitude'])) {
            throw new InvalidDataException('coordinates.latitude', 'Latitude must be a float');
        }

        if (!is_numeric($data['longitude'])) {
            throw new InvalidDataException('coordinates.longitude', 'Longitude must be a float');
        }

        // Bigger than 90, smaller than -90
        if ($data['latitude'] > 90 || $data['latitude'] < -90) {
            throw new InvalidDataException('coordinates.latitude', 'Latitude must be between -90 and 90');
        }

        // Bigger than 180, smaller than -180
        if ($data['longitude'] > 180 || $data['longitude'] < -180) {
            throw new InvalidDataException('coordinates.longitude', 'Longitude must be between -180 and 180');
        }

        $coordinates = new self(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
        );

        return $coordinates;
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }
}
