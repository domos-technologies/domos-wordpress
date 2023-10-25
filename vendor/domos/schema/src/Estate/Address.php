<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Sanitizer;

class Address implements Arrayable
{
    public string $street;
    public string $number;
    public string $postal_code;
    public string $city;
    public ?string $country = null;

	public ?string $label = null;

    public ?Coordinates $coordinates = null;

    public static function from(array $data): self
    {
        $address = new self;
        $address->street = $data['street'];
        $address->number = $data['number'];
        $address->postal_code = $data['postal_code'];
        $address->city = $data['city'];
        $address->country = Sanitizer::nullify_string($data['country'] ?? null);
		$address->label = Sanitizer::nullify_string($data['label'] ?? null);

        $address->coordinates = isset($data['coordinates']) && $data['coordinates']
			? Coordinates::from($data['coordinates'])
			: null;

        return $address;
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
