<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Sanitizer;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\Block\FeaturesBlock\FeatureGroup;
use SchemaImmo\WebExpose\BlockType;

class FeaturesBlock extends Block
{
	public ?string $heading = null;

	/** @var array<string, FeatureGroup> */
	public array $groups;

	/**
	 * @param array<string, FeatureGroup> $groups
	 * @param string|null $heading
	 */
	public function __construct(
		array $groups = [],
		?string $heading = null,
		?string $id = null,
	)
	{
		parent::__construct(BlockType::Features, $id);

		$this->groups = $groups;
		$this->heading = $heading;
	}

	public function fill(array $data): static
	{
		parent::fill($data);

		$this->heading = Sanitizer::nullify_string($data['heading'] ?? null);
		$this->groups = array_map(
			fn (array $group) => FeatureGroup::from($group),
			$data['groups'] ?? []
		);
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'heading' => $this->heading,
			'groups' => array_map(
				fn (FeatureGroup $group) => $group->toArray(),
				$this->groups
			)
		];
	}
}
