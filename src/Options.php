<?php

namespace Domos\Core;

use Domos\Core\Options\CitiesOption;
use Domos\Core\Options\TokenOption;
use Domos\Core\Options\UrlOption;
use Domos\Core\Options\UsagesOption;

class Options
{
	public readonly UrlOption $url;
	public readonly TokenOption $token;
	public readonly CitiesOption $cities;
	public readonly UsagesOption $usages;

	public function __construct()
	{
		$this->url = new UrlOption();
		$this->token = new TokenOption();
		$this->cities = new CitiesOption();
		$this->usages = new UsagesOption();
	}
}
