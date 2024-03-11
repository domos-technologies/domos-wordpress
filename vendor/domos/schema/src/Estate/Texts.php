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

    public function __construct(
        ?string $slogan = null,
        ?string $description = null,
        ?string $location_text = null,
    )
    {
        $this->slogan = $slogan;
        $this->description = $description;
        $this->location_text = $location_text;
    }

	public static function from(array $data): self
	{
        return new self(
            slogan: Sanitizer::nullify_string($data['slogan'] ?? null),
            description: Sanitizer::nullify_string($data['description'] ?? null),
            location_text: Sanitizer::nullify_string($data['location_text'] ?? null),
        );
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
