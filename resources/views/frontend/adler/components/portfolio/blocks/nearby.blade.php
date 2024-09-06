@props(['estate', 'block'])

@php
    /** @var \SchemaImmo\WebExpose\Block\NearbyBlock $block */
    /** @var \SchemaImmo\Estate $estate */

    $iframeUrl = \Domos\Core\DOMOS::instance()
        ->urlResolver
        ->estateMapUrl($estate->id);
@endphp

<div class="expose-bg-alt">
	<x-adler::portfolio.container class="py-16 md:py-28 expose-text-alt">
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
						class="text-4xl expose-heading font-semibold mb-5"
						x-data
						x-animate-on-intersect="fade-in duration-1000 slide-in-from-bottom"
					>
						{{ $block->heading ?? 'Lage' }}
					</h2>

					<span class="text-2xl expose-heading mb-1">{{ $estate->address->street }} {{ $estate->address->number }}</span>
					<span class="text-2xl expose-heading mb-5">{{ $estate->address->postal_code }}, {{ $estate->address->city }}</span>

					<x-adler::portfolio.blocks.nearby.locations
						:places="$estate->location->places"
						category-button-classes="expose-bg-card expose-text-alt"
						category-button-active-classes="expose-bg-card expose-text"
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
					<div class="mt-10 expose-text prose prose-a:text-inherit prose-h3:font-semibold prose-headings:text-primary-600">
						{!! $estate->texts->location_text !!}
					</div>
				@endif
			</div>
		</div>
	</x-adler::portfolio.container>
</div>
