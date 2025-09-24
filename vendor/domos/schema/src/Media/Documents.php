<?php

namespace SchemaImmo\Media;

use SchemaImmo\Arrayable;
use SchemaImmo\Document;

class Documents implements Arrayable
{
	public function __construct(
		/** @var Document[] $exposes */
		public array $exposes = [],
	)
	{
	}

	public static function from(array $data): self
	{
		$documents = new self;

		if (isset($data['exposes'])) {
			foreach ($data['exposes'] as $document) {
				$documents->exposes[] = Document::from($document);
			}
		}

		return $documents;
	}

	public function toArray(): array
	{
		return array_filter([
			'exposes' => $this->exposes
				? array_map(fn(Document $doc) => $doc->toArray(), $this->exposes)
				: null,
		]);
	}
}