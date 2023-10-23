@props(['id' => 'a', 'overlay' => null])

<section
	{{ $attributes->merge(['class' => 'splide [&_.splide\_\_arrow]:bg-transparent [&_.splide\_\_arrow_svg]:fill-white', 'aria-label' => 'Mietobjekt Bilder-slider']) }}
	x-data="slider"
>
	<div class="splide__track h-full">
		<ul class="splide__list h-full">
			{{ $slot }}
		</ul>
	</div>

	{{ $overlay }}
</section>
