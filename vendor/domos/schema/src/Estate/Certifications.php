<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Estate\Certifications\DGNBCertification;

class Certifications implements Arrayable
{
	public ?DGNBCertification $dgnb = null;

	public ?bool $co2_neutral = null;

	public static function from(array $data): self
	{
		$certifications = new self;

		if (isset($data['dgnb'])) {
			$certifications->dgnb = DGNBCertification::from($data['dgnb']);
		}

		if (isset($data['co2_neutral'])) {
			$certifications->co2_neutral = $data['co2_neutral'];
		}

		return $certifications;
	}

	public function toArray(): array
	{
		return [
			'dgnb' => $this->dgnb
				? $this->dgnb->value
				: null,
			'co2_neutral' => $this->co2_neutral
		];
	}
}
