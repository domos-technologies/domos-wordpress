@props(['estate', 'building'])

<?php
use SchemaImmo\Rentable;

/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\Building $building */
/** @var \SchemaImmo\Rentable[] $rentables */

$rentables = $building->rentables;

$rentableIds = array_map(fn (Rentable $rentable) => $rentable->id, $rentables);

$rentableHasSlider = function (Rentable $rentable) {
	return count($rentable->media->images) > 0
	   || count($rentable->media->floorplans) > 0
	   || count($rentable->media->videos) > 0
	   || count($rentable->media->scans) > 0;
};

$rentableCanBeExpanded = function (Rentable $rentable) {
	return true || count($rentable->features) > 0;
};

$spacesWithRent = array_filter($rentables, function (Rentable $rentable) {
    $spacesWithRent = array_filter($rentable->spaces, function (Rentable\Space $space) {
		return $space->price !== null;
	});

    return count($spacesWithRent) > 0;
});
$hasAnySpaceRent = count($spacesWithRent) > 0;
?>

<table {{ $attributes->class(['w-full text-base table-auto mt-1 rounded shadow-inner bg-gray-50']) }}>
<thead class="border-b text-left [&_th]:font-medium [&_th]:px-6 [&_th]:py-4 uppercase dark:border-gray-500 border-gray-300 text-gray-600">
		<tr>
			<th
				scope="col"
				class="w-px whitespace-nowrap !p-0 !pl-6 !pr-5"
			>
			</th>
			<th
				scope="col"
			>
				Mietfläche
                <span class="block text-xs text-gray-400">&nbsp;</span>
			</th>
			<th
				scope="col"
			>
				Nutzung
                <span class="block text-xs text-gray-400">&nbsp;</span>
            </th>
			<th
				scope="col"
			>
				Geschoss
                <span class="block text-xs text-gray-400">&nbsp;</span>
            </th>
			<th
				scope="col"
			>
				<span>Mietzins</span>

				<span class="block text-xs text-gray-400">
					@if (true)
						Ohne Nebenkosten
					@else
						&nbsp;
					@endif
				</span>
			</th>
			<th
				scope="col"
			>
				Verfügbarkeit
                <span class="block text-xs text-gray-400">&nbsp;</span>
			</th>
{{--            <th--}}
			{{--				scope="col"--}}
			{{--			>--}}
			{{--			</th>--}}
		</tr>
	</thead>
	<tbody x-data="{
        expanded: null,
        init() {
            // Get query string from URL
            const url = new URL(window.location.href);
            const expand = url.searchParams.get('expand');

            const rentableIds = @js($rentableIds);

            if (expand && rentableIds.includes(expand)) {
                this.expanded = expand;

                const y = this.$el.getBoundingClientRect().top + window.scrollY - 100;
                window.scroll({
                    top: y,
                    behavior: 'smooth'
                });
            }
        },
        toggleExpansion(id) {
            this.expanded = this.expanded === id ? null : id;

            const url = new URL(window.location.href);

            if (this.expanded)
                url.searchParams.set('expand', this.expanded);
            else
                url.searchParams.delete('expand');

            // Add query string to URL (replace instead of push,
            // so we don't have to go back multiple times)
            window.history.replaceState({}, '', url);
        }
    }">
		@foreach ($rentables as $rentable)
			@php
				$hasSlider = $rentableHasSlider($rentable);
				$canBeExpanded = $hasSlider || $rentableCanBeExpanded($rentable);
			@endphp

			<x-adler::portfolio.blocks.building.table.row
				:$estate
				:$building
				:$rentable
				:$hasSlider
				:$canBeExpanded
				:$hasAnySpaceRent
			/>
		@endforeach

		@if (count($rentables) === 0)
			<tr>
				<td colspan="100%">
					<div class="w-full flex flex-col items-center justify-center">
						<x-icons.building class="w-14 h-14 opacity-10 mb-5" />

						<span class="text-lg">
							Aktuell keine Mietflächen verfügbar
						</span>
					</div>
				</td>
			</tr>
		@endif
	</tbody>
</table>
