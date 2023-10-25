@props(['estate', 'block'])

@php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\ImageTabsBlock $block */
@endphp

<div class="bg-primary-50">
	<x-adler::portfolio.container class="py-16 md:py-28 text-gray-600">

		@if($block->heading || $block->text)
			<div class="text-center max-w-xl mx-auto w-full mb-20">
				@if($block->heading)
					<h3
						class="mb-6 text-4xl/normal font-semibold relative text-primary-600"
						x-data
						x-animate-on-intersect="fade-in ease-out slide-in-from-bottom duration-1000"
					>
						{{ $block->heading }}
					</h3>
				@endif

				@if($block->text)
					<div
						class="text-lg relative text-gray-600"
						x-data
						x-animate-on-intersect="fade-in ease-out slide-in-from-bottom duration-1000"
					>
						{!! $block->text !!}
					</div>
				@endif
			</div>
		@endif

		<div
			x-data="{
				activeTab: 0,
			}"
		>
			<nav class="w-full overflow-x-auto">
				<div class="flex w-fit min-w-min">
				@foreach($block->tabs as $index => $tab)
					<?php /** @var \SchemaImmo\WebExpose\Block\ImageTabsBlock\ImageTab $tab */ ?>
					<button
						aria-label="{{ $tab->label }}"
						aria-controls="tab-{{ $index }}"
						aria-role="tab"

						x-on:click="activeTab = {{ $index }}"
						x-bind:class="{
							'bg-white': activeTab === {{ $index }},
						}"
						class="flex-shrink-0 w-fit truncate text-primary-500 hover:text-primary-400 text-xs sm:text-sm lg:text-base xl:text-lg font-semibold items-center px-4 py-2 rounded-t-md"
					>
						{{ $tab->label }}
					</button>
				@endforeach
				</div>
			</nav>

			@foreach($block->tabs as $index => $tab)
				<div
					x-show="activeTab === {{ $index }}"
					x-cloak
					class="bg-white p-5"
				>
					<img
						src="{{ $tab->image->src }}" alt="{{ $tab->image->alt }}"
						class="transition duration-1000 w-full h-auto object-contain max-h-[50rem]"
					/>
				</div>
			@endforeach
		</div>
	</x-adler::portfolio.container>
</div>
