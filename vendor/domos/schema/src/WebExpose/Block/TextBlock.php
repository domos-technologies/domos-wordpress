<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class TextBlock extends Block
{
	public string $text = '';

	public function __construct(
		string $text = '',
		?string $id = null,
	)
	{
		parent::__construct(
			BlockType::Text,
			$id,
		);

		$this->text = $text;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->text = $data['text'];

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'text' => $this->text,
		];
	}
}
