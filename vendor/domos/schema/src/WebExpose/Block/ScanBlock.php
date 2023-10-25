<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Media\Scan;
use SchemaImmo\Sanitizer;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class ScanBlock extends Block
{
	public Scan $scan;

	/** @var string|null */
	public $heading = null;

	/** @var string|null */
	public $text = null;

	/**
	 * @param ?Scan $scan
	 * @param ?string $heading
	 * @param ?string $text
	 * @param ?string $id
	 */
	public function __construct(
		$scan = null,
		$heading = null,
		$text = null,
		$id = null,
	)
	{
		parent::__construct(
			BlockType::Scan,
			$id,
		);

		if ($scan) {
			$this->scan = $scan;
		}

		$this->heading = Sanitizer::nullify_string($heading);
		$this->text = Sanitizer::nullify_string($text);
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->scan = Scan::from($data['scan']);
		$this->heading = Sanitizer::nullify_string($data['heading'] ?? null);
		$this->text = Sanitizer::nullify_string($data['text'] ?? null);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'scan' => $this->scan->toArray(),
			'heading' => $this->heading,
			'text' => $this->text,
		];
	}
}
