@props(['estate'])

@php
/** @var \SchemaImmo\Estate $estate */
@endphp

<x-adler::portfolio.container class="py-16 md:py-32 grid grid-cols-1 md:grid-cols-2 gap-16" id="contact-form">
	<div @class([
			'col-span-1',
			'[&_.fi-input-wrapper]:rounded-none [&_.fi-input-wrapper]:ring-0 [&_.fi-input-wrapper]:border-b-2 [&_.fi-input-wrapper]:border-primary-500 [&_.fi-input-wrapper]:shadow-none',
			'[&_.fi-input]:placeholder:text-gray-500 [&_.fi-input]:px-0 [&_.fi-input]:text-lg',
			'[&_.fi-fo-textarea]:ring-0 [&_.fi-fo-textarea]:shadow-none [&_.fi-fo-textarea]:placeholder:text-gray-500 [&_.fi-fo-textarea]:px-0 [&_.fi-fo-textarea]:text-lg [&_.fi-fo-textarea]:border-0 [&_.fi-fo-textarea]:!border-b-2 [&_.fi-fo-textarea]:!border-solid [&_.fi-fo-textarea]:border-primary-500 [&_.fi-fo-textarea]:rounded-none',
			'[&_.fi-btn]:bg-primary-500 [&_.fi-btn]:hover:bg-primary-600 [&_.fi-btn]:text-white [&_.fi-btn]:font-semibold [&_.fi-btn]:px-8 [&_.fi-btn]:py-2 [&_.fi-btn]:rounded-none [&_.fi-btn]:shadow-none [&_.fi-btn]:text-lg',
        ])
	>
		<h2 class="text-4xl expose-heading font-semibold mb-4">KONTAKTANFRAGE</h2>
		<p class="mb-10 expose-text-alt">Holen Sie sich jetzt unverbindlich und unkompliziert Details zum Gebäude und Ihren Mietkonditionen ein.</p>

		<x-adler::portfolio.blocks.contact-form.form :estate="$estate" />
	</div>
	<div class="col-span-1 space-y-10">
		@foreach ($estate->social->contacts as $contact)
			<x-adler::portfolio.blocks.contact-form.contact :contact="$contact" />
		@endforeach
	</div>
</x-adler::portfolio.container>
