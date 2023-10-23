@props(['block'])

<div
    class="border-y-4"
    x-data="{ open: 'json' }"
>
    <nav>
        <button
            @click="open = 'json'"
            :class="{
                'bg-primary-600 text-white': open === 'json',
                'bg-primary-50 text-primary-600': open !== 'json',
            }"
        >
            JSON
        </button>
        <button
            @click="open = 'var_dump'"
            :class="{
                '!bg-primary-600 text-white': open === 'var_dump',
                '!bg-primary-50 text-primary-600': open !== 'var_dump'
            }"
        >
            var_dump
        </button>
    </nav>
    <pre x-show="open === 'json'">@json($block->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
    <pre x-show="open === 'var_dump'"><?php var_dump($block); ?></pre>
</div>

