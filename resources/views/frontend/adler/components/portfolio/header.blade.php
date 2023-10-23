@props(['page', 'bgSrc' => null])


<div
	{{ $attributes->class('relative bg-center bg-cover bg-primary-50') }}
	style="background-image: url('{{ $bgSrc }}');"
>
    <div class="max-w-7xl mx-auto relative flex items-center p-2 sm:p-10 md:px-0 md:py-24">
		{{ $slot }}
    </div>
</div>
