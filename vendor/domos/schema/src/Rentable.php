<?php

namespace SchemaImmo;

use SchemaImmo\Rentable\TransactionType;
use SchemaImmo\Rentable\Price;
use SchemaImmo\Rentable\Space;

class Rentable implements Arrayable
{
	public string $id;
	public ?string $name = null;
	public ?float $area = null;
	public ?string $description = null;

	public TransactionType $transaction_type;
	public ?Price $price = null;

	/** @var Space[] $spaces */
	public array $spaces = [];

	/** @var array<string, mixed> $features */
	public array $features = [];

	public Rentable\Media $media;

	public function __construct(
		string          $id,
		?string         $name = null,
		?float          $area = null,
		?string         $description = null,
		TransactionType $transaction_type = TransactionType::Sale,
		?Price          $price = null,
		array           $spaces = [],
		array 			$features = [],
		Rentable\Media  $media = new Rentable\Media
	)
	{
		$this->id = $id;
		$this->name = $name;
		$this->area = $area;
		$this->description = $description;
		$this->transaction_type = $transaction_type;
		$this->price = $price;
		$this->spaces = $spaces;
		$this->features = $features;
		$this->media = $media;
	}

	public static function from(array $data): self
	{
		$rentable = new self(
			Sanitizer::nullify_string($data['id'] ?? null)
		);

		$rentable->name = Sanitizer::nullify_string($data['name'] ?? null);
		$rentable->area = $data['area'] ?? null;
		$rentable->description = Sanitizer::nullify_string($data['description'] ?? null);

		$rentable->transaction_type = isset($data['transaction_type'])
			? TransactionType::from($data['transaction_type'])
			: TransactionType::Sale;

		$rentable->price = isset($data['price'])
			? Price::from($data['price'])
			: null;

		$rentable->spaces = array_map(
			fn(array $space) => Space::from($space),
			$data['spaces'] ?? []
		);

		$rentable->features = $data['features'] ?? [];

		if (isset($data['media'])) {
			$rentable->media = Rentable\Media::from($data['media']);
		}

		return $rentable;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'area' => $this->area,
			'description' => $this->description,
			'transaction_type' => $this->transaction_type->value,
			'price' => $this->price
				? $this->price->toArray()
				: null,
			'spaces' => array_map(
				fn(Space $space) => $space->toArray(),
				$this->spaces
			),
			'features' => $this->features,
			'media' => $this->media->toArray(),
		];
	}
}
