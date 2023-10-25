<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Sanitizer;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class NearbyBlock extends Block
{
	/** @var string|null */
	public $heading = null;
	public bool $show_location_text;

	public function __construct(
		/** @var string|null */
		$heading = null,

		/** @var bool */
		$show_location_text = true,

		/** @var string|null */
		$id = null,
	)
	{
		parent::__construct(
			BlockType::Nearby,
			$id,
		);

		$this->heading = Sanitizer::nullify_string($heading);
		$this->show_location_text = $show_location_text;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->heading = Sanitizer::nullify_string($data['heading'] ?? null);

		$this->show_location_text = $data['show_location_text'];

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'heading' => $this->heading,
			'show_location_text' => $this->show_location_text,
		];
	}
}
