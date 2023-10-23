@props(['type'])

<?php
	$domos = \Domos\Core\DOMOS::instance();
	$url = $domos->urlResolver->featureLottieIcon($type);
?>

<figure
	{{ $attributes->class('') }}
	x-data="lottie({ src: '{{ $url }}' })"
>
    <div x-ref="container" ></div>
</figure>
