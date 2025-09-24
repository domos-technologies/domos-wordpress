<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Money;
use SchemaImmo\Rentable\Space\Type;

class UsageByType implements Arrayable
{
	public function __construct(
		public Type $type,
		public float $area,
	)
	{
	}

	public static function from(array $data): self
	{
		return new self(
			type: Type::from($data['type']),
			area: $data['area'],
		);
	}

	public function toArray(): array
	{
		return array_filter([
			'type' => $this->type->value,
			'area' => $this->area,
		]);
	}
}