<?php

namespace SchemaImmo;

class Image implements Arrayable
{
	public string $src;
	public ?string $alt = null;

	public static function from(array $data): self
	{
		$image = new self;
		$image->src = $data['src'];

		if (isset($data['alt'])) {
			$image->alt = $data['alt'];
		}

		return $image;
	}

	public function toArray(): array
	{
		return [
			'src' => $this->src,
			'alt' => $this->alt
		];
	}
}
