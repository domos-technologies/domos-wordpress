@props(['matterportIndex' => null, 'videoIndex' => null])

<nav class="absolute top-0 right-0 w-full mb-1 flex justify-end pointer-events-none">
    <div class="flex items-center justify-end w-fit shadow pointer-events-auto">
        @if ($matterportIndex !== null)
            <button
                class="rounded-bl-md text-gray-600 text-sm px-2 py-1 hover:text-gray-700 bg-primary-50/40 hover:bg-primary-50/80 transition-colors flex items-center space-x-2"
                @click="go({{ $matterportIndex }})"
                x-show="currentSliderIndex !== {{ $matterportIndex }}"
                x-transition:enter="transition transform ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-full"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition transform ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-full"
            >
                @svg('bi-badge-3d', 'mt-px')
                <span>Zum 3D-Scan</span>
            </button>
        @endif

        {{-- For now we hide the video button, and just regard it as an image --}}
        {{-- @if ($videoIndex !== null)
            <button
                @class([
                    'text-gray-600 text-sm px-2 py-1 hover:text-gray-700 bg-primary-50/40 hover:bg-primary-50/80 transition-colors flex items-center space-x-2',
                    'rounded-bl-md' => $matterportIndex === null,
                ])
                x-show="currentSliderIndex !== {{ $videoIndex }}"
                x-transition:enter="transition transform ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-full"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition transform ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-full"
                @click="go({{ $videoIndex }})"
            >
                @svg('bi-play-btn', 'mt-px')
                <span>Zum Video</span>
            </button>
        @endif --}}
    </div>
</nav>
