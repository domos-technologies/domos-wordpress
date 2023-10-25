<?php

namespace SchemaImmo\Media\Video;

use SchemaImmo\Media\Dimensions;
use SchemaImmo\Media\Video;
use SchemaImmo\Sanitizer;

class EmbedVideo extends Video
{
	public string $url;

	/** @var ?string $provider */
	public $provider = null;

	/** @var Dimensions|null $dimensions */
	public $dimensions = null;

	/**
	 * @param ?string $url
	 * @param ?string $provider
	 * @param ?string $thumbnail_url
	 * @param ?Dimensions $dimensions
	 * @param ?string $id
	 */
	public function __construct(
		$url = null,
		$provider = null,
		$thumbnail_url = null,
		$dimensions = null,
		$id = null,
	)
	{
		parent::__construct(
			Type::Embed,
			$thumbnail_url,
			$id,
		);

		if ($url) {
			$this->url = $url;
		}

		$this->provider = Sanitizer::nullify_string($provider);
		$this->dimensions = $dimensions;
	}

	public function fill(array $data, array $nonExtraKeys = []): static
	{
		parent::fill($data);

		$this->url = $data['url'];
		$this->provider = Sanitizer::nullify_string($data['provider'] ?? null);
		$this->dimensions = $data['dimensions']
			? Dimensions::from($data['dimensions'])
			: null;

		return $this;
	}

	public function toArrayWithoutExtra(): array
	{
		return [
			'url' => $this->url,
			'provider' => $this->provider,
			'dimensions' => $this->dimensions
				? $this->dimensions->toArray()
				: null,
		];
	}
}
