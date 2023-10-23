@props(['text' => 'text-3xl'])

<h2 {{ $attributes->class(['text-primary-600 uppercase font-semibold border-b-2 border-gray-300 pb-2 flex items-center justify-between', $text]) }}>
    {{ $slot}}
</h2>
