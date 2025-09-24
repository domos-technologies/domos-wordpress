<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Rentable\Space\Type;

class Usage implements Arrayable
{
	public function __construct(
		public ?Type $main = null,
		/** @var array<string, UsageByType> $all The area type and the associated area */
		public array $all = [],
	)
	{
	}

	public static function from(array $data): self
	{
		$all = [];

		foreach ($data['all'] ?? [] as $typeName => $area) {
			$type = Type::tryFrom($typeName);

			if ($type === null) {
				continue;
			}

			$all[$type->value] = new UsageByType(
				type: $type,
				area: $area ?? 0.0
			);
		}

		return new self(
			main: Type::tryFrom($data['main'] ?? ''),
			all: $all
		);
	}

	public function toArray(): array
	{
		$all = [];

		foreach ($this->all as $usageByType) {
			$all[$usageByType->type->value] = $usageByType->area;
		}

		return array_filter([
			'main' => $this->main?->value,
			'all' => $all,
		]);
	}
}