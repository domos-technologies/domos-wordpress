@props(['certification', 'width' => 'w-6'])

<?php
/** @var \SchemaImmo\Estate\Certifications\DGNBCertification $certification */

$src = \Domos\Core\DOMOS::instance()
        ->urlResolver
        ->dgnbCertificationIconUrl($certification);
?>

<img
    {{ $attributes->class([$width]) }}
    src="{{ $src }}"
    alt="DGNB {{ $certification->label() }} Zertifizierung"
    title="DGNB {{ $certification->label() }} Zertifizierung"
    loading="lazy"
/>
