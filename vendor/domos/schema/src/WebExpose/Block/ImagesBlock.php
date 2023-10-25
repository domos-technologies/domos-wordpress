<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Image;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class ImagesBlock extends Block
{
	/**
	 * @var Image[]
	 */
	public array $images = [];

	/**
	 * @param Image[] $images
	 */
	public function __construct(
		array $images = [],

		/** @var string|null */
		$id = null,
	)
	{
		parent::__construct(
			BlockType::ImageTabs,
			$id,
		);

		$this->images = $images;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->images = array_map(
			fn (array $image) => Image::from($image),
			$data['images']
		);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'images' => array_map(
				fn (Image $image) => $image->toArray(),
				$this->images
			),
		];
	}
}
