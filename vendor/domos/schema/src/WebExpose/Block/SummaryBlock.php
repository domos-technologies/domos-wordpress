<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class SummaryBlock extends Block
{
	public ?string $summary = null;

	public array $features;

	public function __construct(
		?string $summary = null,
		array $features = [],
		?string $id = null,
	)
	{
		parent::__construct(
			BlockType::Summary,
			$id,
		);

		$this->summary = $summary;
		$this->features = $features;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->summary = $data['summary'];
		$this->features = $data['features'];

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'summary' => $this->summary,
			'features' => $this->features,
		];
	}
}
