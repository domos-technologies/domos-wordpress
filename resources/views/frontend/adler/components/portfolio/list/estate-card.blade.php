@props(['estate'])

<a href="{{ $estate->url }}" {{ $attributes->class(['relative block group']) }}>
    <div
        class="relative w-full h-full overflow-hidden min-h-[12rem]"
    >
        <img
            src="{{ $estate->thumbnail?->thumbnailSrc }}"
            class="absolute -z-10 h-full w-full object-cover object-center group-hover:opacity-90 transition-opacity"
            loading="lazy"
        >

        <div class="absolute m-5 shadow left-0 top-0 bg-primary-500 font-semibold text-primary-50 px-3 py-1">
            {{ $estate->status }}
        </div>

        <x-portfolio.list.estate-card.usages
            class="absolute right-0 top-0 text-white m-5"
			usage-class="space-x-0 mb-1"
			usage-text-class="bg-primary-700 px-2 items-center flex h-6"
			usage-icon-class="bg-primary-500 p-1 w-6 h-6"
            :usages="$estate->usages"
        />
    </div>


    <div class="flex flex-col mt-3 font-semibold">
        <span class="uppercase tracking-wider">{{ $estate->location }}</span>
        <span class="text-2xl">{{ $estate->name }}</span>
    </div>
</a>
