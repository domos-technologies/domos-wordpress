@props(['estate', 'block'])

<?php
/** @var \SchemaImmo\Estate $estate */
/** @var \SchemaImmo\WebExpose\Block\SummaryBlock $block */
?>

<div class="bg-primary-50">
    <x-adler::portfolio.container
        class="py-28 grid grid-cols-1 justify-between gap-20 lg:grid-cols-12"
    >
        <div class="col-span-full lg:col-span-8 ">
            <div class="animate-in fade-in duration-700 slide-in-from-bottom mb-20 text-gray-600 prose prose-xl prose-a:text-inherit prose-h3:font-semibold prose-h3:text-2xl prose-headings:text-primary-600">
                {!! $block->summary !!}
            </div>

{{--            <x-ui.image-grid--}}
{{--                class="col-span-8 max-h-none rounded-lg"--}}
{{--                :images="$estate->images"--}}
{{--                max-height="md:h-[30rem]"--}}
{{--            />--}}
        </div>
        <div class="col-span-4 hidden lg:block">
            @if (count($estate->social->contacts) > 0)
                <x-adler::portfolio.section-heading
                    text="text-xl"
                    class="mb-6"
                >
                    Ihr Kontakt bei adler
                </x-adler::portfolio.section-heading>

                @foreach ($estate->social->contacts as $contact)
                    <x-adler::portfolio.contact.content
                        class="mb-14 text-primary-900"
                        :contact="$contact"
                    />
                @endforeach
            @endif

            @if ($estate->certifications->dgnb || $estate->certifications->co2_neutral)
                <x-adler::portfolio.section-heading
                    text="text-xl"
                    class="mb-6"
                >
                    <span>Zertifizierungen</span>
                </x-adler::portfolio.section-heading>

                <div class="grid grid-cols-5 gap-5 justify-between">
                    @if ($dgnb_certification = $estate->certifications->dgnb)
                        <x-icons.certifications.dgnb :certification="$dgnb_certification" width="w-full" />
                    @endif

                    @if ($estate->certifications->co2_neutral)
                        <x-icons.certifications.co2-neutral class="w-full" />
                    @endif
                </div>
            @endif
        </div>
    </x-adler::portfolio.container>
</div>
