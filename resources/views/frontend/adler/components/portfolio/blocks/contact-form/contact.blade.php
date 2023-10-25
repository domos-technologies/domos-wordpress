@props(['contact'])

<div class="max-w-md mx-auto bg-gray-50 text-primary-600 px-10 py-8 text-xl shadow-lg">
	@if ($contact->avatar)
		<img
			class="w-full rounded-full max-w-[13rem] mx-auto mb-10"
			src="{{ $contact->avatar->src }}"
			alt="Profilbild von {{ $contact->name }}"
		/>
	@endif

	<p class="font-semibold text-2xl">{{ $contact->name }}</p>
	<p class="font-normal text-2xl mb-5">{{ $contact->role }}</p>

	@if ($contact->email)
		<div @class(['flex items-center'])>
			<x-icons.email class="w-3.5 h-4 mr-2 flex-shrink-0" />
			<p class="truncate hover:opacity-70 transition-opacity">
				<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
			</p>
		</div>
	@endif

	@if ($contact->phone)
		<div @class(['flex items-center'])>
			<x-icons.phone class="w-3.5 h-3.5 mr-2 flex-shrink-0" />
			<p class="truncate hover:opacity-70 transition-opacity">
				<a href="tel:{{ $contact->phone }}">
					{{ $contact->phone }}
				</a>
			</p>
		</div>
	@endif
</div>
