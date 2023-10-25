<?php

namespace SchemaImmo\Estate\Location\Place;

enum Category: string
{
    case Freetime = 'freetime';
    case Food = 'food';
    case Commercial = 'commercial';
    case Parking = 'parking';
	case PublicTransport = 'public-transport';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            Category::PublicTransport => 'ÖPNV',
            Category::Freetime => 'Freizeit',
            Category::Food => 'Essen',
            Category::Commercial => 'Nahversorgung',
            Category::Parking => 'Parken',
            Category::Other => 'Sonstige'
        };
    }

    public function icon(): string
    {
        return match ($this) {
            Category::PublicTransport => 'fas-bus',
            Category::Freetime => 'fas-dumbbell',
            Category::Food => 'fas-utensils',
            Category::Commercial => 'fas-shopping-cart',
            Category::Parking => 'fas-parking',
            Category::Other => 'bi-geo-alt'
        };
    }

    public static function sorted(): array
    {
        return [
            Category::PublicTransport,
            Category::Freetime,
            Category::Food,
            Category::Commercial,
            Category::Parking,
            Category::Other,
        ];
    }
}
