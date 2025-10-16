<?php

namespace SchemaImmo\Rentable\Space;

use Throwable;

class Type
{
    public const Default = self::OpenSpace;
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
    public ?string $label = null;

    protected function __construct(string $value, ?string $label = null)
    {
        $this->value = $value;
        $this->label = $label;
    }

    public function label(): string
    {
        if ($this->label) {
            return $this->label;
        }

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
            default => $this->label ?? ucfirst($this->value)
		};
    }

    public static function from(string $value, ?string $label = null): static
    {
        return new static($value, $label);
    }

    public static function tryFrom(string $value, ?string $label = null): ?static
    {
		try {
			return new static($value, $label);
		} catch (Throwable $e) {
			return null;
		}
    }
}