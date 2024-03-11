const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
module.exports = {
	important: false,
	content: [
		'resources/views/frontend/**/*.blade.php',
		'resources/views/components/**/*.blade.php',
	],
	theme: {
		extend: {
			colors: {
				primary: {
					50: '#f4f7fb',
					100: '#e9eff7',
					200: '#c7d7ec',
					300: '#a5bee1',
					400: '#628eca',
					500: '#1e5db3',
					600: '#1b54a1',
					700: '#174686',
					800: '#12386b',
					900: '#0f2e58',
					950: '#091c36',
				}
			}
		},
	},
	plugins: [
		require('@tailwindcss/typography'),
		require('tailwindcss-animate'),
	],
}

