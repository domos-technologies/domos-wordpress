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
	 * @param Scan[] $scans
	 */
	public function __construct(
		array $images = [],
		array $floorplans = [],
		array $scans = [],
	)
	{
		$this->images = $images;
		$this->floorplans = $floorplans;
		$this->scans = $scans;
	}

	public static function from(array $data): self
	{
		return new Media(
			images: array_map(
				fn (array $image) => Image::from($image),
				$data['images'] ?? []
			),
			floorplans: array_map(
				fn (array $image) => Image::from($image),
				$data['floorplans'] ?? []
			),
			scans: array_map(
				fn (array $scan) => Scan::from($scan),
				$data['scans'] ?? []
			),
		);
	}

	public function toArray(): array
	{
		return array_filter([
			'images' => array_map(
				fn (Image $image) => $image->toArray(),
				$this->images
			),
			'floorplans' => array_map(
				fn (Image $image) => $image->toArray(),
				$this->floorplans
			),
			'scans' => array_map(
				fn (Scan $scan) => $scan->toArray(),
				$this->scans
			),
		]);
	}
}
