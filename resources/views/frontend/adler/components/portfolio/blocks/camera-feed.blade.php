
@props(['estate', 'block'])

@php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\CameraFeedBlock $block */

$feed = $block->camera_feed;
@endphp

<x-adler::portfolio.container class="py-16 md:py-32">
	<div class="relative grid lg:grid-cols-6 lg:gap-8">
		<div class="lg:col-span-4 relative z-10 mt-10">
            @if ($feed->type === \SchemaImmo\Media\CameraFeed\Type::Embed)
                <?php /** @var \SchemaImmo\Media\CameraFeed\EmbedFeed $feed */ ?>
                <x-embed-notice
                    class="aspect-video"
                    :name="
                        $feed->provider
                            ? ucfirst($feed->provider)
                            : $host
                    "
                    :cookie="$feed->provider ?? 'external'"
                >
                    <iframe
                        @class(['w-full h-full aspect-video relative shadow-lg border-0'])
                        allow="autoplay; fullscreen; picture-in-picture"
                        src="{{ $feed->url }}"
                    ></iframe>
				</x-embed-notice>
            @endif
		</div>

		<div class="z-0 lg:col-span-2 flex flex-col justify-start">
			<div class="relative py-10 px-5 lg:mb-10">
				<div class="absolute inset-0 lg:-left-[50%] bg-primary-100"></div>

				<h3 class="mb-3 text-3xl/normal font-semibold relative text-primary-600">{{ $block->heading ?? 'Baustellenkamera' }}</h3>
				<p class="text-lg/relaxed relative text-gray-600">
					{!! $block->text ?? 'Verfolgen Sie den Baufortschritt in Echtzeit.' !!}
				</p>
			</div>
		</div>
	</div>
</x-adler::portfolio.container>
