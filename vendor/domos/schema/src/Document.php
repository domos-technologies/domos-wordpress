<?php

namespace SchemaImmo;

use SchemaImmo\Media\DocumentType;

class Document implements Arrayable
{
	public function __construct(
		public string $url,
		public DocumentType $type,
		public ?string $mime = null,
		public ?string $title = null,
		public ?string $language = null,
	) {
	}

	public static function from(array $data): self
	{
		return new self(
			url: $data['url'],
			type: DocumentType::from($data['type']),
			mime: $data['mime'] ?? null,
			title: $data['title'] ?? null,
			language: $data['language'] ?? null,
		);
	}

	public function toArray(): array
	{
		return array_filter([
			'url' => $this->url,
			'type' => $this->type->value,
			'mime' => $this->mime,
			'language' => $this->language,
			'title' => $this->title,
		]);
	}
}