<?php

namespace SchemaImmo\Media\Video;

use SchemaImmo\Media\Video;
use SchemaImmo\Media\Video\DirectVideo\VideoSource;

class DirectVideo extends Video
{
	/** @var VideoSource[] */
	public array $sources = [];

	/**
	 * @param VideoSource[] $sources
	 * @param string|null $thumbnail_url
	 * @param string|null $id
	 */
	public function __construct(
		array $sources = [],
		$thumbnail_url = null,
		$id = null,
	)
	{
		parent::__construct(
			Type::Direct,
			$thumbnail_url,
			$id,
		);

		$this->sources = $sources;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->sources = array_map(
			fn (array $source) => VideoSource::from($source),
			$data['sources'] ?? []
		);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'sources' => array_map(
				fn (VideoSource $source) => $source->toArray(),
				$this->sources
			),
		];
	}
}
