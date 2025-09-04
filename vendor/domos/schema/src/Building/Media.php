<?php

namespace SchemaImmo\Building;

use SchemaImmo\Arrayable;
use SchemaImmo\Image;
use SchemaImmo\Media\Scan;

class Media implements Arrayable
{
	public ?Image $thumbnail = null;
	public ?Image $thumbnail_small = null;

	/** @var Image[] $images */
	public array $images = [];

	/** @var Scan[] $scans */
	public array $scans = [];

	public function __construct(
		?Image $thumbnail = null,
		?Image $thumbnail_small = null,
		/** @var Image[] $images */
		array $images = [],
		/** @var Scan[] $scans */
		array $scans = [],
	)
	{
		$this->thumbnail = $thumbnail;
		$this->thumbnail_small = $thumbnail_small;
		$this->images = $images;
		$this->scans = $scans;
	}

	public static function from(array $data): Arrayable
	{
		$media = new self;
		$media->thumbnail = isset($data['thumbnail'])
			? Image::from($data['thumbnail'])
			: null;

		$media->thumbnail_small = isset($data['thumbnail_small'])
			? Image::from($data['thumbnail_small'])
			: null;

		$media->images = array_map(function ($image) {
			return Image::from($image);
		}, $data['images'] ?? []);

		$media->scans = array_map(function ($scan) {
			return Scan::from($scan);
		}, $data['scans'] ?? []);

		return $media;
	}

	public function toArray(): array
	{
		return array_filter([
			'thumbnail' => $this->thumbnail?->toArray(),
			'thumbnail_small' => $this->thumbnail_small?->toArray(),
			'images' => array_map(function ($image) {
				return $image->toArray();
			}, $this->images),
			'scans' => array_map(function ($scan) {
				return $scan->toArray();
			}, $this->scans),
		]);
	}
}
