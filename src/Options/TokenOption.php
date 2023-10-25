<?php

namespace Domos\Core\Options;

/**
 * @extends Option<string>
 */
class TokenOption extends Option
{
	protected const OPTION = 'domos_token';

	/**
	 * @param string $value
	 */
	protected function validate($value): bool
	{
		if (is_null($value)) {
			return true;
		}

		if (!is_string($value)) {
			return false;
		}

		return true;
	}
}
