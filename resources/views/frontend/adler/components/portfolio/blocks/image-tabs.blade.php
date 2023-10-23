@props(['page', 'block'])

@php
    $tabs = collect($block->getData('tabs', []))
    	->values();
@endphp

<div class="bg-primary-50">
	<x-adler::portfolio.container class="py-16 md:py-28 text-gray-600">

		@if($block->getData('heading') || $block->getData('text'))
			<div class="text-center max-w-xl mx-auto w-full mb-20">
				@if($heading = $block->getData('heading'))
					<h3
						class="mb-6 text-4xl/normal font-semibold relative text-primary-600"
						x-data
						x-animate-on-intersect="fade-in ease-out slide-in-from-bottom duration-1000"
					>
						{{ $heading }}
					</h3>
				@endif

				@if($text = $block->getData('text'))
					<div
						class="text-lg relative text-gray-600"
						x-data
						x-animate-on-intersect="fade-in ease-out slide-in-from-bottom duration-1000"
					>
						{!! $text !!}
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
				@foreach($tabs as $index => $tab)
					<button
						aria-label="{{ $tab['title'] }}"
						aria-controls="tab-{{ $index }}"
						aria-role="tab"

						x-on:click="activeTab = {{ $index }}"
						x-bind:class="{
							'bg-white': activeTab === {{ $index }},
						}"
						class="flex-shrink-0 w-fit truncate text-primary-500 hover:text-primary-400 text-xs sm:text-sm lg:text-base xl:text-lg font-semibold items-center px-4 py-2 rounded-t-md"
					>
						{{ $tab['title'] }}
					</button>
				@endforeach
				</div>
			</nav>

			@foreach($tabs as $index => $tab)
				@php
					$image = $block->getFile("tabs.{$index}.image");
				@endphp
				<div
					x-show="activeTab === {{ $index }}"
					x-cloak
					class="bg-white p-5"
				>
					<img
						src="{{ $image->url() }}" alt="{{ $image->name }}"
						class="transition duration-1000 w-full h-auto object-contain max-h-[50rem]"
					/>
				</div>
			@endforeach
		</div>
	</x-adler::portfolio.container>
</div>
