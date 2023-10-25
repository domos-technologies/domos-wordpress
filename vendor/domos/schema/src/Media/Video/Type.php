<?php

namespace SchemaImmo\Media\Video;

enum Type: string
{
	case Embed = 'embed';
	case Direct = 'direct';

	public function getVideoClass(): string
	{
		return match ($this) {
			self::Embed => \SchemaImmo\Media\Video\EmbedVideo::class,
			self::Direct => \SchemaImmo\Media\Video\DirectVideo::class,
		};
	}
}
