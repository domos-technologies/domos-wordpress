import lottie from 'lottie-web';
import {makeLottieColor, type LottieColor} from "@/helpers/colors";
import {getLottieColors} from "@/options";

function replaceColors(icon: string, color1: LottieColor, color2: LottieColor): string {
	return String(icon)
		.replaceAll('["color1"]', JSON.stringify(color1))
		.replaceAll('["color2"]', JSON.stringify(color2));
}

async function fetchIconData(src: string): Promise<object>
{
	const json = await fetch(src)
		.then((res) => res.text());

	const { color1, color2 } = getLottieColors();

	const lottieColor1: LottieColor = makeLottieColor(color1);
	const lottieColor2: LottieColor = makeLottieColor(color2);

	// console.log('Colors', {
	// 	color1,
	// 	color2,
	// 	lottieColor1,
	// 	lottieColor2,
	// });

	const replaced = replaceColors(json, lottieColor1, lottieColor2);

	return JSON.parse(replaced);
}

function lottieIcon(props: {
	src: string;
	autoplay: boolean;
	loop: boolean;
}) {
	return {
		initialized: false,
		src: props.src,

		init() {
			if (!this.$refs.container.getAttribute('data-lottie-initialized')) {
				this.create(
					this.$refs.container,
					this.src,
					props.autoplay,
					props.loop
				);
			}
		},

		async create(
			container,
			src?: string,
			autoplay = false,
			loop = false
		) {
			const data = await fetchIconData(src);

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

			container.setAttribute('data-lottie-initialized', true);

			const animateBackToo = false;

			// On container hover
			container.addEventListener('mouseenter', () => {
				animation.setDirection(1);

				if (animation.isPaused && !animateBackToo) {
					animation.goToAndPlay(0);
				} else if (animation.isPaused && animateBackToo) {
					animation.play();
				}
			});

			if (animateBackToo) {
				container.addEventListener('mouseleave', () => {
					animation.setDirection(-1);

					if (animation.isPaused) {
						animation.play();
					}
				});
			}
		},
	};
}

export function plugin(Alpine) {
	Alpine.data('lottie', lottieIcon);
}
