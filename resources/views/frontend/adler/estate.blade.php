<?php

use \Domos\Core\Formatters\AddressFormatter;

$hexToRgb = function ($hex) {
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

	return implode(",", $rgb);
};

$primaryColors = \Domos\Core\DOMOS::instance()->getPrimaryShades();
$grayColors = \Domos\Core\DOMOS::instance()->getGrayShades();

global $wp_styles;
?>

<div class="domos-estate" id="domos-estate">
	<template shadowrootmode="open">
		<div>
			<style>
				/* set root CSS variables */
				:root, :host, .domos-estate {
					@foreach($primaryColors as $shade => $color)
					--domos-primary-{{ $shade }}: {{ $color }};
					@endforeach

					@foreach($grayColors as $shade => $color)
					--domos-gray-{{ $shade }}: {{ $color }};
					@endforeach

					@foreach($primaryColors as $shade => $color)
					--primary-{{ $shade }}: {{ $hexToRgb($color) }};
					@endforeach

					@foreach($grayColors as $shade => $color)
					--gray-{{ $shade }}: {{ $hexToRgb($color) }};
					@endforeach
				}
			</style>

			{{-- We manually output our styles here, because we need them to be INSIDE the shadow DOM to apply. --}}
			<?php $wp_styles->do_item('domos-frontend'); ?>
			<?php $wp_styles->do_item('domos-frontend-external'); ?>

			{{-- For tailwind purge: lg:table --}}
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
						@if (property_exists($block, 'html') && $block->html)
							{!! $block->html !!}
						@elseif(WP_DEBUG)
							<x-block-debugger :block="$block"/>
						@endif

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

			<script>
				// Trigger slider callbacks
				if (window.DOMOS_SLIDER_CALLBACKS) {
					window.DOMOS_SLIDER_CALLBACKS.forEach((callback) => callback());
				}

				// Disable queueing of slider callbacks
				window.DOMOS_SLIDER_CALLBACKS = false;
			</script>
		</div>
	</template>
</div>
