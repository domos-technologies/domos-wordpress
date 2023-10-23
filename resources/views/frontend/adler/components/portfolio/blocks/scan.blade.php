
@props(['estate', 'block'])

@php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\ScanBlock $block */

$scan = $block->scan;
@endphp

<x-adler::portfolio.container class="py-16 md:py-32">
	<div class="relative grid lg:grid-cols-6 lg:gap-8">
		<div class="lg:col-span-4 relative z-10 mt-10">
            @if ($scan->type === \SchemaImmo\Media\Scan\Type::Embed)
                <?php /** @var \SchemaImmo\Media\Scan $scan */ ?>
                <x-embed-notice
                    class="aspect-video"
                    :name="
                        $scan->provider
                            ? ucfirst($scan->provider)
                            : $host
                    "
                    :cookie="$scan->provider ?? 'external'"
                >
                    <iframe
                        @class(['w-full h-full aspect-video relative shadow-lg border-0'])
                        allow="autoplay; fullscreen; picture-in-picture"
                        src="{{ $scan->url }}"
                    ></iframe>
				</x-embed-notice>
            @endif
		</div>

		<div class="z-0 lg:col-span-2 flex flex-col justify-start">
			<div class="relative py-10 px-5 lg:mb-10">
				<div class="absolute inset-0 lg:-left-[50%] bg-primary-100"></div>

				<h3 class="mb-3 text-3xl/normal font-semibold relative text-primary-600">{{ $block->heading ?? '3D-Scan' }}</h3>
				<p class="text-lg/relaxed relative text-gray-600">
					{!! $block->text ?? 'Entdecken Sie jeden Winkel in unserem virtuellen Rundgang.' !!}
				</p>
			</div>
		</div>
	</div>
</x-adler::portfolio.container>
