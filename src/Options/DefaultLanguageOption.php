<?php

namespace Domos\Core\Options;

/**
 * @extends Option<string>
 */
class DefaultLanguageOption extends Option
{
	protected $default = 'de';

	protected const OPTION = 'immocore_default_language';
}
