<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Image;
use SchemaImmo\Media\Scan;

class Media implements Arrayable
{
	public ?Image $thumbnail = null;

	/** @var Image[] $gallery */
	public array $gallery = [];

	/** @var Image|null $logo */
	public $logo = null;

	public array $videos = [];

	/** @var Scan[] */
	public array $scans = [];

	public static function from(array $data): self
	{
		$images = new self;

		if (isset($data['thumbnail'])) {
			$images->thumbnail = Image::from($data['thumbnail']);
		}

		if (isset($data['gallery'])) {
			foreach ($data['gallery'] as $image) {
				$images->gallery[] = Image::from($image);
			}
		}

		if (isset($data['logo'])) {
			$images->logo = Image::from($data['logo']);
		}

		return $images;
	}

	public function toArray(): array
	{
		return [
			'thumbnail' => $this->thumbnail
				? $this->thumbnail->toArray()
				: null,
			'gallery' => array_map(fn (Image $image) => $image->toArray(), $this->gallery),
			'logo' => $this->logo
				? $this->logo->toArray()
				: null,
			'videos' => [], //array_map(fn (Video $video) => $video->toArray(), $this->videos),
			'scans' => [], //array_map(fn (Scan $scan) => $scan->toArray(), $this->scans),
		];
	}
}
