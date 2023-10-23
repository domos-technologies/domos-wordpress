@props(['page', 'block'])

@php
    $estateId = property_exists($page, 'estate') ? $page->estate?->id : null;
@endphp

<x-adler::portfolio.container class="py-16 md:py-32 grid grid-cols-1 md:grid-cols-2 gap-16">
	<div @class([
			'col-span-1',
			'[&_.fi-input-wrapper]:rounded-none [&_.fi-input-wrapper]:ring-0 [&_.fi-input-wrapper]:border-b-2 [&_.fi-input-wrapper]:border-primary-500 [&_.fi-input-wrapper]:shadow-none',
			'[&_.fi-input]:placeholder:text-gray-500 [&_.fi-input]:px-0 [&_.fi-input]:text-lg',
			'[&_.fi-fo-textarea]:ring-0 [&_.fi-fo-textarea]:shadow-none [&_.fi-fo-textarea]:placeholder:text-gray-500 [&_.fi-fo-textarea]:px-0 [&_.fi-fo-textarea]:text-lg [&_.fi-fo-textarea]:border-0 [&_.fi-fo-textarea]:!border-b-2 [&_.fi-fo-textarea]:!border-solid [&_.fi-fo-textarea]:border-primary-500 [&_.fi-fo-textarea]:rounded-none',
			'[&_.fi-btn]:bg-primary-500 [&_.fi-btn]:hover:bg-primary-600 [&_.fi-btn]:text-white [&_.fi-btn]:font-semibold [&_.fi-btn]:px-8 [&_.fi-btn]:py-2 [&_.fi-btn]:rounded-none [&_.fi-btn]:shadow-none [&_.fi-btn]:text-lg',
        ])
	>
		<h2 class="text-4xl text-primary-500 font-semibold mb-4">KONTAKTANFRAGE</h2>
		<p class="mb-10 text-gray-700">Holen Sie sich jetzt unverbindlich und unkompliziert Details zum Gebäude und Ihren Mietkonditionen ein.</p>

		@livewire(\App\Livewire\Components\Portfolio\ContactForm::class, ['estateId' => $estateId])
	</div>
	<div class="col-span-1 space-y-10">
		@foreach ($page->estate->points_of_contact as $contact)
			<div class="max-w-md mx-auto bg-gray-50 text-primary-600 px-10 py-8 text-xl shadow-lg">
				@if ($contact->avatar)
					<img
						class="w-full rounded-full max-w-[13rem] mx-auto mb-10"
						src="{{ $contact->avatar->src }}"
						alt="Profilbild von {{ $contact->name }}"
					/>
				@endif

				<p class="font-semibold text-2xl">{{ $contact->name }}</p>
				<p class="font-normal  text-2xl mb-5">{{ $contact->position }}</p>

				@if ($contact->email)
					<div @class(['flex items-center'])>
						@svg('bi-envelope-open', 'w-3.5 h-4 mr-2 flex-shrink-0')
						<p class="truncate hover:opacity-70 transition-opacity">
							<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
						</p>
					</div>
				@endif

				@if ($contact->phone)
					<div @class(['flex items-center'])>
						@svg('bi-phone', 'w-3.5 h-3.5 mr-2 flex-shrink-0')
						<p class="truncate hover:opacity-70 transition-opacity">
							<a href="tel:{{ $contact->phone }}">
								{{ $contact->phone }}
							</a>
						</p>
					</div>
				@endif
			</div>
		@endforeach
	</div>
</x-adler::portfolio.container>
