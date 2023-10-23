@props(['meta', 'headless' => false])

<x-portfolio.layout.skeleton
    :meta="$meta"
    :headless="$headless"
>
    <main {{ $attributes }}>
		@unless ($headless)
			<x-adler::portfolio.navbar />
		@endunless

		{{ $slot }}

		<x-adler::portfolio.footer />
	</main>
</x-portfolio.layout.skeleton>
