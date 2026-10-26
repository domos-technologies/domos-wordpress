<?php

namespace Domos\Core\Options;

/**
 * @extends Option<string[]>
 */
class LanguagesOption extends Option
{
	protected const OPTION = 'immocore_languages';

	protected $default = [];

	/**
	 * Only a list of language codes may be stored. Without this, any value passed
	 * to set() (a string, a nested array, ...) was accepted and written, and the
	 * sync then read something it could not iterate.
	 *
	 * @param string[] $value
	 */
	protected function validate($value): bool
	{
		if (!is_array($value) || !array_is_list($value)) {
			return false;
		}

		foreach ($value as $language) {
			if (!is_string($language)) {
				return false;
			}
		}

		return true;
	}

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
