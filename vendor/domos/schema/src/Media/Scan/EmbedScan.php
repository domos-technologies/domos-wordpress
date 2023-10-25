<?php

namespace SchemaImmo\Media\Scan;

use SchemaImmo\Media\Scan;
use SchemaImmo\Sanitizer;

class EmbedScan extends Scan
{
	public string $url;

	/** @var string|null $thumbnail_url */
	public $thumbnail_url = null;

	/**
	 * @param ?string $url
	 * @param ?string $thumbnail_url
	 * @param ?string $provider
	 * @param ?string $id
	 */
	public function __construct(
		$url = null,
		$thumbnail_url = null,
		$provider = null,
		$id = null,
	)
	{
		parent::__construct(
			Type::Embed,
			$provider,
			$id,
		);

		if ($url) {
			$this->url = $url;
		}

		$this->thumbnail_url = $thumbnail_url;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->url = $data['url'];
		$this->thumbnail_url = Sanitizer::nullify_string($data['thumbnail_url'] ?? null);

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'url' => $this->url,
			'thumbnail_url' => $this->thumbnail_url,
		];
	}
}
