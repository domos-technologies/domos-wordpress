<?php

namespace SchemaImmo\Rentable\Space;

enum TypeEnum: string
{
	// TODO: work this out bruh
	case Office = 'office';
	case Living = 'living';
	case Production = 'production';
	case Storage = 'storage';
	case Retail = 'retail';
	case Gastronomy = 'gastronomy';
	case Research = 'research';
	case Health = 'health';

	public function label(): string
	{
		return match ($this) {
			self::Office => 'Büro',
			self::Living => 'Wohnen',
			self::Production => 'Produktion',
			self::Storage => 'Lager / Logistik',
			self::Retail => 'Groß- / Einzelhandel',
			self::Gastronomy => 'Gastronomie & Freizeit',
			self::Research => 'Forschung & Entwicklung',
			self::Health => 'Gesundheit & soziale Nutzungen',
		};
	}
}
