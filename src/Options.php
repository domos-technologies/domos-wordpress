<?php

namespace Domos\Core;

use Domos\Core\Options\CitiesOption;
use Domos\Core\Options\TokenOption;
use Domos\Core\Options\UrlOption;
use Domos\Core\Options\UsagesOption;
use Domos\Core\Options\LanguagesOption;
use Domos\Core\Options\DefaultLanguageOption;

class Options
{
	public readonly UrlOption $url;
	public readonly TokenOption $token;
	public readonly CitiesOption $cities;
	public readonly UsagesOption $usages;
	public readonly LanguagesOption $languages;
	public readonly DefaultLanguageOption $default_language;

	public function __construct()
	{
		$this->url = new UrlOption();
		$this->token = new TokenOption();
		$this->cities = new CitiesOption();
		$this->usages = new UsagesOption();
		$this->languages = new LanguagesOption();
		$this->default_language = new DefaultLanguageOption();
	}
}
