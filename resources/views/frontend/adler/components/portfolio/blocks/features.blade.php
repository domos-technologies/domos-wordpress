@props(['estate', 'block'])

@php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\FeaturesBlock $block */
@endphp

@if (count($block->groups) > 0)
	<x-adler::portfolio.container class="py-16 md:py-32 domos-feature-block">
		<section
			{{ $attributes->class(['grid grid-cols-1 sm:grid-cols-2 gap-10 grid-flow-row-dense']) }}
		>
			@foreach($block->groups as $group)
				<div
					@class(["col-span-1 p-8 bg-gray-50 hover:bg-primary-50/50 transition-colors domos-feature-block--group"])
					x-data
					x-animate-on-intersect="fade-in ease-out slide-in-from-bottom duration-1000"
				>
					<h2
						class="text-xl text-primary-50 font-semibold mb-5 domos-feature-block--group--heading"
						style="break-before: column;"
					>
						{{ $group->label }}
					</h2>

					<div class="grid md:grid-cols-2 gap-x-3 gap-y-5">
						@foreach ($group->features as $name => $preformattedFeature)
							<?php
								$name = str_replace('_', '-', $name);
								$label = $preformattedFeature->label;
							?>
							<div @class([
								'flex flex-shrink flex-grow-0 flex-col items-center space-y-3 domos-feature-block--feature',
							])>
								<x-icons.feature :type="$name" class="w-20 h-20 aspect-square mb-1" />
								<p @class(['text-center text-gray-600 text-lg font-medium'])>
									{{ $label }}
								</p>
							</div>
						@endforeach
					</div>
				</div>
			@endforeach
		</section>

	</x-adler::portfolio.container>
@endif

