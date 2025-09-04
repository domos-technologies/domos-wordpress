<?php

namespace SchemaImmo\Rentable;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use SchemaImmo\Arrayable;
use SchemaImmo\Rentable\Availability\Status;

class Availability implements Arrayable
{
	public Status $status;
	public string $text;

	public ?DateTimeInterface $date = null;
	public bool $afterTenantConstruction = false;
	public bool $availableAfterIsQuarterly = false;

	public function __construct(
		Status              $status,
		string              $text,
		?DateTimeInterface  $date = null,
		bool                $afterTenantConstruction = false,
	)
	{
		$this->status = $status;
		$this->text = $text;
		$this->date = $date;
		$this->afterTenantConstruction = $afterTenantConstruction;
	}

	public static function from(array $data): self
	{
		try {
			$date = isset($data['date'])
				? new DateTimeImmutable($data['date'])
				: null;
		} catch (Exception $e) {
			error_log('Could not parse availability date (' . $data['date'] . '): ' . $e->getMessage());

			$date = null;
		}

		return new self(
			status: Status::from($data['status']),
			text: $data['text'],
			date: $date,
			afterTenantConstruction: $data['after_tenant_construction'] ?? false,
		);
	}

	public function toArray(): array
	{
		return array_filter([
			'status' => $this->status->value,
			'text' => $this->text,
			'date' => $this->date?->format('Y-m-d'),
			'after_tenant_construction' => $this->afterTenantConstruction,
		]);
	}
}