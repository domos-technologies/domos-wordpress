<div class="flex items-center text-primary-500">
    @svg('bi-search', 'absolute w-5 h-5 text-primary-500')
    <input
        x-data="{ value: @entangle($statePath).live }"
        @input.debounce.250ms="value = $event.target.value"
        autocomplete="off"
        type="search"
        class="flex-1 border-b-2 border-transparent border-b-gray-500 bg-transparent pl-10 text-2xl outline-none placeholder:text-gray-500 focus:border-transparent focus:border-b-primary-500 focus:outline-none focus:ring-0"
        placeholder="Objektname, Ort, oder PLZ"
    />
</div>

{{--<button--}}
{{--    class="group relative flex items-center overflow-hidden border-2 border-[#83E6EB] px-5 py-4 text-xl text-[#83E6EB] transition-colors duration-500 hover:text-white"--}}
{{-->--}}
{{--    <div--}}
{{--        class="absolute inset-0 -translate-x-full transform bg-[#83E6EB] transition-transform duration-500 ease-in-out group-hover:translate-x-0"--}}
{{--    ></div>--}}
{{--    Suchen--}}
{{--    @svg('bi-arrow-right', 'w-9 h-9 ml-3')--}}
{{--</button>--}}
