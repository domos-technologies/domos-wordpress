<?php

namespace SchemaImmo\Building;

use SchemaImmo\Arrayable;
use SchemaImmo\Image;

class Media implements Arrayable
{
	public ?Image $thumbnail = null;

	/** @var Image[] $images */
	public array $images = [];

	public function __construct(
		?Image $thumbnail = null,
		array $images = []
	)
	{
		$this->thumbnail = $thumbnail;
		$this->images = $images;
	}

	public static function from(array $data): Arrayable
	{
		$media = new self;
		$media->thumbnail = isset($data['thumbnail'])
			? Image::from($data['thumbnail'])
			: null;

		$media->images = array_map(function ($image) {
			return Image::from($image);
		}, $data['images'] ?? []);

		return $media;
	}

	public function toArray(): array
	{
		return [
			'thumbnail' => $this->thumbnail?->toArray(),
			'images' => array_map(function ($image) {
				return $image->toArray();
			}, $this->images),
		];
	}
}
