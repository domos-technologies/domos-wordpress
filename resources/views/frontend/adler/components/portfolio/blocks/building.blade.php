@props(['block', 'estate'])

<?php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\BuildingBlock $block */

$building = $block->building;
?>

<x-adler::portfolio.container
	class="py-16 md:py-20 space-y-40"
	id="buildings"
>
	<article {{ $attributes }}>
		<div class="grid grid-cols-1 lg:grid-cols-5 gap-14 mb-20">
			<section class="col-span-full lg:col-span-3">
				<x-adler::portfolio.section-heading class="mb-3">
					{{ $building->name }}
				</x-adler::portfolio.section-heading>

				<p class="mb-10 text-xl font-medium text-gray-500">
					{{ \Domos\Core\Formatters\AddressFormatter::line($building->address) }}
				</p>

				<x-adler::portfolio.blocks.building.facts
					:facts="$block->facts"
				/>
			</section>

			<div class="col-span-full lg:col-span-2">
				@if (count($building->media->images) > 0)
					<x-adler::portfolio.blocks.building.slider
						:images="$building->media->images"
					/>
				@endif
			</div>
		</div>

		@if(count($block->features) > 0)
			<section class="grid grid-cols-5 gap-10 mb-20">
				<h4
					class="col-span-1 mb-8 text-2xl/normal font-semibold relative text-primary-600"
				>
					Fakten
				</h4>

				<div class="col-span-4 grid grid-cols-5 gap-5">
					@foreach($block->features as $name => $preformattedFeature)
						<?php
							/** @var \SchemaImmo\WebExpose\PreformattedFeature $preformattedFeature */
							$name = str_replace('_', '-', $name);
							$label = $preformattedFeature->label;
						?>
						<div class="flex flex-col items-center">
							<x-icons.feature :type="$name" class="w-24 h-24 aspect-square mb-2" />
							<div class="text-base text-center w-full font-medium text-gray-500">{{ $label }}</div>
						</div>
				    @endforeach
				</div>
			</section>
		@endif

		<section class="grid-cols-5 gap-10 hsidden lg:block ">
			<h4
				class="col-span-1 mb-8 text-2xl/normal font-semibold relative text-primary-600"
			>
				Mietflächen
			</h4>

			<x-adler::portfolio.blocks.building.table
				:estate="$estate"
				:building="$building"
				class="col-span-4 hidden lg:table"
			/>
		</section>
	</article>
</x-adler::portfolio.container>
