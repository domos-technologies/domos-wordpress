@props(['type'])

<?php

use \SchemaImmo\Estate\Location\Place\Type;

//	return match ($this) {
//            LocationType::PublicTransport => $useOfficialTransportIcons ? 'ifm-bus' : 'fas-bus',
//            LocationType::BusStop => $useOfficialTransportIcons ? 'ifm-bus' : 'fas-bus',
//            LocationType::LightRailStation => $useOfficialTransportIcons ? 'ifm-s-bahn' : 'fas-train',
//            LocationType::SubwayStation => $useOfficialTransportIcons ? 'ifm-u-bahn' : 'fas-train-subway',
//            LocationType::TramStation => $useOfficialTransportIcons ? 'ifm-tram' : 'fas-train',
//            LocationType::TrainStation => 'fas-train-tram',
//			LocationType::Airport => 'fas-plane',
//			LocationType::Port => 'fas-ship',
//            LocationType::Parking => 'fas-parking',
//            LocationType::Restaurant => 'fas-utensils',
//            LocationType::Cafe => 'fas-coffee',
//            LocationType::Shop => 'fas-shopping-cart',
//            LocationType::Parks => 'fas-tree',
//            LocationType::FitnessCenter => 'fas-dumbbell',
//            LocationType::Supermarket => 'fas-shopping-cart',
//            LocationType::Building => 'fas-building',
//            default => 'fas-location-dot'
//        };

$findIcon = function (Type $type) {
	switch ($type) {
		case Type::Building:
			return 'building';
		case Type::Cafe:
			return 'coffee';
		case Type::FitnessCenter:
			return 'dumbbell';
        case Type::Parking:
			return 'parking';
        case Type::Parks:
			return 'tree';
        case Type::Port:
			return 'ship';
        case Type::PublicTransport:
			return 'transport.bus';
        case Type::Restaurant:
			return 'utensils';
        case Type::Shop:
			return 'cart-shopping';
        case Type::Supermarket:
			return 'cart-shopping';
        case Type::TramStation:
			return 'transport.tram';
        case Type::TrainStation:
			return 'train';
        case Type::LightRailStation:
			return 'transport.s-bahn';
        case Type::SubwayStation:
			return 'transport.u-bahn';
        case Type::BusStop:
			return 'transport.bus';
        case Type::Airport:
			return 'plane';
        default:
			return 'location-dot';
	}
};

$iconName = $findIcon($type);
$component = "icons.places.{$iconName}"

?>

<x-dynamic-component :component="$component" {{ $attributes->class(['fill-current']) }} />
