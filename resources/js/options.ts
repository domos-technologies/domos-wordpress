import {
	AnyColorString,
	colorToRgb,
	LottieColor,
	makeLottieColor,
	type TailwindColor,
	type TailwindShade
} from "@/helpers/colors";

declare global {
	interface Window {
		DOMOS: {
			lightbox: boolean;
			colors: {
				primary: Record<TailwindShade, AnyColorString>;
				gray: Record<TailwindShade, AnyColorString>;

				lottie: {
					color1: AnyColorString;
					color2: AnyColorString;
				}
			}
		};
	}
}

export function isLightboxEnabled() {
	return window.DOMOS?.lightbox ?? false;
}

export function getPrimaryColors(): TailwindColor {
	const defaultColor = '#000';

	const primary = window.DOMOS?.colors?.primary ?? {};

	return {
		'50': colorToRgb(primary['50'] ?? defaultColor),
		'100': colorToRgb(primary['100'] ?? defaultColor),
		'200': colorToRgb(primary['200'] ?? defaultColor),
		'300': colorToRgb(primary['300'] ?? defaultColor),
		'400': colorToRgb(primary['400'] ?? defaultColor),
		'500': colorToRgb(primary['500'] ?? defaultColor),
		'600': colorToRgb(primary['600'] ?? defaultColor),
		'700': colorToRgb(primary['700'] ?? defaultColor),
		'800': colorToRgb(primary['800'] ?? defaultColor),
		'900': colorToRgb(primary['900'] ?? defaultColor),
		'950': colorToRgb(primary['950'] ?? defaultColor),
	};
}

export function getLottieColors(): { color1: LottieColor, color2: LottieColor } {
	const lottie = window.DOMOS?.colors?.lottie;

	const defaultColor = ('#000' as AnyColorString);
	const color1Hex = lottie?.color1 ?? defaultColor;
	const color2Hex = lottie?.color2 ?? defaultColor;

	const color1Rgb = colorToRgb(color1Hex);
	const color2Rgb = colorToRgb(color2Hex);

	const color1Lottie = makeLottieColor(color1Rgb);
	const color2Lottie = makeLottieColor(color2Rgb);

	return {
		color1: color1Lottie,
		color2: color2Lottie,
	};
}

