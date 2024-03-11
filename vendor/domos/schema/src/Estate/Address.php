<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Exceptions\InvalidDataException;
use SchemaImmo\Exceptions\ValidationException;
use SchemaImmo\Sanitizer;

class Address implements Arrayable
{
    public string $street;
    public string $number;
    public string $postal_code;
    public string $city;
    public ?string $country = null;
    public ?Coordinates $coordinates = null;
    public ?string $label = null;

    public function __construct(
        string $street,
        string $number,
        string $postal_code,
        string $city,
        ?string $country = null,
        ?Coordinates $coordinates = null,
        ?string $label = null,
    )
    {
        $this->street = $street;
        $this->number = $number;
        $this->postal_code = $postal_code;
        $this->city = $city;
        $this->country = $country;
        $this->coordinates = $coordinates;
        $this->label = $label;
    }

    public static function from(array $data): self
    {
        $isset = fn (string $key) => isset($data[$key]);
        $isEmptyString = fn (string $key) => $isset($key) && $data[$key] === '';

        $exception = match (true) {
            // Required fields
            !$isset('street') => new InvalidDataException('street', 'Missing street'),
            !$isset('number') => new InvalidDataException('number', 'Missing street number'),
            !$isset('postal_code') => new InvalidDataException('postal_code', 'Missing postal code'),
            !$isset('city') => new InvalidDataException('city', 'Missing city'),

            // Data must be strings
            $isset('street') && !is_string($data['street']) => new InvalidDataException('street', 'Street must be a string'),
            $isset('number') && !is_string($data['number']) => new InvalidDataException('number', 'Street number must be a string'),
            $isset('postal_code') && !is_string($data['postal_code']) => new InvalidDataException('postal_code', 'Postal code must be a string'),
            $isset('city') && !is_string($data['city']) => new InvalidDataException('city', 'City must be a string'),
            $isset('country') && !is_string($data['country']) => new InvalidDataException('country', 'Country must be a string'),
            $isset('label') && !is_string($data['label']) => new InvalidDataException('label', 'Label must be a string'),

            // Data cannot be empty strings
            $isset('street') && $isEmptyString('street') => new InvalidDataException('street', 'Street cannot be empty'),
            $isset('number') && $isEmptyString('number') => new InvalidDataException('number', 'Street number cannot be empty'),
            $isset('postal_code') && $isEmptyString('postal_code') => new InvalidDataException('postal_code', 'Postal code cannot be empty'),
            $isset('city') && $isEmptyString('city') => new InvalidDataException('city', 'City cannot be empty'),
            $isset('country') && $isEmptyString('country') => new InvalidDataException('country', 'Country cannot be empty'),
            $isset('label') && $isEmptyString('label') => new InvalidDataException('label', 'Label cannot be empty'),

            default => null,
        };

        if ($exception) {
            throw $exception;
        }

        return new self(
            street: $data['street'],
            number: $data['number'],
            postal_code: $data['postal_code'],
            city: $data['city'],
            country: Sanitizer::nullify_string($data['country'] ?? null),
            coordinates: isset($data['coordinates'])
                ? Coordinates::from($data['coordinates'])
                : null,
            label: Sanitizer::nullify_string($data['label'] ?? null),
        );
    }

    public function toArray(): array
    {
        $data = [
            'street' => $this->street,
            'number' => $this->number,
            'postal_code' => $this->postal_code,
            'city' => $this->city
        ];

		if ($this->country) {
			$data['country'] = $this->country;
		}

		if ($this->label) {
			$data['label'] = $this->label;
		}

		if ($this->coordinates) {
			$data['coordinates'] = $this->coordinates->toArray();
		}

		return $data;
    }
}
