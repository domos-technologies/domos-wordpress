<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Building;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;
use SchemaImmo\WebExpose\PreformattedFeature;

class BuildingBlock extends Block
{
	public Building $building;

	/** @var array<string, string> */
	public array $facts = [];

	/** @var array<string, PreformattedFeature> */
	public array $features = [];

	public function __construct(
		?Building $building = null,
		array $facts = [],
		array $features = [],
		?string $id = null,
	)
	{
		parent::__construct(
			BlockType::Building,
			$id,
		);

		if ($building) {
			$this->building = $building;
		}

		$this->facts = $facts;
		$this->features = $features;
	}

	public function fill(array $data): static
	{
		parent::fill($data);

		$this->building = Building::from($data['building']);
		$this->facts = $data['facts'] ?? [];
		$this->features = array_map(
			fn (array $feature) => PreformattedFeature::from($feature),
			$data['features'] ?? []
		);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'building' => $this->building->toArray(),
			'facts' => $this->facts,
			'features' => array_map(
				fn(PreformattedFeature $feature) => $feature->toArray(),
				$this->features
			),
		];
	}
}
