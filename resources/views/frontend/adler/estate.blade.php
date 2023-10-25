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
		return 'rgb(' . implode(",", $rgb) . ')';
	};
?>
<style>
/* set root CSS variables */
:root {
	--primary-50: {{ $hexToRgb('#f4f7fb') }};
	--primary-100: {{ $hexToRgb('#e9eff7') }};
	--primary-200: {{ $hexToRgb('#c7d7ec') }};
	--primary-300: {{ $hexToRgb('#a5bee1') }};
	--primary-400: {{ $hexToRgb('#628eca') }};
	--primary-500: {{ $hexToRgb('#1e5db3') }};
	--primary-600: {{ $hexToRgb('#1b54a1') }};
	--primary-700: {{ $hexToRgb('#174686') }};
	--primary-800: {{ $hexToRgb('#12386b') }};
	--primary-900: {{ $hexToRgb('#0f2e58') }};
	--primary-950: {{ $hexToRgb('#091c36') }};
}
</style>
<script>
	window.DOMOS = window.DOMOS || {
		lightbox: false
	};
</script>

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
            $view = 'portfolio.blocks.' . $block->type->value;
        ?>
        @if($block->type === \SchemaImmo\WebExpose\BlockType::Custom || view()->exists($prefix . 'components.' . $view) === false)
            <x-block-debugger :block="$block" />

            @continue
        @endif

        <x-dynamic-component
            :component="$prefix . $view"
            :estate="$estate"
            :block="$block"
        />
    @endforeach

	<x-adler::portfolio.blocks.contact-form :estate="$estate" />
@endif
