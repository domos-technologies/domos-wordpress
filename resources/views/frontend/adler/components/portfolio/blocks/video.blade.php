@props(['estate', 'block'])

@php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\VideoBlock $block */

$video = $block->video;
@endphp

<x-adler::portfolio.container class="py-16 md:py-32">
	<div class="relative grid lg:grid-cols-6 lg:gap-10">
		<div class="z-0 lg:col-span-2 flex flex-col justify-start">
			<div class="relative p-10 lg:mb-10">
				<div class="absolute inset-0 lg:-right-[50%] bg-primary-100"></div>

				<h3 class="mb-3 text-3xl/normal font-semibold relative text-primary-600">{{ $block->heading ?? 'Video' }}</h3>
				<div class="text-lg/relaxed relative text-gray-600 prose prose-a:text-inherit prose-h3:font-semibold prose-headings:text-primary-600">
					{!! $block->text ?? 'Erleben Sie die Immobilie in bewegten Bildern.' !!}
				</div>
			</div>
		</div>

		<div class="lg:col-span-4 relative z-10 mt-10">
			@if($block->video->type === \SchemaImmo\Media\Video\Type::Direct)
				<video
					class="aspect-video relative shadow-lg w-full"
					controls

					@if($thumbnail = $block->video->thumbnail_url)
						poster="{{ $thumbnail }}"
					@endif
				>
                    @foreach($block->video->sources as $source)
                        <?php /** @var \SchemaImmo\Media\Video\DirectVideo\VideoSource $source */ ?>
                        <source
                            src="{{ $source->url }}"

                            @if($mime = $source->mime)
                            type="{{ $source->mime }}"
                            @endif
                        />
                    @endforeach
				</video>
			@elseif ($block->video->type === \SchemaImmo\Media\Video\Type::Embed)
                <?php
                    /** @var \SchemaImmo\Media\Video\EmbedVideo $video */
                    $host = parse_url($video->url, PHP_URL_HOST);
                ?>
				<x-embed-notice
                    class="aspect-video"
                    :name="
                        $video->provider
                            ? ucfirst($video->provider)
                            : $host
                    "
                    :cookie="$video->provider ?? 'external'"
                >
                    <iframe
                        @class(['w-full h-full aspect-video relative shadow-lg border-0'])
                        allow="autoplay; fullscreen; picture-in-picture"
                        src="{{ $video->url }}"
                    ></iframe>
				</x-embed-notice>
			@endif
		</div>
	</div>
</x-adler::portfolio.container>
