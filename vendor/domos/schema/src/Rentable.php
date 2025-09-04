<?php

namespace SchemaImmo;

use SchemaImmo\Rentable\Availability;
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
	public ?Price $price_per_m2 = null;

	/** @var Space[] $spaces */
	public array $spaces = [];

	/** @var array<string, mixed> $features */
	public array $features = [];

	public Rentable\Media $media;

	public ?Availability $availability = null;

	public function __construct(
		string          $id,
		?string         $name = null,
		?float          $area = null,
		?string         $description = null,
		TransactionType $transaction_type = TransactionType::Sale,
		?Price          $price = null,
		?Price          $price_per_m2 = null,
		array           $spaces = [],
		array 			$features = [],
		?Rentable\Media $media = null,
		?Availability   $availability = null
	)
	{
		$this->id = $id;
		$this->name = $name;
		$this->area = $area;
		$this->description = $description;
		$this->transaction_type = $transaction_type;
		$this->price = $price;
		$this->price_per_m2 = $price_per_m2;
		$this->spaces = $spaces;
		$this->features = $features;
		$this->media = $media ?? new Rentable\Media;
		$this->availability = $availability;
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

		$rentable->price_per_m2 = isset($data['price_per_m2'])
			? Price::from($data['price_per_m2'])
			: null;

		$rentable->spaces = array_map(
			fn(array $space) => Space::from($space),
			$data['spaces'] ?? []
		);

		$rentable->features = $data['features'] ?? [];

		if (isset($data['media'])) {
			$rentable->media = Rentable\Media::from($data['media']);
		} else {
			$rentable->media = new Rentable\Media;
		}

		if (isset($data['availability'])) {
			$rentable->availability = Availability::from($data['availability']);
		}

		return $rentable;
	}

	public function toArray(): array
	{
		return array_filter([
			'id' => $this->id,
			'name' => $this->name,
			'area' => $this->area,
			'description' => $this->description,
			'transaction_type' => $this->transaction_type->value,
			'price' => $this->price?->toArray(),
			'price_per_m2' => $this->price_per_m2?->toArray(),
			'spaces' => array_map(
				fn(Space $space) => $space->toArray(),
				$this->spaces
			),
			'features' => $this->features,
			'media' => $this->media->toArray(),
			'availability' => $this->availability?->toArray(),
		]);
	}
}
