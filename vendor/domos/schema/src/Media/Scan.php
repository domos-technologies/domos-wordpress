<?php

namespace SchemaImmo\Media;

use SchemaImmo\Arrayable;
use SchemaImmo\CanHaveExtraData;
use SchemaImmo\Media\Scan\Type;
use SchemaImmo\Sanitizer;

abstract class Scan implements Arrayable
{
	use CanHaveExtraData;

	public ?string $id = null;
	public Type $type;

	/** @var string|null $provider */
	public ?string $provider = null;

	protected function __construct(
		Type $type,
		?string $provider = null,
		?string $id = null,
	)
	{
		$this->type = $type;
		$this->provider = $provider;
		$this->id = $id;
	}

	public static function from(array $data): static
	{
		$type = Type::from($data['type']);
		$scanClass = $type->getScanClass();

		$scan = new $scanClass;
		$scan->fill($data);

		return $scan;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		$this->id = Sanitizer::nullify_string($data['id'] ?? null);
		$this->type = Type::from($data['type']);
		$this->provider = Sanitizer::nullify_string($data['provider'] ?? null);
		$this->extra = $data['extra'] ?? [];

		return $this;
	}

	public function toArray(): array
	{
		return array_merge(
			[
				'id' => $this->id,
				'type' => $this->type->value,
				'provider' => $this->provider,
			],
			$this->toArrayWithoutExtra(),
			count($this->extra) > 0
				? ['extra' => $this->extra]
				: []
		);
	}
}
