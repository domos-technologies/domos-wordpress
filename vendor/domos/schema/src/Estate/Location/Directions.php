<?php

namespace SchemaImmo\Estate\Location;

use SchemaImmo\Arrayable;
use SchemaImmo\Estate\Coordinates;

class Directions implements Arrayable
{
	public Coordinates $from;
	public Coordinates $to;

	public float $distance_air;

	public ?Route $walking = null;
	public ?Route $cycling = null;
	public ?Route $driving = null;
	public ?Route $public_transport = null;

	public static function from(array $data): self
	{
		$directions = new self;
		$directions->from = Coordinates::from($data['from']);
		$directions->to = Coordinates::from($data['to']);
		$directions->distance_air = $data['distance_air'];

		if (isset($data['walking'])) {
			$directions->walking = Route::from($data['walking']);
		}

		if (isset($data['cycling'])) {
			$directions->cycling = Route::from($data['cycling']);
		}

		if (isset($data['driving'])) {
			$directions->driving = Route::from($data['driving']);
		}

		if (isset($data['public_transport'])) {
			$directions->public_transport = Route::from($data['public_transport']);
		}

		return $directions;
	}

	public function toArray(): array
	{
		return [
			'from' => $this->from->toArray(),
			'to' => $this->to->toArray(),
			'distance_air' => $this->distance_air,
			'walking' => $this->walking
				? $this->walking->toArray()
				: null,
			'cycling' => $this->cycling
				? $this->cycling->toArray()
				: null,
			'driving' => $this->driving
				? $this->driving->toArray()
				: null,
			'public_transport' => $this->public_transport
				? $this->public_transport->toArray()
				: null,
		];
	}
}
