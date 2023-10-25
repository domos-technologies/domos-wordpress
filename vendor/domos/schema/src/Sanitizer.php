<?php

namespace SchemaImmo;

class Sanitizer
{
	/**
	 * Nullify empty strings.
	 *
	 * @param string|null $value
	 * @return string|null
	 */
	public static function nullify_string(?string $value): ?string
	{
		return $value === ''
			? null
			: $value;
	}
}
