@props([
    'places' => [],
    'categoryButtonClasses' => 'bg-primary-400',
    'categoryButtonActiveClasses' => 'hover:bg-primary-400',
    'expanded' => false,
    'maxItems' => 5,
])

<?php
use \SchemaImmo\Estate\Location\Place;
/** @var Place[] $places */

// Get Location->type enum and make it unique, collections not available in PHP 7.3.
// SORT_REGULAR because we're comparing enums
$placeTypes = array_unique(array_map(fn (Place $place) => $place->type, $places), SORT_REGULAR);
$placeTypeLabels = [];

foreach ($placeTypes as $type) {
    $placeTypeLabels[$type->value] = $type->label();
}

$categories = Place\Category::cases();
$placesByType = [];

foreach ($categories as $category) {
    $filtered = array_filter($places, fn (Place $place) => $place->type->category() === $category);
    $placesByType[$category->value] = array_values($filtered);
}

$categories = array_filter($categories, fn (Place\Category $category) => count($placesByType[$category->value]) > 0);

?>

<div
	x-data="{
	    placeTypeLabels: @js($placeTypeLabels),
		places: @js($placesByType),
		filter: null,
		showMoreClicked: @js($expanded),
		maxItems: @js($maxItems),

		getIcon(name, type) {
			name = name.toLowerCase();
{{--			.replaceAll('-', '');--}}
{{--			const icon = this.$refs['icon' + name];--}}
			const icon = this.$el.querySelector('#icon-' + type + '-' + name);

			console.log(this.$el, document);

			if (!icon) {
                console.error('unknown icon: #icon-' + type + '-' + name);
                return null;
            }

			return icon.innerHTML;
		},

		get filteredLocations() {
		    let places;

			if (this.filter === null) {
			    places = [];

				for (const placesInCategory of Object.values(this.places)) {
                    places.push(...placesInCategory);
                }
			} else {
			    places = this.places[this.filter] ?? [];
			}

            // Sort places by distance,
            places.sort((a, b) => {
                const distanceA = a.directions_from_estate?.walking?.distance ?? 999999;
                const distanceB = b.directions_from_estate?.walking?.distance ?? 999999;

                return distanceA - distanceB;
            });

			return places;
		},

		get limitedLocations() {
			if (!this.showingMore) {
				return this.filteredLocations.slice(0, this.maxItems);
			}

			return this.filteredLocations;
		},

		get showingMore() {
			return this.showMoreClicked;
		},

		get showMoreButtonVisible() {
			return this.limitedLocations.length > this.maxItems;
		},

		formatDistance(kilometers) {
		    if (kilometers < 1) {
                return Math.round(kilometers * 1000) + ' m';
            }

            return kilometers.toFixed(1) + ' km';
        },

        formatDuration(minutes) {
		    if (minutes < 60) {
                return Math.floor(minutes) + ' min';
            }


            const hours = Math.floor(minutes / 60);
            const remainingMinutes = Math.floor(minutes % 60);

            return hours + ' h ' + remainingMinutes + ' min';
        },

        formatTypeLabel(type) {
            return this.placeTypeLabels[type] ?? '';
        }
	}"
>
	<nav class="flex flex-wrap lg:flex-nowrap mb-10">
		<button
			@class([
				'flex flex-col items-center mr-2 px-2 py-1 pt-2 group',
			])
			:class="{
				@js($categoryButtonClasses): filter !== null,
				@js($categoryButtonActiveClasses): filter === null
			}"
			type="button"
			@click="filter = null"
		>
			<x-icons.places.location-dot class="w-6 h-6 mb-1 fill-current" />

			<span class="text-sm">Alles</span>
		</button>

		@foreach ($categories as $category)
			<button
				@class([
        			'flex flex-col items-center mr-2 px-2 py-1 pt-2 group',
        		])
				:class="{
					@js($categoryButtonClasses): filter !== @js($category->value),
					@js($categoryButtonActiveClasses): filter === @js($category->value)
				}"
				type="button"
				@click="filter = @js($category->value)"
			>
{{--                <span x-html="getIcon(@js($category->value), 'category')"></span>--}}
{{--				@foreach ($placeTypes as $type)--}}
{{--					<div id="icon-place-{{ $type->value }}" x-show="filter === @js($category->value)">--}}
{{--						<x-icons.dynamic-place-icon :type="$type" class="w-5 h-5" />--}}
{{--					</div>--}}
{{--				@endforeach--}}

{{--				@foreach ($categories as $category)--}}
{{--					<div id="icon-category-{{ $category->value }}" x-show="filter === @js($category->value)">--}}
{{--						<x-icons.dynamic-place-category-icon :category="$category" class="w-6 h-6 mb-1" />--}}
{{--					</div>--}}
{{--				@endforeach--}}

				<div>
					<x-icons.dynamic-place-category-icon :category="$category" class="w-6 h-6 mb-1" />
				</div>
				<span class="text-sm">{{ $category->label() }}</span>
			</button>
		@endforeach
	</nav>

	<div
		class="flex-1 flex flex-col items-start overflow-hidden overflow-y-hidden space-y-2 p-0"
		:aria-expanded="showingMore"
	>
		<template x-for="location of limitedLocations">
			<button
				:key="location.id"
				class="bg-transparent border-0 border-transparent p-0 flex items-center w-full space-x-3 text-lg text-left hover:opacity-80 expose-text"
				@click="flyTo({
                    center: [
                        location.coordinates.longitude,
                        location.coordinates.latitude
                    ],
                    zoom: 17,
                    pitch: 45
                })"
			>
				<span x-html="getIcon(location.type, 'place')" class="mr-1"></span>

				<div class="flex-1 flex flex-col">
					<span class="line-clamp-1" x-text="location.name"></span>
					<span
						class="text-sm opacity-75"
						x-text="formatTypeLabel(location.type)"
					></span>
				</div>
				<div class="flex flex-col items-end" x-show="location.directions_from_estate && location.directions_from_estate.walking">
					<div class="flex items-center">
						<span
							x-text="formatDistance(location.directions_from_estate.walking.distance)"
						></span>

                        <x-icons.person-walking class="w-4 h-4 ml-1" />
					</div>
					<span
						class="text-sm opacity-75 mr-5"
						x-text="formatDuration(location.directions_from_estate.walking.duration)"
					></span>
				</div>
			</button>
		</template>

		<button
			x-cloak
			x-show="showMoreButtonVisible"
			class="flex items-center justify-center pt-5 w-full"
			@click="showMoreClicked = !showMoreClicked"
			:aria-label="showingMore ? 'Weniger anzeigen' : 'Mehr anzeigen'"
		>
			<span x-show="!showingMore" :aria-hidden="showingMore">Mehr anzeigen</span>
			<span x-show="showingMore" :aria-hidden="!showingMore">Weniger anzeigen</span>

            <x-icons.chevron-down
                class="w-4 h-4 ml-2 transform transition-transform"
                x-bind:class="{
                    'rotate-180': showingMore
                }"
            />
		</button>
	</div>
</div>
