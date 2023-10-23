@props(['page', 'block'])

@php
    $getColumnClass = function (array $columns) {
        switch (count($columns)) {
            case 1:
                return 'w-full';
            case 2:
            case 4:
                return 'w-full sm:max-w-1/2';
            case 3:
            case 5:
            case 6:
                return 'w-full sm:max-w-1/2 lg:max-w-1/3';
            default:
                return 'w-full sm:max-w-1/2 lg:max-w-1/3';
        }
    };

    $getChunks = function (array $columns) {
        switch (count($columns)) {
            case 1:
                return 1;
            case 2:
            case 4:
                return 2;
            case 3:
            case 5:
            case 6:
                return 3;
            default:
                return 3;
        }
    };

    $columnClass = $getColumnClass($block->columns);
    $chunks = $getChunks($block->columns);
@endphp

<x-adler::portfolio.container
	class="py-16 md:py-20"
	id="feature-columns"
>
	@if($heading = $block->heading)
		<x-adler::portfolio.section-heading
			class="mb-14"
		>
			{{ $heading }}
		</x-adler::portfolio.section-heading>
	@endif

	<div
		@class(['flex flex-wrap justify-center gap-10 mb-10'])
		x-data
		x-animate-on-intersect="fade-in ease-out slide-in-from-bottom duration-1000"
	>
		@foreach($block->columns as $id => $column)
			<div
				@class([
					'flex-1 col-span-1 min-w-[300px] fade-in max-w-md flex space-x-4 items-start justify-start',
					$columnClass
				])
			>
				<x-icons.feature :type="$column->feature" class="w-24 h-24" />

				<div class="flex-1">
					<h3 class="mb-2 text-xl font-semibold text-primary-600">
						{{ $column->heading }}
					</h3>

					@if($column->text)
						<p class="prose">
							{{ $column->text }}
						</p>
					@endif
				</div>
			</div>
		@endforeach
	</div>
</x-adler::portfolio.container>
