<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;

class Coordinates implements Arrayable
{
    public float $latitude;
    public float $longitude;

    public static function from(array $data): self
    {
        $coordinates = new self;
        $coordinates->latitude = $data['latitude'];
        $coordinates->longitude = $data['longitude'];

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
