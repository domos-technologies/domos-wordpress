import Splide from '@splidejs/splide';

/*
 * DO NOT REMOVE THIS "EMPTY" ALPINE COMPONENT.
 *
 * This is because we use seperate slider implementations for domos-native web expose and the WordPress one.
 * We use the SAME HTML for both, but different JS implementations.
 *
 * Why? Well, Alpine does not 100% work correctly with shadow DOM, and the Splide slider does not work correctly
 * in the component lifecycle when used in a shadow DOM. So, we use a web component in the WordPress implementation
 * and a regular Alpine component in the domos-native web expose.
 *
 * BUT, we need to keep an empty component around to avoid causing errors when x-data="slider" is executed (as it's
 * part of the HTML). However the <splide-slider>-HTML is simply ignored by Alpine, so it's fine!.
 */
export function plugin(Alpine) {
    Alpine.data('slider', (name: string) => ({}));
}

// Web component version of the slider
class SplideSlider extends HTMLElement {
	constructor() {
		super();
	}

	connectedCallback() {
		console.log('SplideSlider connected');

		const splide = new Splide(this, {
			type: 'slide',
			perPage: 1,
			pagination: false,
		});

		splide.mount();
	}
}

customElements.define('splide-slider', SplideSlider);
