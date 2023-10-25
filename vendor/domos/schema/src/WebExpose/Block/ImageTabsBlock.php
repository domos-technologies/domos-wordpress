<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Image;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\Block\ImageTabsBlock\ImageTab;
use SchemaImmo\WebExpose\BlockType;

class ImageTabsBlock extends Block
{
	public ?string $heading = null;
	public ?string $text = null;

	/**
	 * @var ImageTab[]
	 */
	public array $tabs = [];

	/**
	 * @param ImageTab[] $tabs
	 */
	public function __construct(
		array $tabs = [],
		?string $heading = null,
		?string $text = null,

		/** @var string|null */
		$id = null,
	)
	{
		parent::__construct(
			BlockType::ImageTabs,
			$id,
		);

		$this->heading = $heading;
		$this->text = $text;
		$this->tabs = $tabs;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->heading = $data['heading'] ?? null;
		$this->text = $data['text'] ?? null;

		$this->tabs = array_map(
			fn (array $image) => ImageTab::from($image),
			$data['tabs']
		);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'heading' => $this->heading,
			'text' => $this->text,
			'tabs' => array_map(
				fn (ImageTab $image) => $image->toArray(),
				$this->tabs
			),
		];
	}
}
