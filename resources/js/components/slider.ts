import Splide from '@splidejs/splide';

export function plugin(Alpine) {
    Alpine.data('slider', (name: string) => ({
		splide: null,
		currentSliderIndex: 0,

		init() {
			/*
			 * TODO: reinvestigate slider timeout in the future
			 * 2024-03-07: This is a workaround for the issue where the slider is not
			 * initialized properly when the component is first rendered.
			 *
			 * The error: [splide] A track/list element is missing.
			 *
			 * This has to do with the slides being inside a shadow root for style encapsulation.
			 * However, the slider library is not aware of this and cannot find the slides for a little bit of time.
			 *
			 * In the future we can replace the slider or find a better solution. But tomorrow is launch day and this needs to ship.
			 */
			setTimeout(() => {
				this.splide = new Splide(this.$el, {
					type: 'slide',
					perPage: 1,
					pagination: false,
				});

				this.splide.on('move', (newIndex: number) => {
					this.currentSliderIndex = newIndex;
				});

				this.splide.mount();
			}, 800);
		},

		destroy() {
			this.splide.destroy();
		},

		go(to: number) {
			this.splide.go(to);
		},
	}));
}
