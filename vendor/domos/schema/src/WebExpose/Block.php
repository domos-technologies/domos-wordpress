<?php

namespace SchemaImmo\WebExpose;

use SchemaImmo\Arrayable;
use SchemaImmo\CanHaveExtraData;
use SchemaImmo\WebExpose\Block\CustomBlock;

abstract class Block implements Arrayable
{
	use CanHaveExtraData;

	public ?string $id = null;
	public BlockType $type;

	protected function __construct(
		BlockType $type,
		?string $id = null,
	)
	{
		$this->type = $type;
		$this->id = $id;
	}

	public static function from(array $data): static
	{
		$type = BlockType::tryFrom($data['type']) ?? BlockType::Custom;

		$blockClass = $type instanceof BlockType
			? $type->getBlockClass()
			: CustomBlock::class;

		$block = new $blockClass;
		$block->fill($data);

		return $block;
	}

	public function fill(array $data): static
	{
		$this->id = $data['id'] ?? null;
		$this->type = BlockType::tryFrom($data['type']) ?? BlockType::Custom;
		$this->extra = $data['extra'] ?? [];

		return $this;
	}

	public function toArray(): array
	{
		return array_merge(
			[
				'id' => $this->id,
				'type' => $this->type->value,
			],
			$this->toArrayWithoutExtra(),
			count($this->extra) > 0
				? ['extra' => $this->extra]
				: []
		);
	}
}
