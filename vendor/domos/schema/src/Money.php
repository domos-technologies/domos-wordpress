<?php

namespace SchemaImmo;

use SchemaImmo\Money\Currency;

class Money implements Arrayable
{
	public float $amount;
	public Currency $currency;

	public function __construct(
		float $amount,
		Currency $currency = Currency::Euro
	)
	{
		$this->amount = $amount;
		$this->currency = $currency;
	}

	public static function from(array $data): self
	{
		return new Money(
			$data['amount'],
			isset($data['currency'])
				? Currency::from($data['currency'])
				: Currency::Euro
		);
	}

	public function toArray(): array
	{
		return [
			'amount' => $this->amount,
			'currency' => $this->currency->value,
		];
	}
}
