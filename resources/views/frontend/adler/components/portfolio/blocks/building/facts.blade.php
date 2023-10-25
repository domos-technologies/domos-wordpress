@props([
	'facts' => [],
	'colSpan' => 'col-span-1',
	'size' => 'text-2xl',
	'color' => 'text-primary-600',
	'keySize' => 'text-base',
	'keyColor' => 'text-gray-500'
])

<div {{ $attributes->class(['grid grid-cols-2 gap-5 font-medium']) }}>

	@foreach($facts as $key => $value)
		<dl {{ $attributes->class(['col-span-1']) }}>
			<dd @class([$size, $color])>{{ $value ?? $slot }}</dd>
			<dt @class([$keySize, $keyColor])>{{ $key }}</dt>
		</dl>
	@endforeach

</div>
