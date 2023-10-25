<?php

namespace SchemaImmo\Estate\Location;

use SchemaImmo\Arrayable;

class Route implements Arrayable
{
	/**
	 * @var float Duration in minutes
	 */
	public float $duration;

	/**
	 * @var float Distance in kilometers
	 */
	public float $distance;

	public static function from(array $data): self
	{
		$route = new self;
		$route->duration = $data['duration'];
		$route->distance = $data['distance'];

		return $route;
	}

	public function toArray(): array
	{
		return [
			'duration' => $this->duration,
			'distance' => $this->distance
		];
	}
}
