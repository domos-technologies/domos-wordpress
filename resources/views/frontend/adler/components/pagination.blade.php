<nav
	{{ $attributes }}
	x-data="{
		generatePagination(totalPages, currentPage) {
			let delta = 2;
			let range = [];
			let rangeWithDots = [];
			let l;

			for (let i = 1; i <= totalPages; i++) {
				if (i === 1 || i === totalPages || i >= currentPage - delta && i <= currentPage + delta) {
					range.push(i);
				}
			}

			for (let i of range) {
				if (l) {
					if (i - l === 2) {
						rangeWithDots.push(l + 1);
					} else if (i - l !== 1) {
						rangeWithDots.push('...');
					}
				}
				rangeWithDots.push(i);
				l = i;
			}

			return rangeWithDots;
		}
	}"
>
	<ul class="flex items-center text-sm leading-tight font-semibold bg-primary-100 border divide-x h-9 text-primary-500 divide-primary-200 border-primary-200">
		<li class="h-full">
			<a :href="`?page=1`"
			   class="relative inline-flex items-center h-full px-3 ml-0 group border-b-2 border-transparent transition-colors"
			   :class="{
				   'opacity-50 hover:text-primary-500': pagination.currentPage === 1,
				   'hover:text-primary-900 hover:border-primary-500': pagination.currentPage !== 1,
			   }"
			   @click.prevent="previousPage"
			   :disabled="pagination.currentPage === 1"
			>
				<x-icons.chevron-left class="w-4 h-4" />
			</a>
		</li>

		<template x-for="page in generatePagination(pagination.lastPage, pagination.currentPage)">
			<li class="h-full">
				<a href="#"
				   class="relative inline-flex items-center h-full px-3 group hover:text-primary-900 border-b-2 hover:border-primary-600 transition-colors"
				   :class="{
						'bg-primary-50 border-primary-600': page === pagination.currentPage,
						'border-transparent': page !== pagination.currentPage,
				   }"
				   x-text="page"
				   @click.prevent="if (page !== '...') { goToPage(page); }"
				>
					<span
						x-show="page === '...'"
						class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-primary-900 group-hover:border-l group-hover:border-r group-hover:border-primary-900 left-1/2 group-hover:left-0 group-hover:w-full"></span>
				</a>
			</li>
		</template>
{{--		<li class="hidden h-full md:block">--}}
{{--			<a href="#" class="relative inline-flex items-center h-full px-3 text-primary-900 group bg-gray-50">--}}
{{--				<span>1</span>--}}
{{--				<span--}}
{{--					class="box-content absolute bottom-0 left-0 w-full h-px -mx-px translate-y-px border-l border-r bg-primary-900 border-primary-900"></span>--}}
{{--			</a>--}}
{{--		</li>--}}
{{--		<li class="hidden h-full md:block">--}}
{{--			<a href="#" class="relative inline-flex items-center h-full px-3 group hover:text-primary-900">--}}
{{--				<span>2</span>--}}
{{--				<span--}}
{{--					class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-primary-900 group-hover:border-l group-hover:border-r group-hover:border-primary-900 left-1/2 group-hover:left-0 group-hover:w-full"></span>--}}
{{--			</a>--}}
{{--		</li>--}}
{{--		<li class="hidden h-full md:block">--}}
{{--			<a href="#" class="relative inline-flex items-center h-full px-3 group hover:text-primary-900">--}}
{{--				<span>3</span>--}}
{{--				<span--}}
{{--					class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-primary-900 group-hover:border-l group-hover:border-r group-hover:border-primary-900 left-1/2 group-hover:left-0 group-hover:w-full"></span>--}}
{{--			</a>--}}
{{--		</li>--}}
{{--		<li class="hidden h-full md:block">--}}
{{--			<div class="relative inline-flex items-center h-full px-2.5 group">--}}
{{--				<span>...</span>--}}
{{--			</div>--}}
{{--		</li>--}}
{{--		<li class="hidden h-full md:block">--}}
{{--			<a href="#" class="relative inline-flex items-center h-full px-3 group hover:text-primary-900">--}}
{{--				<span>6</span>--}}
{{--				<span--}}
{{--					class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-primary-900 group-hover:border-l group-hover:border-r group-hover:border-primary-900 left-1/2 group-hover:left-0 group-hover:w-full"></span>--}}
{{--			</a>--}}
{{--		</li>--}}
{{--		<li class="hidden h-full md:block">--}}
{{--			<a href="#" class="relative inline-flex items-center h-full px-3 group hover:text-primary-900">--}}
{{--				<span>7</span>--}}
{{--				<span--}}
{{--					class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-primary-900 group-hover:border-l group-hover:border-r group-hover:border-primary-900 left-1/2 group-hover:left-0 group-hover:w-full"></span>--}}
{{--			</a>--}}
{{--		</li>--}}
{{--		<li class="hidden h-full md:block">--}}
{{--			<a href="#" class="relative inline-flex items-center h-full px-3 group hover:text-primary-900">--}}
{{--				<span>8</span>--}}
{{--				<span--}}
{{--					class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-primary-900 group-hover:border-l group-hover:border-r group-hover:border-primary-900 left-1/2 group-hover:left-0 group-hover:w-full"></span>--}}
{{--			</a>--}}
{{--		</li>--}}
		<li class="h-full">
			<a :href="`?page=${pagination.lastPage}`"
			   class="relative inline-flex items-center h-full px-3 ml-0 group border-b-2 border-transparent transition-colors"
			   :class="{
				   'opacity-50 hover:text-primary-500': pagination.currentPage === pagination.lastPage,
				   'hover:text-primary-900 hover:border-primary-500': pagination.currentPage !== pagination.lastPage,
			   }"
			   @click.prevent="nextPage"
			   :disabled="pagination.currentPage === pagination.lastPage"
			>
				<x-icons.chevron-right class="w-4 h-4" />
			</a>
		</li>
	</ul>
</nav>
