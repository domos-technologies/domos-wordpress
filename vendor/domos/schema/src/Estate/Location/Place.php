<?php

namespace SchemaImmo\Estate\Location;

use SchemaImmo\Arrayable;
use SchemaImmo\Estate\Address;
use SchemaImmo\Estate\Coordinates;

class Place implements \SchemaImmo\Arrayable
{
	/** @var string|null */
	public $id = null;
	public Place\Type $type;
	public string $name;
	public Coordinates $coordinates;
	public ?Address $address = null;

	public ?Directions $directions_from_estate = null;

    public static function tryFrom(array $data): ?self
    {
        try {
            return self::from($data);
        } catch (\SchemaImmo\Exceptions\InvalidDataException $e) {
            return null;
        }
    }

    public static function from(array $data): self
    {
        if (!isset($data['type'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('place.type', 'Missing type');
        }

        if (!Place\Type::tryFrom($data['type'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('place.type', "Invalid type '{$data['type']}'");
        }

        if (!isset($data['name'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('place.name', 'Missing name');
        }

        if (!isset($data['coordinates'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('place.coordinates', 'Missing coordinates');
        }

        if (!is_array($data['coordinates'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('place.coordinates', 'Coordinates must be an array');
        }

        if (isset($data['address']) && !is_array($data['address'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('place.address', 'Address must be an array');
        }

        if (isset($data['directions_from_estate']) && !is_array($data['directions_from_estate'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('place.directions_from_estate', 'Directions must be an array');
        }

		$place = new self;
		$place->id = $data['id'] ?? null;
		$place->type = Place\Type::from($data['type']);
		$place->name = $data['name'];
		$place->coordinates = Coordinates::from($data['coordinates']);

		if (isset($data['address'])) {
			$place->address = Address::from($data['address']);
		}

		if (isset($data['directions_from_estate'])) {
			$place->directions_from_estate = Directions::from($data['directions_from_estate']);
		}

		return $place;
    }

    public function toArray(): array
    {
        return [
			'id' => $this->id,
			'type' => $this->type->value,
			'name' => $this->name,
			'coordinates' => $this->coordinates->toArray(),
			'address' => $this->address ? $this->address->toArray() : null,
			'directions_from_estate' => $this->directions_from_estate ? $this->directions_from_estate->toArray() : null,
		];
    }
}
