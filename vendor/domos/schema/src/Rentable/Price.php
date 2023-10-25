<?php

namespace SchemaImmo\Rentable;

use SchemaImmo\Arrayable;
use SchemaImmo\Money;

class Price implements Arrayable
{
	public Money $base;
	public ?Money $extra_costs = null;

	public function __construct(
		Money  $base,
		?Money $extra_costs = null
	)
	{
		$this->base = $base;
		$this->extra_costs = $extra_costs;
	}

	public static function from(array $data): Arrayable
	{
		return new self(
			Money::from($data['base']),
			isset($data['extra_costs'])
				? Money::from($data['extra_costs'])
				: null
		);
	}

	public function base_per_m2(float $area): float
	{
		// TODO: is this safe to do? Floating point precision?
		return $this->base->amount / $area;
	}

	public function extra_costs_per_m2(float $area): ?float
	{
		if (!$this->extra_costs) {
			return null;
		}

		// TODO: is this safe to do? Floating point precision?
		return $this->extra_costs->amount / $area;
	}

	public function total(): float
	{
		return $this->base->amount
			+ ($this->extra_costs ? $this->extra_costs->amount : 0);
	}

	public function toArray(): array
	{
		return [
			'base' => $this->base->toArray(),
			'extra_costs' => $this->extra_costs
				? $this->extra_costs->toArray()
				: null,
		];
	}
}
