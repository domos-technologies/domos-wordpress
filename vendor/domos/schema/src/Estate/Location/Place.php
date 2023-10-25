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

    public static function from(array $data): self
    {
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
