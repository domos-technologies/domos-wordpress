<?php

use \Domos\Core\Formatters\AddressFormatter;

$hexToRgb = function ($hex) {
	return $hex;
	$hex = str_replace('#', '', $hex);
	if (strlen($hex) === 3) {
		$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
		$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
		$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
	} else {
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));
	}
	$rgb = array($r, $g, $b);

	return 'rgb(' . implode(",", $rgb) . ')';
};

$colors = \Domos\Core\DOMOS::instance()->getPrimaryShades();
?>
<style>
	/* set root CSS variables */
	:root {
		@foreach($colors as $shade => $color)
		--domos-primary- {{ $shade }}: {{ $hexToRgb($color) }};
        @endforeach
	}
</style>

{{-- Header --}}
<x-adler::portfolio.header :bg-src="$estate->media->thumbnail->src ?? null">
	<div class="bg-gray-50/70 w-full md:w-2/3 lg:w-1/2 p-8 text-primary-500 ">
		@if($logo = $estate->media->logo)
			<img
				src="{{ $logo->src }}"
				alt="{{ $logo->alt }}"
				class="max-w-sm h-20 md:h-auto mb-8 animate-in fade-in duration-700 slide-in-from-bottom"
			/>
		@endif

		<h1 class="animate-in fade-in duration-700 slide-in-from-bottom text-3xl md:text-5xl/tight font-bold mb-4">
			{{ $estate->texts->slogan ?? $estate->name }}
		</h1>
		<p
			class="animate-in fade-in duration-700 slide-in-from-bottom text-2xl md:text-4xl/tight whitespace-pre-wrap"
		>{{ AddressFormatter::line($estate->address) }}</p>
	</div>
</x-adler::portfolio.header>


@if ($estate->expose === null)
	<div class="bg-primary-50">
		<x-adler::portfolio.container class="py-28">
			<div class="text-center">
				<h2 class="text-4xl text-primary-600 font-semibold mb-5">
					Kein Exposé vorhanden
				</h2>

				<p class="text-2xl text-primary-600">
					Das Exposé für diese Immobilie ist leider nicht verfügbar.
				</p>
			</div>
		</x-adler::portfolio.container>
	</div>
@else
	@foreach($estate->expose->blocks as $block)
			<?php
			$prefix = 'adler::';
			$view   = 'portfolio.blocks.' . $block->type->value;
			?>
		@if($block->type === \SchemaImmo\WebExpose\BlockType::Custom || view()->exists($prefix . 'components.' . $view) === false)
			<x-block-debugger :block="$block"/>

			@continue
		@endif

		<x-dynamic-component
			:component="$prefix . $view"
			:estate="$estate"
			:block="$block"
		/>
	@endforeach

	<x-adler::portfolio.blocks.contact-form :estate="$estate"/>
@endif
