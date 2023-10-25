<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\CanHaveExtraData;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class CustomBlock extends Block
{
	public function __construct(
		array $data = [],

		/** @var string|null */
		$id = null,
	)
	{
		parent::__construct(
			BlockType::Custom,
			$id,
		);

		$this->extra = $data;
	}

	public function toArrayWithoutExtra(): array
	{
		return [];
	}
}
