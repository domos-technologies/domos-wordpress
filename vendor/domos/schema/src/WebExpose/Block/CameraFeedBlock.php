<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Media\CameraFeed;
use SchemaImmo\Media\Scan;
use SchemaImmo\Sanitizer;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class CameraFeedBlock extends Block
{
	public CameraFeed $camera_feed;

	/** @var string|null */
	public $heading = null;

	/** @var string|null */
	public $text = null;

	public function __construct(
		?CameraFeed $camera_feed = null,
		?string $heading = null,
		?string $text = null,
		?string $id = null,
	)
	{
		parent::__construct(
			BlockType::CameraFeed,
			$id,
		);

		if ($camera_feed) {
			$this->camera_feed = $camera_feed;
		}

		$this->heading = Sanitizer::nullify_string($heading);
		$this->text = Sanitizer::nullify_string($text);
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->camera_feed = CameraFeed::from($data['camera_feed']);
		$this->heading = Sanitizer::nullify_string($data['heading'] ?? null);
		$this->text = Sanitizer::nullify_string($data['text'] ?? null);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'camera_feed' => $this->camera_feed->toArray(),
			'heading' => $this->heading,
			'text' => $this->text,
		];
	}
}
