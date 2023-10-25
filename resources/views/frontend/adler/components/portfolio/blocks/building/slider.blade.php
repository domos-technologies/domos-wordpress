@props([
	'scans' => [],
	'videos' => [],
	'images' => [],
])

<x-adler::slider
	class="w-full rounded-lg overflow-hidden shadow aspect-video"
>
	@php
		$index = 0;
		$matterportIndex = null;
		$videoIndex = null;
	@endphp

	@foreach ($scans as $scan)
		<x-adler::slider.iframe-slide :src="$matterportUrl" />

		@php
			if ($matterportIndex === null) {
				$matterportIndex = $index;
			}

			$index++;
		@endphp
	@endforeach

	@foreach ($images as $image)
		<x-adler::slider.image-slide
			class="object-cover object-center aspect-video"
			:src="$image->src"
			:preview-src="$image->src"
		/>

		@php
			$index++;
		@endphp
	@endforeach

	@foreach($videos as $video)
		<x-adler::slider.iframe-slide :src="$videoUrl" />--}}

		@php
			if ($videoIndex === null) {
				$videoIndex = $index;
			}

			$index++;
		@endphp
	@endforeach

	<x-slot:overlay>
		<x-adler::slider.navigation
			:$matterportIndex
			:$videoIndex
		/>
	</x-slot:overlay>
</x-adler::slider>
