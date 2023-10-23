@props(['page', 'block'])

@php
    $estate = property_exists($page, 'estate')
    	? $page->estate
    	: null;

    $closeByQuery = app(\App\Domains\Estate\Repositories\EstateRepository::class)
    	->queryForPortfolioList();

    $generalQuery = app(\App\Domains\Estate\Repositories\EstateRepository::class)
		->queryForPortfolioList();

	$estates = collect();

    if ($estate) {
        $closeByQuery
        	->where('id', '!=', $estate->id)
        	->whereHas('address', function ($query) use ($estate) {
				$query->where('city', $estate->address->city);
			});

        $generalQuery
        	->where('id', '!=', $estate->id);

        $estates = $closeByQuery
			->limit(3)
			->get()
			->shuffle();
    }

    $estates = $estates
    	->merge(
            $generalQuery
            	->limit(3)
            	->get()
            	->shuffle()
        );

    $estates = $estates
    	->unique('id')
    	->take(3)
    	->map(fn ($estate) => new \App\View\DTO\PortfolioList\PortfolioListEstate($estate));
@endphp

<x-adler::portfolio.container class="py-16 md:py-32">
	<x-adler::portfolio.section-heading
		text="text-2xl"
		class="mb-6"
	>
		Weitere Objekte
	</x-adler::portfolio.section-heading>
	<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-16">
		@foreach($estates as $estate)
			<x-adler::portfolio.list.estate-card :estate="$estate" class="col-span-1 aspect-video mb-20" />
		@endforeach
	</div>
</x-adler::portfolio.container>
