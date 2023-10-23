@props([
    'message' => null,
    'accept' => null,
    'name',
    'cookie',
])

<?php

$privacy_url = \Domos\Core\DOMOS::instance()
    ->urlResolver
    ->privacyUrl();

?>

<article
    {{ $attributes->class('w-full h-full') }}
    x-data="{
        contents: @js(base64_encode($slot)),
        html: null,
        accepted: $persist(false).as(@js('domos.' . $cookie . '.accepted')),

        init() {
            this.html = atob(this.contents);
        },

        accept() {
            this.accepted = true;
        }
    }"
>
    <template x-if="!accepted">
        <div
            class="flex flex-col items-center justify-center bg-gray-100 h-full w-full "
        >
            <div class="flex flex-col items-center justify-center max-w-sm" x-cloak>
                @if ($message && $message instanceof \Illuminate\View\ComponentSlot)
                    {{ $message }}
                @else
                    <p class="mb-10 text-lg">
                        @if ($message)
                            {{ $message }}
                        @else
                            Um diesen externen Inhalt ({{ $name }}) zu laden, müssen Sie zuerst die dazugehörige <a href="{{ $privacy_url }}" class="hover:opacity-75 underline underline-offset-4">Datenschutzerklärung</a> lesen und akzeptieren.
                        @endif
                    </p>
                @endif

                @if ($accept && $accept instanceof \Illuminate\View\ComponentSlot)
                    {{ $accept }}
                @else
                    <button
                        @click.prevent="accept"
                        class="text-2xl bg-primary-600 hover:bg-primary-400 text-primary-50 px-4 py-2"
                    >
                        Akzeptieren & laden
                    </button>
                @endif
            </div>
        </div>
    </template>

    <template x-if="accepted">
        <div x-html="html"></div>
    </template>
</article>
