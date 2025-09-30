<?php

namespace Domos\Core\Options;

/**
 * @template T
 */
abstract class Option
{
	protected const OPTION = '';

	protected $value;

	protected $default = null; 

	/**
	 * @return T
	 */
	public function fresh()
	{
		$value = get_option(static::OPTION);

		if (!$value) {
			$value = $this->default;
		}

		return $value;
	}

	/**
	 * @return T
	 */
	public function get()
	{
		if (!isset($this->value)) {
			$this->value = $this->fresh();
		}

		return $this->value;
	}

	/**
	 * @param T $value
	 */
	public function set($value)
	{
		if (!$this->validate($value)) {
			throw new \Exception('Invalid value');
		}

		if (get_option(static::OPTION) === false) {
			add_option(static::OPTION, $value);
		} else {
			update_option(static::OPTION, $value);
		}
	}

	/**
	 * @param T $value
	 */
	protected function validate($value): bool
	{
		return true;
	}
}
