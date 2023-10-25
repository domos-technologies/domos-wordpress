<?php

namespace SchemaImmo\WebExpose\Block\FeaturesBlock;

use SchemaImmo\Arrayable;
use SchemaImmo\WebExpose\PreformattedFeature;

class FeatureGroup implements Arrayable
{
	public string $id;
	public string $label;

	/** @var array<string, PreformattedFeature> */
	public array $features = [];

	public function __construct(
		string $id,
		string $label,
		array $features = []
	)
	{
		$this->id = $id;
		$this->label = $label;
		$this->features = $features;
	}

	public static function from(array $data): self
	{
		$group = new self(
			$data['id'],
			$data['label'],
			array_map(
				fn (array $feature) => PreformattedFeature::from($feature),
				$data['features'] ?? []
			)
		);

		return $group;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'label' => $this->label,
			'features' => array_map(
				fn (PreformattedFeature $feature) => $feature->toArray(),
				$this->features
			)
		];
	}
}
