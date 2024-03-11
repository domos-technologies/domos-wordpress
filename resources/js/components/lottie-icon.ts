import lottie from 'lottie-web';
import {getLottieColors} from "@/options";

async function fetchIconData(src: string): Promise<object> {
	const json = await fetch(src).then((res) => res.text());

	const colors = getLottieColors();

	const replaced = String(json)
		.replaceAll('["color1"]', JSON.stringify(colors.color1))
		.replaceAll('["color2"]', JSON.stringify(colors.color2));

	// style the console output with the given color
	// console.log('%cThis is a color1', primary800, `color: rgb(${primary800})`);
	// console.log('%cThis is a color2', primary400, `color: rgb(${primary400})`);

	return JSON.parse(replaced);
}

export default function lottieIcons(props: {
	src?: string;
	data?: any;
	autoplay: boolean;
	loop: boolean;
}) {
	return {
		initialized: false,
		src: props.src,
		data: props.data,

		init() {
			const container = this.$el.querySelector('.lottie-container');

			this.create(
				container,
				this.src,
				this.data,
				props.autoplay,
				props.loop
			);
		},

		async create(
			container,
			src?: string,
			data?: any,
			autoplay = false,
			loop = false
		) {
			if (!data) {
				data = await fetchIconData(src);
			}

			const animation = lottie.loadAnimation({
				container,
				renderer: 'svg',
				loop: loop,
				autoplay: autoplay,
				// path: src,
				animationData: data,

				// @ts-ignore
				onComplete() {
					animation.stop();
				},
			});

			const DURATION = 500;

			// get current time
			const currentTime = new Date().getTime();

			animation.addEventListener('DOMLoaded', () => {
				const time = new Date().getTime() - currentTime;

				// container.style.opacity = '1';
			});

			container.setAttribute('data-lottie-initialized', true);

			const animateBackToo = false;

			// On container hover
			container.addEventListener('mouseenter', () => {
				animation.setDirection(1);

				if (animation.isPaused && !animateBackToo)
					animation.goToAndPlay(0);
				else if (animation.isPaused && animateBackToo) animation.play();
			});

			if (animateBackToo) {
				container.addEventListener('mouseleave', () => {
					animation.setDirection(-1);
					if (animation.isPaused) animation.play();
				});
			}
		},
	};
}

export function plugin(Alpine) {
	Alpine.data('lottie', lottieIcons);
}
