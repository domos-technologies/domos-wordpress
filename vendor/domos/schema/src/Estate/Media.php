<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Image;
use SchemaImmo\Media\Documents;
use SchemaImmo\Media\Scan;

class Media implements Arrayable
{
	public function __construct(
		public ?Image $thumbnail = null,
		public ?Image $thumbnail_small = null,

		/** @var Image[] $gallery */
		public array $gallery = [],

		/** @var Image|null $logo */
		public ?Image $logo = null,

		public array $videos = [],

		/** @var Scan[] $scans */
		public array $scans = [],
	)
	{
	}

	public static function from(array $data): self
	{
		$images = new self;

		if (isset($data['thumbnail'])) {
			$images->thumbnail = Image::from($data['thumbnail']);
		}

		if (isset($data['thumbnail_small'])) {
			$images->thumbnail_small = Image::from($data['thumbnail_small']);
		}

		if (isset($data['gallery'])) {
			foreach ($data['gallery'] as $image) {
				$images->gallery[] = Image::from($image);
			}
		}

		if (isset($data['logo'])) {
			$images->logo = Image::from($data['logo']);
		}

		if (isset($data['scans'])) {
			foreach ($data['scans'] as $scan) {
				$images->scans[] = Scan::from($scan);
			}
		}

		return $images;
	}

	public function toArray(): array
	{
		return array_filter([
			'thumbnail' => $this->thumbnail?->toArray(),
			'thumbnail_small' => $this->thumbnail_small?->toArray(),
			'gallery' => array_map(fn (Image $image) => $image->toArray(), $this->gallery),
			'logo' => $this->logo?->toArray(),
			'videos' => [], //array_map(fn (Video $video) => $video->toArray(), $this->videos),
			'scans' => array_map(fn (Scan $scan) => $scan->toArray(), $this->scans),
		]);
	}
}
