<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Sanitizer;

class Texts implements \SchemaImmo\Arrayable
{
	/** @var string|null $slogan */
	public $slogan = null;

	/** @var string|null $description */
	public $description = null;

	/** @var string|null $location_text */
	public $location_text = null;

	public static function from(array $data): self
	{
		$texts = new self;
		$texts->slogan = Sanitizer::nullify_string($data['slogan'] ?? null);
		$texts->description = Sanitizer::nullify_string($data['description'] ?? null);
		$texts->location_text = Sanitizer::nullify_string($data['location_text'] ?? null);

		return $texts;
	}

	public function toArray(): array
	{
		return [
			'slogan' => $this->slogan,
			'description' => $this->description,
			'location_text' => $this->location_text,
		];
	}
}
