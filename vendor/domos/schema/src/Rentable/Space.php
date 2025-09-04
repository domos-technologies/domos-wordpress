<?php

namespace SchemaImmo\Rentable;

// * @property-read string $id
// * @property SpaceType $type
// * @property float $area
// * @property Money $rent_per_m2
// * @property Money $extra_costs_per_m2
// * @property int $floor
// * @property ?string $notes

use SchemaImmo\Arrayable;
use SchemaImmo\Sanitizer;

class Space implements Arrayable
{
	public ?string $id = null;
	public Space\Type $type;
	public ?float $area = null;
	public ?Price $price = null;
	public ?Price $price_per_m2 = null;

	public ?int $floor = null;
	public ?string $notes = null;

	public function __construct(
        Space\Type $type,
        ?string    $id = null,
        ?float     $area = null,
        ?Price     $price = null,
		?Price     $price_per_m2 = null,
        ?int       $floor = null,
        ?string    $notes = null
	)
	{
		$this->type = $type;
		$this->id = Sanitizer::nullify_string($id);
		$this->area = $area;
		$this->price = $price;
		$this->price_per_m2 = $price_per_m2;
		$this->floor = $floor;
		$this->notes = Sanitizer::nullify_string($notes);
	}

	public static function from(array $data): self
	{
		return new self(
			Space\Type::from($data['type']),
			$data['id'] ?? null,
			$data['area'] ?? null,
			isset($data['price'])
				? Price::from($data['price'])
				: null,
			isset($data['price_per_m2'])
				? Price::from($data['price_per_m2'])
				: null,
			$data['floor'] ?? null,
			$data['notes'] ?? null,
		);
	}

	public function toArray(): array
	{
		return array_filter([
			'id' => $this->id,
			'type' => $this->type->value,
			'area' => $this->area,
			'price' => $this->price?->toArray(),
			'price_per_m2' => $this->price_per_m2?->toArray(),
			'floor' => $this->floor,
			'notes' => $this->notes,
		]);
	}
}
