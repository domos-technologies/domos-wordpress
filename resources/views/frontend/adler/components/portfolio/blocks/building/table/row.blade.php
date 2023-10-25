@props(['rentable', 'estate', 'building', 'expanded' => null, 'hasSlider' => false, 'canBeExpanded' => false, 'hasAnySpaceRent' => false])

<tr
	@class([
		'border-t border-gray-300 [&>td]:space-y-2 [&>td]:whitespace-nowrap [&>td]:py-2 [&>td]:px-6 [&>td]:border-0',
		'transition-colors hover:bg-gray-100' => $canBeExpanded
	])

	@if ($canBeExpanded)
		role="button"
		@click="toggleExpansion('{{ $rentable->id }}')"
	@endif
>
	<td class=" w-px whitespace-nowrap !p-0 !py-3 !pl-6 align-top text-gray-500">
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
{{--<template x-if="expanded === '{{ $rentable->id }}'">--}}
	<tr
		class="overflow-hidden group"
		:class="{
			'border-b border-gray-300': expanded === '{{ $rentable->id }}',
			'opacity-0': expanded !== '{{ $rentable->id }}',
		}"
	>
		<td class="!h-0 !p-0 !border-0 group-hover:bg-transparent"></td>
		<td colspan="6" class="!h-0 !p-0 !border-0 group-hover:bg-transparent" >
			<div
				@class([
					'transition-[max-height opacity] grid  gap-12 overflow-hidden pl-10 pr-8',
					'grid-cols-1' => !$hasSlider,
					'grid-cols-2' => $hasSlider
				])
				:class="{
					'!max-h-0 !py-0': expanded !== '{{ $rentable->id }}',
					'py-8': expanded === '{{ $rentable->id }}',
				}"
				x-cloak
				x-show="expanded === '{{ $rentable->id }}'"
				x-transition:enter="ease-out duration-200"
				x-transition:enter-start="opacity-0 max-h-0"
				x-transition:enter-end="opacity-100 max-h-96"
				x-transition:leave="ease-in duration-200"
				x-transition:leave-start="opacity-100 max-h-96"
				x-transition:leave-end="opacity-0 max-h-0"
			>
				@if ($hasSlider)
					<x-adler::portfolio.blocks.building.table.slider
						:images="$rentable->media->images"
						:scans="$rentable->media->scans"
						:videos="$rentable->media->videos"
					/>
				@endif

				<div class="flex flex-col">
					<x-adler::portfolio.blocks.building.table.facts
						:facts="[
							'Mietpreis' => $rentable->price
								? \Domos\Core\Formatters\MoneyFormatter::text($rentable->price->base_per_m2($rentable->area))
								: 'Auf Anfrage',
							'Nebenkosten' => $rentable->price
								? \Domos\Core\Formatters\MoneyFormatter::text($rentable->price->extra_costs_per_m2($rentable->area))
								: 'Auf Anfrage',
							'Gesamtfläche' => \Domos\Core\Formatters\AreaFormatter::text($rentable->area),
						]"
					/>

					@if(count($rentable->features) > 0)
						<div class="col-span-4 grid grid-cols-4 gap-4 mt-8">
							@foreach($rentable->features as $name => $data)
								<?php
									$name = str_replace('_', '-', $name);
									$label = \Domos\Core\FeatureType::from($name)->label();
								?>
								<div class="flex flex-col items-center max-w-[100px]">
									<x-icons.feature :type="$name" class="w-14 aspect-square mb-1" />
									<div class="text-sm text-center w-full font-medium text-gray-500">{{ $label }}</div>
								</div>
							@endforeach
						</div>
					@endif

					<div class="flex-1 min-h-[3rem]"></div>

					<div class="grid grid-cols-1 gap-2 w-full">
						@if (count($rentable->media->floorplans) > 0 && ($floorplan = $rentable->media->floorplans[0]))
							<x-adler::button-link
								href="{{ $floorplan->src }}"
								target="_blank"
								download="{{ $estate->name }} - {{ $building->name }} - {{ str($rentable->name)->replace('.', '_') }} - Grundriss"
							>
								<x-icons.download class="w-5 h-5 inline-block mr-2" />
								<span>Grundriss herunterladen</span>
							</x-adler::button-link>
						@endif
					</div>
				</div>
			</div>
		</td>
	</tr>
{{--</template>--}}
