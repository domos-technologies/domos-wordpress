@props(['certification', 'width' => 'w-6'])

<?php
/** @var \SchemaImmo\Estate\Certifications\DGNBCertification $certification */

$src = \Domos\Core\DOMOS::instance()
        ->urlResolver
        ->co2NeutralIconUrl();
?>

<img
    {{ $attributes->class([$width]) }}
    src="{{ $src }}"
    alt="Das Objekt wird CO²-neutral betrieben"
    title="Das Objekt wird CO²-neutral betrieben"
    loading="lazy"
/>
