<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Image;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class ImageBlock extends Block
{
	public Image $image;

	public function __construct(
		/** @var Image|null */
		$image = null,

		/** @var string|null */
		$id = null,
	)
	{
		parent::__construct(
			BlockType::Image,
			$id,
		);

		if ($image) {
			$this->image = $image;
		}
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->image = Image::from($data['image']);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'image' => $this->image->toArray(),
		];
	}
}
