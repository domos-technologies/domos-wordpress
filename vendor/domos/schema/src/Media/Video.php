<?php

namespace SchemaImmo\Media;

use SchemaImmo\Arrayable;
use SchemaImmo\CanHaveExtraData;
use SchemaImmo\Media\Video\Type;
use SchemaImmo\Sanitizer;

abstract class Video implements Arrayable
{
	use CanHaveExtraData;

	public ?string $id = null;
	public Type $type;

	/** @var string|null $thumbnail_url */
	public $thumbnail_url = null;

	public function __construct(
		Type $type,
		?string $thumbnail_url = null,
		?string $id = null,
	)
	{
		$this->type = $type;
		$this->thumbnail_url = Sanitizer::nullify_string($thumbnail_url);
		$this->id = Sanitizer::nullify_string($id);
	}

	public static function from(array $data): static
	{
		$type = Type::from($data['type']);
		$videoClass = $type->getVideoClass();

		$block = new $videoClass;
		$block->fill($data);

		return $block;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		$this->id = Sanitizer::nullify_string($data['id'] ?? null);
		$this->type = Type::from($data['type']);
		$this->thumbnail_url = Sanitizer::nullify_string($data['thumbnail_url'] ?? null);
		$this->extra = $data['extra'] ?? [];

		return $this;
	}

	public function toArray(): array
	{
		return array_merge(
			[
				'id' => $this->id,
				'type' => $this->type->value,
				'thumbnail_url' => $this->thumbnail_url,
			],
			$this->toArrayWithoutExtra(),
			count($this->extra) > 0
				? ['extra' => $this->extra]
				: []
		);
	}
}

