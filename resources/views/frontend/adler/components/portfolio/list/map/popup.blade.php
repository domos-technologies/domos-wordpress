@props(['estate'])

<x-portfolio.maps.base-popup :key="$estate->id">
    <x-portfolio.list.map.popup.slider
        :thumbnail="$estate->thumbnail"
        :images="$estate->images"
    >
        <x-slot:overlay>
            @if ($estate->available_rentables > 0)
                <x-portfolio.list.map.popup.pill color="success">
                    {{ $estate->available_rentables }} Flächen verfügbar
                </x-portfolio.list.map.popup.pill>
            @else
                <x-portfolio.list.map.popup.pill color="danger">
                    Voll vermietet
                </x-portfolio.list.map.popup.pill>
            @endif
        </x-slot:overlay>
    </x-portfolio.list.map.popup.slider>


	<x-portfolio.list.map.popup.content padding="">
        <x-portfolio.list.map.popup.sections.title-header class="px-6 py-5 bg-primary-600/90 mb-5">
            <h2 class="text-2xl font-semibold text-white ">{{ $estate->name }}</h2>
            <p class="xl:text-xl text-gray-300">{{ $estate->address }}</p>
        </x-portfolio.list.map.popup.sections.title-header>

		{{-- <hr class="my-4" /> --}}
		<x-portfolio.list.map.popup.content-sections class="px-6 pb-4">
			<aside>
				<x-portfolio.list.map.popup.sections.usages
                    class="mb-4"
                    :area-by-type="$estate->area_by_type"
                />

                <x-portfolio.list.map.popup.sections.features
                    :features="$estate->features"
                />
			</aside>

			@if ($estate->favourite_locations->count() > 0)
                <x-portfolio.list.map.popup.sections.locations
                    :locations="$estate->favourite_locations"
                />
			@endif
        </x-portfolio.list.map.popup.content-sections>

		<x-portfolio.list.map.popup.sections.continue-button
            :estate="$estate"
            grow
        >
            Objekt ansehen
        </x-portfolio.list.map.popup.sections.continue-button>
	</x-portfolio.list.map.popup.content>
</x-portfolio.maps.base-popup>
