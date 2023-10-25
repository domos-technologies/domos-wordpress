<?php

namespace SchemaImmo\Media\CameraFeed;

enum Type: string
{
	case Embed = 'embed';

	public function getCameraFeedClass(): string
	{
		return match ($this) {
			self::Embed => EmbedFeed::class,
		};
	}
}
