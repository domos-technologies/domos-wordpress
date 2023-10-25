<?php

namespace SchemaImmo;

use SchemaImmo\Estate\Address;
use SchemaImmo\Building\Media;

class Building implements Arrayable
{
	public string $id;
	public ?string $name = null;
	public ?Address $address = null;
	public ?float $area = null;

	public Media $media;

	public array $features = [];

	/** @var Rentable[] $rentables */
	public array $rentables = [];

	public function __construct(
		string $id,
		?string $name = null,
		?Address $address = null,
		?float $area = null,
		Media $media = new Media,

		/** @var array<string, bool|array> $features */
		array $features = [],

		/** @var Rentable[] $rentables */
		array $rentables = []
	)
	{
		$this->id = $id;
		$this->name = $name;
		$this->address = $address;
		$this->area = $area;
		$this->media = $media;
		$this->features = $features;
		$this->rentables = $rentables;
	}

	public static function from(array $data): self
	{
		$building = new self(
			Sanitizer::nullify_string($data['id'] ?? null)
		);
		$building->name = $data['name'];
		$building->address = isset($data['address'])
			? Address::from($data['address'])
			: null;
		$building->area = $data['area'] ?? null;

		$building->media = isset($data['media'])
			? Media::from($data['media'])
			: new Media;

		$building->features = $data['features'] ?? [];

		$building->rentables = array_map(
			fn (array $rentable) => Rentable::from($rentable),
			$data['rentables'] ?? []
		);

		return $building;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'address' => $this->address ? $this->address->toArray() : null,
			'area' => $this->area,
			'media' => $this->media->toArray(),
			'features' => $this->features,
			'rentables' => array_map(
				fn (Rentable $rentable) => $rentable->toArray(),
				$this->rentables
			),
		];
	}

	public function feature(string $type, ?string $key = null)
	{
		if (!isset($this->features[$type])) {
			return null;
		}

		if ($key) {
			return $this->features[$type][$key] ?? null;
		}

		return $this->features[$type];
	}
}
