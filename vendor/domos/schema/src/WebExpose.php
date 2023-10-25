<?php

namespace SchemaImmo;

use SchemaImmo\WebExpose\Block;

class WebExpose implements Arrayable
{
	/**
	 * @var array<string, bool|array>
	 */
	public array $sidebar_features = [];

	/**
	 * @var array<string, bool|array>
	 */
	public array $pool_features = [];

	/**
	 * @var array<Block>
	 */
	public array $blocks = [];

	public static function from(array $data): self
	{
		$web_expose = new self;
		$web_expose->sidebar_features = $data['sidebar_features'] ?? [];
		$web_expose->pool_features = $data['pool_features'] ?? [];

		foreach ($data['blocks'] as $block) {
			$web_expose->blocks[] = Block::from($block);
		}

		return $web_expose;
	}

	public function toArray(): array
	{
		return [
			'sidebar_features' => $this->sidebar_features,
			'pool_features' => $this->pool_features,
			'blocks' => array_map(
				fn (Block $block) => $block->toArray(),
				$this->blocks
			),
		];
	}
}
