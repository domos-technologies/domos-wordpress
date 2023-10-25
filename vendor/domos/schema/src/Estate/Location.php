<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Estate\Location\Place;

class Location implements Arrayable
{
	/**
	 * @var Place[]
	 */
	public array $places = [];

	public static function from(array $data): self
	{
		$location = new self;

		foreach ($data['places'] as $place) {
			$location->places[] = Place::from($place);
		}

		return $location;
	}

	public function toArray(): array
	{
		return [
            'places' => array_map(fn (Place $place) => $place->toArray(), $this->places),
        ];
	}
}
