<?php

namespace SchemaImmo\WebExpose\Block\ImageTabsBlock;

use SchemaImmo\Arrayable;
use SchemaImmo\Image;

class ImageTab implements Arrayable
{
	public string $id;
	public string $label;
	public Image $image;

	public function __construct(
		string $id,
		string $label,
		Image $image,
	)
	{
		$this->id = $id;
		$this->label = $label;
		$this->image = $image;
	}

	public static function from(array $data): self
	{
		$tab = new self(
			$data['id'],
			$data['label'],
			Image::from($data['image'])
		);

		return $tab;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'label' => $this->label,
			'image' => $this->image->toArray(),
		];
	}
}
