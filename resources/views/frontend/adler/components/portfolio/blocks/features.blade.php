@props(['estate', 'block'])

@php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\FeaturesBlock $block */

$feed = $block->;
@endphp
{{-- Features --}}
@if ($page->estate->features->count() > 0)
	<x-adler::portfolio.container class="py-20 md:py-20">
		@if($heading = $block->getData('heading', 'BESONDERE MERKMALE'))
			<h3
				class="text-center mb-20 text-4xl/normal font-semibold relative text-primary-600"
				x-data
				x-animate-on-intersect="fade-in ease-out slide-in-from-bottom duration-1000"
			>
				{{ $heading }}
			</h3>
		@endif

		<x-portfolio.blocks.feature-block-grouped :groups="$page->estate->featureGroupsInPool" />
	</x-adler::portfolio.container>
@endif

