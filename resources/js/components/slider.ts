import Splide from '@splidejs/splide';

export function plugin(Alpine) {
    Alpine.data('slider', (name: string) => ({
        splide: null,
        currentSliderIndex: 0,

        init() {
            this.splide = new Splide(this.$el, {
                type: 'loop',
                perPage: 1,
                pagination: false,
            });

            this.splide.on('move', (newIndex: number) => {
                this.currentSliderIndex = newIndex;
            });

            this.splide.mount();
        },

        go(to: number) {
            this.splide.go(to);
        }
    }));
}
