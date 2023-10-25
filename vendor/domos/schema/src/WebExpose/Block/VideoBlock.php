<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\Media\Video;
use SchemaImmo\Sanitizer;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class VideoBlock extends Block
{
	public Video $video;

	/** @var string|null */
	public $heading = null;

	/** @var string|null */
	public $text = null;

	/**
	 * @param ?Video $video
	 * @param ?string $heading
	 * @param ?string $text
	 * @param ?string $id
	 */
	public function __construct(
		$video = null,
		$heading = null,
		$text = null,
		$id = null,
	)
	{
		parent::__construct(
			BlockType::Video,
			$id,
		);

		if ($video) {
			$this->video = $video;
		}

		$this->heading = Sanitizer::nullify_string($heading);
		$this->text = Sanitizer::nullify_string($text);
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->video = Video::from($data['video']);
		$this->heading = Sanitizer::nullify_string($data['heading'] ?? null);
		$this->text = Sanitizer::nullify_string($data['text'] ?? null);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'video' => $this->video->toArray(),
			'heading' => $this->heading,
			'text' => $this->text,
		];
	}
}
