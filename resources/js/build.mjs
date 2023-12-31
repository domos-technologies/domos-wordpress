import * as esbuild from 'esbuild';

const isDev = process.argv.includes('--dev');

async function compile(options) {
	const context = await esbuild.context(options);

	if (isDev) {
		await context.watch();
	} else {
		await context.rebuild();
		await context.dispose();
	}
}

const defaultOptions = {
	define: {
		'process.env.NODE_ENV': isDev ? `'development'` : `'production'`,
	},
	bundle: true,
	mainFields: ['module', 'main'],
	platform: 'neutral',
	sourcemap: isDev ? 'inline' : false,
	sourcesContent: isDev,
	treeShaking: true,
	target: ['es2020'],
	minify: !isDev,
	plugins: [
		{
			name: 'watchPlugin',
			setup: function (build) {
				build.onStart(() => {
					console.log(
						`Build started at ${new Date(
							Date.now()
						).toLocaleTimeString()}: ${
							build.initialOptions.outdir
						}`
					);
				});

				build.onEnd((result) => {
					if (result.errors.length > 0) {
						console.log(
							`Build failed at ${new Date(
								Date.now()
							).toLocaleTimeString()}: ${
								build.initialOptions.outdir
							}`,
							result.errors
						);
					} else {
						console.log(
							`Build finished at ${new Date(
								Date.now()
							).toLocaleTimeString()}: ${
								build.initialOptions.outdir
							}`
						);
					}
				});
			},
		},
	],
};

compile({
	...defaultOptions,
	entryPoints: [
		// relative to the root of the project
		'./resources/js/pages/estate.ts',
	],
	outdir: './public/js',
});
