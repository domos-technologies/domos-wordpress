<?php
/** @var \SchemaImmo\Estate $estate */
?>

<div
	class="relative bg-center bg-cover bg-primary-50"
	style="background-image: url('$bgSrc');"
>
    <div class="max-w-7xl mx-auto relative flex items-center p-2 sm:p-10 md:px-0 md:py-24">
		<div class="bg-gray-50/70 w-full md:w-2/3 lg:w-1/2 p-8 text-primary-500 ">
            <?php if ($estate->logo): ?>
                <img
                    src="{{ $page->estate->logo->src }}"
                    alt="{{ $page->estate->logo->alt }}"
                    class="max-w-sm h-20 md:h-auto mb-8 animate-in fade-in duration-700 slide-in-from-bottom"
                />
            <?php endif; ?>
            <h1 class="animate-in fade-in duration-700 slide-in-from-bottom text-3xl md:text-5xl/tight font-bold mb-4">{{ $page->estate->slogan ?? $page->estate->name }}</h1>
            <p class="animate-in fade-in duration-700 slide-in-from-bottom text-2xl md:text-4xl/tight whitespace-pre-wrap">{{ $page->estate->address->formatted }}</p>
        </div>

    </div>
</div>
