<?php

namespace SchemaImmo\Rentable;

use SchemaImmo\Arrayable;
use SchemaImmo\Image;
use SchemaImmo\Media\Scan;
use SchemaImmo\Media\Video;

class Media implements Arrayable
{
	/** @var Image[] */
	public array $images = [];

	/** @var Image[] */
	public array $floorplans = [];

	/** @var Scan[] */
	public array $scans = [];

	/** @var Video[] $videos */
	public array $videos = [];

	/**
	 * @param Image[] $images
	 * @param Image[] $floorplans
	 */
	public function __construct(
		array $images = [],
		array $floorplans = []
	)
	{
		$this->images = $images;
		$this->floorplans = $floorplans;
	}

	public static function from(array $data): self
	{
		return new Media(
			array_map(
				fn (array $image) => Image::from($image),
				$data['images'] ?? []
			),
			array_map(
				fn (array $image) => Image::from($image),
				$data['floorplans'] ?? []
			)
		);
	}

	public function toArray(): array
	{
		return [
			'images' => array_map(
				fn (Image $image) => $image->toArray(),
				$this->images
			),
			'floorplans' => array_map(
				fn (Image $image) => $image->toArray(),
				$this->floorplans
			),
		];
	}
}
