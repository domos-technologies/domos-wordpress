import {Brand} from "@/helpers/brand";

export type RGB = Brand<[number, number, number], 'RGB'>;
export type LottieRGB = Brand<[number, number, number], 'LottieRGB'>;
export type LottieColor = {
	a: 0,
	k: LottieRGB,
	ix: 3,
};

export type TailwindShade = '50'|'100'|'200'|'300'|'400'|'500'|'600'|'700'|'800'|'900'|'950';
export type TailwindColor = { [key in TailwindShade]: RGB; };

// export function getCSSVariable(colorName: TailwindColorName = 'primary', shade: TailwindShade = '600'): RGB|null
// {
// 	const style = getComputedStyle(document.documentElement);
// 	const color = style.getPropertyValue(`--${colorName}-${shade}`);
//
// 	if (color === null || color === '') {
// 		return null;
// 	}
//
// 	return colorToRgb(color);
// }

export function colorToRgb(color: string): RGB {
	if (color.startsWith('#')) {
		return hexToRgb(color);
	} else if (color.startsWith('rgba(')) {
		return rgbaStringToRgb(color);
	} else if (color.startsWith('rgb(')) {
		return rgbStringToRgb(color);
	} else {
		throw new Error('Invalid color format: ' + color);
	}
}

export function rgbToLottieRgb(rgb: number[]): LottieRGB {
	return rgb
		.slice(0, 3)
		.map((v) => v / 255.0) as LottieRGB;
}

function hexToRgb(hex: string): RGB {
	hex = hex.replace(/^#/, '');

	const bigint = parseInt(hex, 16);
	return [(bigint >> 16) & 255, (bigint >> 8) & 255, bigint & 255] as RGB;
}

function rgbStringToRgb(rgb: string): RGB {
	return rgb
		.replace('rgb(', '')
		.replace(')', '')
		.split(',')
		.slice(0, 3)
		.map((v) => parseInt(v)) as RGB;
}

function rgbaStringToRgb(rgba: string): RGB {
	return rgba
		.replace('rgba(', '')
		.replace(')', '')
		.split(',')
		.slice(0, 3)
		.map((v) => parseInt(v)) as RGB;
}

export function makeLottieColor(rgb: RGB): LottieColor {
	return {
		a: 0,
		k: rgbToLottieRgb(rgb),
		ix: 3,
	};
}
