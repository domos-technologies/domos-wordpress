<?php

namespace SchemaImmo\Media\Video\DirectVideo;

use SchemaImmo\Arrayable;
use SchemaImmo\Media\Dimensions;
use SchemaImmo\Sanitizer;

class VideoSource implements Arrayable
{
	public string $url;

	/** @var ?string */
	public $mime = null;

	/** @var Dimensions|null */
	public $dimensions = null;

	public function __construct(
		string $url,

		/** @var ?string */
		$mime = null,

		/** @var Dimensions|null */
		$dimensions = null,
	)
	{
		$this->url = $url;
		$this->mime = Sanitizer::nullify_string($mime);
		$this->dimensions = $dimensions;
	}

	public static function from(array $data): Arrayable
	{
		$source = new self($data['url']);
		$source->mime = Sanitizer::nullify_string($data['mime'] ?? null);
		$source->dimensions = $data['dimensions']
			? Dimensions::from($data['dimensions'])
			: null;

		return $source;
	}

	public function toArray(): array
	{
		return [
			'url' => $this->url,
			'mime' => $this->mime,
			'dimensions' => $this->dimensions
				? $this->dimensions->toArray()
				: null,
		];
	}
}
