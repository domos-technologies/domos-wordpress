<?php

namespace Domos\Core\Options;

/**
 * @extends Option<string[]>
 */
class LanguagesOption extends Option
{
	protected const OPTION = 'immocore_languages';

	protected $default = [];

	public static function getSuggestions(): array
	{
		return [
			'de',
			'en',
			'fr',
			'it',
			'es',
			'nl',
			'tr',
			'ar',
			'ua',
			'ja',
			'cn',
		];
	}
}
