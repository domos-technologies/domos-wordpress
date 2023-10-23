@props(['estate', 'block'])

@php
    /** @var \SchemaImmo\WebExpose\Block\NearbyBlock $block */
    /** @var \SchemaImmo\Estate $estate */

    $iframeUrl = \Domos\Core\DOMOS::instance()
        ->urlResolver
        ->estateMapUrl($estate->id);
@endphp

<div class="bg-primary-50">
	<x-adler::portfolio.container class="py-16 md:py-28 text-gray-600">
		<div
            class="grid grid-cols-1 md:grid-cols-8 gap-20"
            id="nearby-map"
            x-data="{
                flyTo(camera) {
                    this.$refs.mapFrame.contentWindow.postMessage({
                        type: 'map:fly-to',
                        camera
                    }, '*');
                }
            }"
        >
			<div class="relative md:col-span-3">
				<div
					class="sticky top-[100px] font-medium flex flex-col"
				>
					<h2
						class="text-4xl text-primary-600 font-semibold mb-5"
						x-data
						x-animate-on-intersect="fade-in duration-1000 slide-in-from-bottom"
					>
						{{ $block->heading ?? 'Lage' }}
					</h2>

					<span class="text-2xl text-primary-600 mb-1">{{ $estate->address->street }} {{ $estate->address->number }}</span>
					<span class="text-2xl text-primary-600 mb-5">{{ $estate->address->postal_code }}, {{ $estate->address->city }}</span>

					<x-adler::portfolio.blocks.nearby.locations
						:places="$estate->location->places"
						category-button-classes="bg-gray-200 text-gray-700"
						category-button-active-classes="bg-gray-200"
					/>
				</div>
			</div>
			<div class="md:col-span-5 relative" >
                <iframe
                    src="{{ $iframeUrl }}"
                    class="w-full aspect-video relative shadow rounded-lg border-gray-200 border-2"
                    x-ref="mapFrame"
                ></iframe>

				@if($block->show_location_text && $estate->texts->location_text)
					<div class="mt-10 text-gray-600 prose prose-a:text-inherit prose-h3:font-semibold prose-headings:text-primary-600">
						{!! $estate->texts->location_text !!}
					</div>
				@endif
			</div>
		</div>
	</x-adler::portfolio.container>
</div>
