@props(['category'])

<?php

use \SchemaImmo\Estate\Location\Place\Category;

$findIcon = function (Category $category) {
	switch ($category) {
		case Category::PublicTransport:
			return 'transport.bus';
        case Category::Commercial:
			return 'cart-shopping';
        case Category::Freetime:
			return 'dumbbell';
        case Category::Food:
			return 'utensils';
        case Category::Parking:
			return 'parking';
        default:
			return 'location-dot';
	}
};

$iconName = $findIcon($category);
$component = "icons.places.{$iconName}"

?>

<x-dynamic-component :component="$component" {{ $attributes->class(['fill-current']) }} />
