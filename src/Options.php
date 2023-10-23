<?php

namespace Domos\Core;

use Domos\Core\Options\CitiesOption;
use Domos\Core\Options\UsagesOption;

class Options
{
	public readonly CitiesOption $cities;
	public readonly UsagesOption $usages;

	public function __construct()
	{
		$this->cities = new CitiesOption();
		$this->usages = new UsagesOption();
	}
}
