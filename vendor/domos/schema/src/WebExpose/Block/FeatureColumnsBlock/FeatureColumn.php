<?php

namespace SchemaImmo\WebExpose\Block\FeatureColumnsBlock;

use SchemaImmo\Arrayable;
use SchemaImmo\Sanitizer;

class FeatureColumn implements Arrayable
{
	public string $feature;
	public string $heading;
	public ?string $text = null;

	/**
	 * @param ?string $feature
	 * @param ?string $heading
	 * @param ?string $text
	 */
	public function __construct(
		$feature = null,
		$heading = null,
		$text = null,
	)
	{
		if ($feature) {
			$this->feature = $feature;
		}

		if ($heading) {
			$this->heading = $heading;
		}

		$this->text = Sanitizer::nullify_string($text);
	}

	public static function from(array $data): Arrayable
	{
		return new static(
			$data['feature'],
			$data['heading'],
			$data['text'],
		);
	}

	public function toArray(): array
	{
		return [
			'feature' => $this->feature,
			'heading' => $this->heading,
			'text' => $this->text,
		];
	}
}
