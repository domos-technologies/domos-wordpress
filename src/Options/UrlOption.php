<?php

namespace Domos\Core\Options;

/**
 * @extends Option<string>
 */
class UrlOption extends Option
{
	protected const OPTION = 'domos_url';

	/**
	 * @param string $value
	 */
	protected function validate($value): bool
	{
		if (is_null($value)) {
			return false;
		}

		if (!is_string($value)) {
			return false;
		}

		if (!filter_var($value, FILTER_VALIDATE_URL)) {
			return false;
		}

		return true;
	}
}
