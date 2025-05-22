<?php

namespace SchemaImmo\Estate\Location\Place;

enum Type: string
{
	case PublicTransport = "public-transport";
	case SubwayStation = "subway-station";
	case LightRailStation = "light-rail-station";
	case TramStation = "tram-station";
	case BusStop = "bus-stop";
	case TrainStation = "train-station";
	case Airport = "airport";
	case Port = "port";
	case Parking = "parking";
	case Parks = "parks";
	case Restaurant = "restaurant";
	case FitnessCenter = "fitness-center";
	case Supermarket = "supermarket";
	case Cafe = "cafe";
	case Building = "building";
	case Shop = "shop";
	case HighwayJunction = "highway-junction";
	case Other = "other";

	public function label(): string
	{
		return match ($this) {
			Type::PublicTransport => "ÖPNV",
			Type::SubwayStation => "U-Bahn",
			Type::LightRailStation => "S-Bahn",
			Type::TramStation => "Straßenbahn",
			Type::BusStop => "Bushaltestelle",
			Type::TrainStation => "Bahnhof",
			Type::Airport => "Flughafen",
			Type::Port => "Hafen",
			Type::Parking => "Parkplatz",
			Type::Parks => "Park",
			Type::Restaurant => "Restaurant",
			Type::FitnessCenter => "Fitnessstudio",
			Type::Supermarket => "Supermarkt",
			Type::Cafe => "Café",
			Type::Building => "Gebäude",
			Type::Shop => "Geschäft",
			Type::HighwayJunction => "Autobahn-Auffahrt",
			Type::Other => "Sonstiges",
			default => str($this->value)
				->snake()
				->replace("-", " ")
				->title()
				->toString(),
		};
	}

	public function category(): Category
	{
		switch ($this) {
			case Type::PublicTransport:
			case Type::SubwayStation:
			case Type::LightRailStation:
			case Type::TramStation:
			case Type::BusStop:
			case Type::TrainStation:
			case Type::Airport:
			case Type::Port:
			case Type::HighwayJunction:
				return Category::PublicTransport;
			case Type::Parks:
			case Type::FitnessCenter:
			case Type::Building:
				return Category::Freetime;
			case Type::Restaurant:
			case Type::Cafe:
				return Category::Food;
			case Type::Shop:
			case Type::Supermarket:
				return Category::Commercial;
			case Type::Parking:
				return Category::Parking;
			default:
				return Category::Other;
		}
	}
}
