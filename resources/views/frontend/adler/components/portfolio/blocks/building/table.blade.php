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


			<tr
                @class([
                    'border-t border-gray-300 [&>td]:space-y-2 [&>td]:whitespace-nowrap [&>td]:py-2 [&>td]:px-6',
                    'transition-colors hover:bg-gray-100' => $canBeExpanded
                ])

                @if ($canBeExpanded)
                    role="button"
				    @click="toggleExpansion('{{ $rentable->id }}')"
                @endif
			>
				<td class="w-px whitespace-nowrap !p-0 !py-3 !pl-6 align-top text-gray-500">
					@if ($canBeExpanded)
						<x-icons.chevron-right
							class="w-5 h-5 transition-transform"
							x-bind:class="{
								'transform rotate-90': expanded === '{{ $rentable->id }}',
								'transform rotate-0': expanded !== '{{ $rentable->id }}'
							}"
						/>
					@endif
				</td>
				<td class="font-medium">
					@foreach ($rentable->spaces as $space)
						<p>{{ \Domos\Core\Formatters\AreaFormatter::text($space->area) }}</p>
					@endforeach
				</td>
				<td>
					@foreach ($rentable->spaces as $space)
						<p>{{ $space->type->label() }}</p>
					@endforeach
				</td>
				<td>
					@foreach ($rentable->spaces as $space)
						<p>{{ \Domos\Core\Formatters\FloorFormatter::text($space->floor) }}</p>
					@endforeach
				</td>
				<td @class(['align-top' => !$hasAnySpaceRent])>
					@foreach ($rentable->spaces as $space)
						<p>
							@if($space->price && $space->area)
								{{ \Domos\Core\Formatters\MoneyFormatter::text($space->price->base_per_m2($space->area)) }}
								<span class="opacity-50">
									pro m²
								</span>
							@else
								auf Anfrage
							@endif
						</p>
					@endforeach
				</td>
				<td class="align-top">
					<p>
						ab sofort
{{--						{{ $rentable->availability }}--}}
					</p>
				</td>
{{--                <td>--}}
{{--                    <nav class="flex h-full items-center justify-end">--}}
{{--                        <button--}}
{{--                            class="transition transform scale-100 active:scale-90 group"--}}
{{--                            x-data="starButton(@js($toCartData($rentable)))"--}}
{{--                            x-tooltip="tooltipText"--}}
{{--                            @click="toggle"--}}
{{--                        >--}}
{{--                            @svg('bi-building-add', 'w-6 h-6 text-gray-600 group-hover:text-success-600', ['x-show' => '!starred', 'x-cloak'])--}}
{{--                            @svg('bi-building-fill-check', 'w-6 h-6 text-primary-600 block group-hover:hidden', ['x-show' => 'starred', 'x-cloak'])--}}
{{--                            @svg('bi-building-fill-x', 'w-6 h-6 group-hover:text-danger-600/50 hidden group-hover:block', ['x-show' => 'starred', 'x-cloak'])--}}
{{--                        </button>--}}
{{--                    </nav>--}}
{{--                </td>--}}
			</tr>
			<template x-if="expanded === '{{ $rentable->id }}'">
				<tr class="overflow-hidden">
					<td></td>
					<td colspan="6" class="h-0">
						<div
							@class([
								'transition-[max-height opacity] grid  gap-12 overflow-hidden pl-10 pr-8 py-8',
								'grid-cols-1' => !$hasSlider,
								'grid-cols-2' => $hasSlider
							])
							x-transition:enter="ease-out duration-200"
							x-transition:enter-start="opacity-0 max-h-0"
							x-transition:enter-end="opacity-100 max-h-96"
							x-transition:leave="ease-in duration-200"
							x-transition:leave-start="opacity-100 max-h-96"
							x-transition:leave-end="opacity-0 max-h-0"
						>
	{{--                        @if ($hasSlider)--}}
	{{--                            <x-ui.slider--}}
	{{--                                class="w-full rounded-lg overflow-hidden shadow aspect-video"--}}
	{{--                            >--}}
	{{--                                @php--}}
	{{--                                    $index = 0;--}}
	{{--                                    $matterport_index = null;--}}
	{{--                                    $video_index = null;--}}
	{{--                                @endphp--}}
	{{--                                @if ($matterportUrl = $rentable->matterport_url)--}}
	{{--                                    <x-ui.slider.iframe-slide :src="$matterportUrl" />--}}

	{{--                                    @php--}}
	{{--                                        $matterport_index = $index;--}}
	{{--                                        $index++;--}}
	{{--                                    @endphp--}}
	{{--                                @endif--}}

	{{--                                @foreach ($rentable->slides as $image)--}}
	{{--                                    <x-ui.slider.image-slide class="object-cover object-center aspect-video" :src="$image->src" :preview-src="$image->thumbnailSrc" />--}}

	{{--                                    @php--}}
	{{--                                        $index++;--}}
	{{--                                    @endphp--}}
	{{--                                @endforeach--}}

	{{--                                @if ($videoUrl = $rentable->video_url)--}}
	{{--                                    <x-ui.slider.iframe-slide :src="$videoUrl" />--}}

	{{--                                    @php--}}
	{{--                                        $video_index = $index;--}}
	{{--                                        $index++;--}}
	{{--                                    @endphp--}}
	{{--                                @endif--}}

	{{--                                <x-slot:overlay>--}}
	{{--                                    <x-ui.slider.navigation :matterport-index="$matterport_index" :video-index="$video_index" />--}}
	{{--                                </x-slot:overlay>--}}
	{{--                            </x-ui.slider>--}}
	{{--                        @endif--}}

							<div class="flex flex-col">
	{{--							<x-portfolio.section.fact-box--}}
	{{--								:facts="$rentable->facts"--}}
	{{--								class="w-full opacity-90 mb-10 !gap-y-5"--}}
	{{--								cols="grid-cols-2"--}}
	{{--							/>--}}

	{{--							@if($rentable->features->isNotEmpty())--}}
	{{--								<x-portfolio.blocks.feature-block--}}
	{{--									:features="$rentable->features"--}}
	{{--									:theme="$theme->features"--}}
	{{--									size="sm"--}}
	{{--								/>--}}
	{{--							@endif--}}

								<div class="flex-1 min-h-[3rem]"></div>

								<div class="grid grid-cols-1 gap-2 w-full">
									 Download button
									@if (count($rentable->media->floorplans) > 0 && ($floorplan = $rentable->media->floorplans[0]))
										<a
											class="button"
											href="{{ $floorplan->src }}"
											target="_blank"
											download="{{ $page->estate->name }} - {{ $building->name }} - {{ str($rentable->name)->replace('.', '_') }} - Grundriss"
										>
											Grundriss herunterladen &nbsp;
										</a>
									@endif
								</div>
							</div>
						</div>
					</td>
				</tr>
			</template>
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
