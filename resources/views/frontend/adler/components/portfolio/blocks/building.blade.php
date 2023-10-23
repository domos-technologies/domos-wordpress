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

{{--				<x-portfolio.section.fact-box--}}
{{--					:facts="[]"--}}
{{--					:theme="$theme->factBox"--}}
{{--					class="w-full"--}}
{{--					cols="grid-cols-2"--}}
{{--				/>--}}
			</section>

			<div class="col-span-full lg:col-span-2">
{{--				@if ($hasSlider)--}}
{{--					<x-ui.slider--}}
{{--						class="w-full aspect-video rounded-lg overflow-hidden shadow"--}}
{{--					>--}}
{{--						@php--}}
{{--							$index = 0;--}}
{{--							$matterport_index = null;--}}
{{--							$video_index = null;--}}
{{--						@endphp--}}

{{--						@if ($building->matterport_url)--}}
{{--							<x-ui.slider.iframe-slide :src="$matterport_url" />--}}

{{--							@php--}}
{{--								$matterport_index = $index;--}}
{{--								$index++;--}}
{{--							@endphp--}}
{{--						@endif--}}

{{--						@foreach ($building->images as $image)--}}
{{--							<x-ui.slider.image-slide class="object-cover object-center" :src="$image->src" :preview-src="$image->thumbnailSrc" />--}}

{{--							@php--}}
{{--								$index++;--}}
{{--							@endphp--}}
{{--						@endforeach--}}

{{--						@if ($building->video_url)--}}
{{--							<x-ui.slider.iframe-slide :src="$video_url" />--}}

{{--							@php--}}
{{--								$video_index = $index;--}}
{{--								$index++;--}}
{{--							@endphp--}}
{{--						@endif--}}

{{--						<x-slot:overlay>--}}
{{--							<x-ui.slider.navigation :matterport-index="$matterport_index" :video-index="$video_index" />--}}
{{--						</x-slot:overlay>--}}
{{--					</x-ui.slider>--}}
{{--				@endif--}}
			</div>
		</div>

		@if(count($building->features) > 0)
			<section class="grid grid-cols-5 gap-10 mb-20">
				<h4
					class="col-span-1 mb-8 text-2xl/normal font-semibold relative text-primary-600"
				>
					Fakten
				</h4>

				<div class="col-span-4 grid grid-cols-7 gap-5">
					@foreach($building->features as $name => $data)
						<?php
							$name = str_replace('_', '-', $name);
							$label = \Domos\Core\FeatureType::from($name)->label();
						?>
						<div class="flex flex-col justify-center">
							<x-icons.feature :type="$name" class="w-full aspect-square mb-1" />
							<div class="text-xs text-center w-full font-medium text-gray-500">{{ $label }}</div>
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
				class="col-span-4"
			/>
		</section>
	</article>
</x-adler::portfolio.container>
