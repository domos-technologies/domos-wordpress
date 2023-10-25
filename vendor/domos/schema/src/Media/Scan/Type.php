<?php

namespace SchemaImmo\Media\Scan;

enum Type: string
{
	case Embed = 'embed';

	public function getScanClass(): string
	{
		return match ($this) {
			self::Embed => EmbedScan::class,
		};
	}
}
