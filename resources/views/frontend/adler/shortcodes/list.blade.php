@props(['jsUrl', 'cssUrl', 'searchApiUrl', 'cities' => [], 'usages' => []])

<?php

$labelClass = 'block mb-2 text-sm font-bold text-primary-600';

?>

<link rel="stylesheet" href="{{ $cssUrl }}" />
<script src="{{ $jsUrl }}" defer type="module"></script>

<article
	class="domos-list max-w-7xl mx-auto px-5 md:px-10 lg:px-20 py-10"
	x-data="{
		error: null,
		loading: true,

		pagination: {
			currentPage: 1,
			lastPage: 1,
			total: 0,
		},
		estates: [],

		filter: {
			search: '',
			city: 'all',
			usage: 'all',
		},

		init() {
			// get page from URL query
			const url = new URL(window.location.href);
			const page = url.searchParams.get('domos_query_page');

			if (page !== null) {
				this.pagination.currentPage = parseInt(page);
			}

			// react when back button is pressed
			window.addEventListener('popstate', () => {
				const url = new URL(window.location.href);
				const page = url.searchParams.get('domos_query_page');

				if (page !== null) {
					this.pagination.currentPage = parseInt(page);
				} else {
					this.pagination.currentPage = 1;
				}

				this.search();
			});

			this.$watch('filter', () => {
				this.updatePage(1);
			}, { deep: true });

			this.search();
		},

		async search() {
			this.error = null;
			this.loading = true;

			const baseUrl = '{{ $searchApiUrl }}';
			const url = new URL(baseUrl);

			if (this.filter.search !== '') {
				url.searchParams.set('full_text', this.filter.search);
			}

			if (this.filter.city !== 'all') {
				url.searchParams.set('city', this.filter.city);
			}

			if (this.filter.usage !== 'all') {
				url.searchParams.set('usage', this.filter.usage);
			}

			if (this.pagination.currentPage !== 1) {
				url.searchParams.set('page', this.pagination.currentPage);
			}

			const response = await fetch(url, {
				headers: {
					'Accept': 'application/json',
				}
			});

			if (!response.ok) {
				console.error(response);

				this.error = 'Bei der Suche ist leider ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.';
				this.loading = false;

				return;
			}

			const json = await response.json();

			this.estates = json.data;
			this.pagination = {
				currentPage: json.pagination.current_page,
				lastPage: json.pagination.last_page,
				total: json.pagination.total,
			};
			this.loading = false;
		},

		nextPage() {
			if (this.pagination.currentPage === this.pagination.lastPage) {
				return;
			}

			this.updatePage(this.pagination.currentPage + 1);
			this.search();
		},

		previousPage() {
			if (this.pagination.currentPage === 1) {
				return;
			}

			this.updatePage(this.pagination.currentPage - 1);
			this.search();
		},

		goToPage(page) {
			this.updatePage(page);
			this.search();
		},

		updatePage(page) {
			this.pagination.currentPage = page;

			// update URL query
			const url = new URL(window.location.href);

			if (page === 1) {
				url.searchParams.delete('domos_query_page');
			} else {
				url.searchParams.set('domos_query_page', page);
			}

			// push new URL to history
			window.history.pushState({}, '', url);
		},
	}"
>
	<iframe
		src="{{ \Domos\Core\DOMOS::instance()->url() }}/api/embeds/v1/world-map"
		class="w-full aspect-video border-0 mb-10"
	></iframe>

	<header class="mb-20">
		<form
			class="grid md:grid-cols-4 gap-10 items-center"
			@submit.prevent="search"
		>
			<div class="col-span-2 flex flex-col">
				<label for="search" @class([$labelClass])>
					Suche
				</label>
				<input
					id="search"
					name="search"
					type="search"
					placeholder="Suche..."
					class="mb-0"
					x-model="filter.search"
					@input.debounce.500="search"
				/>
			</div>

			<div class="col-span-1">
				<label for="filter_city" @class([$labelClass])>
					Stadt
				</label>
				<select
					id="filter_city"
					x-model="filter.city"
					@change="search"
				>
					<option value="all">Alle</option>

					@foreach ($cities as $city)
						<option value="{{ $city }}">{{ $city }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-span-1">
				<label for="filter_usage" @class([$labelClass])>
					Nutzung
				</label>
				<select
					id="filter_usage"
					x-model="filter.usage"
					@change="search"
				>
					<option value="all">Alle</option>

					@foreach ($usages as $type)
						<option value="{{ $type->value }}">{{ $type->label() }}</option>
					@endforeach
				</select>
			</div>
		</form>

		<div x-show="error" class="domos-list-error mt-5 text-red-500" x-text="error"></div>
	</header>

	<div
		class="domos-list-loader w-full flex items-center justify-center"
		:class="{
			'hidden': !loading,
		}"
	>
		<x-icons.loader class="animate-spin w-20 h-20 opacity-10" />
	</div>

	<template x-if="!loading && !error">
		<div>
			<div class="domos-list-estates grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mb-12">
				<template x-for="estate in estates">
					<a
						:key="estate.id"

						@class([
							'domos-list-estate group w-full flex flex-col transition-opacity',
							'font-semibold text-left',
						])
						:href="`/objekte/${estate.slug}`"
					>
						<figure class="domos-list-estate-thumbnail relative w-full mb-5 min-h-[12rem] bg-primary-100" style="aspect-ratio: 4/3; margin-bottom: 1.25rem;">
							<template x-if="estate.media.thumbnail">
								<img
									class="absolute !h-full w-full object-cover object-center group-hover:opacity-80"
									:src="estate.media.thumbnail.thumbnailSrc ?? estate.media.thumbnail.src"
									:alt="estate.media.thumbnail.alt"
									loading="lazy"
									{{-- sometimes Wordpress plugins like elementor use custom CSS which fucks up image height --}}
									style="height: 100% !important;"
								/>
							</template>
						</figure>

						<div class="domos-list-estate-city text-gray-500 text-base uppercase mb-1 group-hover:opacity-80" x-text="estate.address.city"></div>
						<div
							class="domos-list-estate-name text-2xl line-clamp-2"
							:class="{
								'mb-1': estate.texts.slogan,
							}"
							x-text="estate.name"
						></div>

						<div
							x-show="estate.texts.slogan"
							class="domos-list-estate-slogan text-sm opacity-50"
							x-text="estate.texts.slogan"
						></div>
					</a>
				</template>
			</div>

			<div class="domos-list-pagination w-full flex items-center justify-center">
				<x-adler::pagination />
			</div>
		</div>
	</template>
</article>
