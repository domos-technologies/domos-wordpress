import lottie from 'lottie-web';

async function fetchIconData(src: string): Promise<object>
{
	const json = await fetch(src).then((res) => res.text());

	const style = getComputedStyle(document.documentElement);
	// get CSS variable
	let primary800 = style.getPropertyValue('--primary-800');
	let primary400 = style.getPropertyValue('--primary-400');

	if (primary800 === '') {
		primary800 = 'rgb(0,255,0)';
	}

	if (primary400 === '') {
		primary400 = 'rgb(255,0,0)';
	}


	const lottieRgba1 = [
		...primary800
			.replace('rgb(', '')
			.replace(')', '')
			.split(','),
	].map((v) => parseInt(v) / 255.0);

	const lottieRgba2 = [
		...primary400
			.replace('rgb(', '')
			.replace(')', '')
			.split(','),
	].map((v) => parseInt(v) / 255.0);

	console.log(lottieRgba1, lottieRgba2);

	// json_encode(['a' => 0, 'k' => $lottieRgba1, 'ix' => 3])
	const color1 = {
		a: 0,
		k: lottieRgba1,
		ix: 3,
	};

	const color2 = {
		a: 0,
		k: lottieRgba2,
		ix: 3,
	};

	const replaced = String(json)
		.replaceAll('["color1"]', JSON.stringify(color1))
		.replaceAll('["color2"]', JSON.stringify(color2));

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
			console.log('lottie init', data);

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
