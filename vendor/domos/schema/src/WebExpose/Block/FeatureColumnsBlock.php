<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Sanitizer;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class FeatureColumnsBlock extends Block
{
	/** @var Block\FeatureColumnsBlock\FeatureColumn[] $columns */
	public array $columns = [];

	/** @var string|null $heading */
	public $heading = null;

	/**
	 * @param Block\FeatureColumnsBlock\FeatureColumn[] $columns
	 * @param ?string $heading
	 * @param ?string $id
	 */
	public function __construct(
		array $columns = [],
		$heading = null,
		$id = null,
	)
	{
		parent::__construct(
			BlockType::FeatureColumns,
			$id,
		);

		$this->columns = $columns;
		$this->heading = Sanitizer::nullify_string($heading);
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->columns = array_map(
			fn (array $column) => Block\FeatureColumnsBlock\FeatureColumn::from($column),
			$data['columns'],
		);
		$this->heading = Sanitizer::nullify_string($data['heading'] ?? null);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'columns' => array_map(
				fn (Block\FeatureColumnsBlock\FeatureColumn $column) => $column->toArray(),
				$this->columns,
			),
			'heading' => $this->heading,
		];
	}
}
