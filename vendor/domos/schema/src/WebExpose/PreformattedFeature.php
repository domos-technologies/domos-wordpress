<?php

namespace SchemaImmo\WebExpose;

use SchemaImmo\Arrayable;

class PreformattedFeature implements Arrayable
{
	public string $type;
	public string $label;
	public string $icon;

	public function __construct(
		string $type,
		string $label,
		string $icon
	)
	{
		$this->type = $type;
		$this->label = $label;
		$this->icon = $icon;
	}

	public static function from(array $data): self
	{
		return new self(
			$data['type'],
			$data['label'],
			$data['icon']
		);
	}

	public function toArray(): array
	{
		return [
			'type' => $this->type,
			'label' => $this->label,
			'icon' => $this->icon
		];
	}
}
