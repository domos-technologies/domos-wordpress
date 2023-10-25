<?php

namespace SchemaImmo\Media;

use SchemaImmo\Arrayable;

class Dimensions implements Arrayable
{
	public int $width;
	public int $height;

	public static function from(array $data): self
	{
		$size = new self;

		$size->width = $data['width'];
		$size->height = $data['height'];

		return $size;
	}

	public function toArray(): array
	{
		return [
			'width' => $this->width,
			'height' => $this->height,
		];
	}
}
