@props([
    'contact',
    'nameClass' => 'text-lg font-semibold text-primary-600',
    'textClass' => 'text-base'
])

<?php /** @var \SchemaImmo\Contact $contact */ ?>

<div {{ $attributes->class(['grid grid-cols-4 gap-5']) }}>
    <div @class([
        'space-y-1',
        $textClass,
        'col-span-3' => $contact->avatar !== null,
        'col-span-full' => $contact->avatar === null,
    ])>
        <div>
			<span @class([$nameClass, 'block'])>{{ $contact->name }}</span>

			@if($contact->role)
				<span class="block truncate mb-3">{{ $contact->role }}</span>
			@endif
		</div>

        @if ($contact->email)
            <div @class(['flex items-center'])>
                <x-icons.email class="w-3.5 h-3.5 mr-2 flex-shrink-0" />

                <span class="block truncate">
                    <a
                        href="mailto:{{ $contact->email }}"
                    >
                        {{ $contact->email }}
                    </a>
                </span>
            </div>
        @endif

        @if ($contact->phone)
            <div @class(['flex items-center'])>
                <x-icons.phone class="w-3.5 h-3.5 mr-2 flex-shrink-0" />
                <span class="truncate">
                    <a href="tel:{{ $contact->phone }}">
                        {{ $contact->phone }}
                    </a>
                </span>
            </div>
        @endif

        @if ($address = $contact->address)
            <div @class(['pt-2 flex'])>
                <x-icons.building class="w-3.5 h-3.5 mr-2 mt-1.5 flex-shrink-0" />

                <div>
                    @if ($label = $address->label)
                        <span class="">{{ $label }}</span>
                    @endif

                    <span class="block">{{ $address->street }}</span>
                    <span class="block">{{ $address->postal_code }}, {{ $address->city }}</span>
                </div>
            </div>
        @endif
    </div>

	@if ($contact->avatar)
        <img
            class="col-span-1 rounded-full"
            src="{{ $contact->avatar->src }}"
            alt="Profilbild von {{ $contact->name }}"
        />
    @endif
</div>
