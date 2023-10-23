@props(['src', 'previewSrc' => null, 'alt' => null])

<x-adler::slider.slide>
    <a
        href="{{ $src }}"
        x-data
        x-lightbox
	>
        <img
            {{ $attributes->class('w-full') }}
            src="{{ $previewSrc ?? $src }}"
			:alt="$alt"
            loading="lazy"
        />
    </a>
</x-adler::slider.slide>
