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

	public ?int $floor = null;
	public ?string $notes = null;

	public function __construct(
        Space\Type $type,
        ?string    $id = null,
        ?float     $area = null,
        ?Price     $price = null,
        ?int       $floor = null,
        ?string    $notes = null
	)
	{
		$this->type = $type;
		$this->id = Sanitizer::nullify_string($id);
		$this->area = $area;
		$this->price = $price;
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
			$data['floor'] ?? null,
			$data['notes'] ?? null,
		);
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type->value,
			'area' => $this->area,
			'price' => $this->price
				? $this->price->toArray()
				: null,
			'floor' => $this->floor,
			'notes' => $this->notes,
		];
	}
}
