import {colorToRgb, RGB, type TailwindColor, type TailwindShade} from "@/helpers/colors";
import {Brand} from "@/helpers/brand";

// hex, rgb, rgba
type AnyColorString = Brand<string, 'AnyColorString'>;

declare global {
	interface Window {
		DOMOS: {
			lightbox: boolean;
			colors: {
				primary: Record<TailwindShade, AnyColorString>;

				lottie: {
					color1: AnyColorString;
					color2: AnyColorString;
				}
			}
		};
	}
}

export function isLightboxEnabled() {
	return window.DOMOS && window.DOMOS.lightbox;
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

export function getLottieColors(): { color1: RGB, color2: RGB } {
	const lottie = window.DOMOS?.colors?.lottie;

	if (lottie) {
		return {
			color1: colorToRgb(lottie.color1),
			color2: colorToRgb(lottie.color2),
		};
	} else {
		const primary = getPrimaryColors();

		return {
			color1: primary['900'],
			color2: primary['500'],
		};
	}
}

