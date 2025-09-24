<?php

namespace SchemaImmo\Rentable\Space;

class Type
{
    protected const Office = 'office';
	protected const Living = 'living';
	protected const Production = 'production';
	protected const Storage = 'storage';
	protected const Retail = 'retail';
	protected const Gastronomy = 'gastronomy';
	protected const Research = 'research';
	protected const Health = 'health';
    protected const OpenSpace = 'open-space';
	protected const OutdoorSpace = 'outdoor-space';

    public string $value;

    protected function __construct(string $value)
    {
        $this->value = $value;
    }

    public function label()
    {
        return match ($this->value) {
			self::Office => 'Büro',
			self::Living => 'Wohnen',
			self::Production => 'Produktion',
			self::Storage => 'Lager / Logistik',
			self::Retail => 'Groß- / Einzelhandel',
			self::Gastronomy => 'Gastronomie & Freizeit',
			self::Research => 'Forschung & Entwicklung',
			self::Health => 'Gesundheit & soziale Nutzungen',
            self::OpenSpace => 'Freifläche',
            self::OutdoorSpace => 'Außenfläche',
            default => ucfirst($this->value)
		};
    }

    public static function from(string $value): static
    {
        return new static($value);
    }

    public static function tryFrom(string $value): ?static
    {
		try {
			return new static($value);
		} catch (\Throwable $e) {
			return null;
		}
    }
}