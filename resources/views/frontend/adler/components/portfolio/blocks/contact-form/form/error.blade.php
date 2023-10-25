@props(['error'])

<p
	x-cloak
	x-show="{{ $error }}"
	x-text="{{ $error }}"
	class="text-red-600 text-sm font-medium mb-10"
></p>
