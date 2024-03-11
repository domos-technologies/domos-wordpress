<?php

namespace Domos\Core\Providers;

use Domos\Core\DOMOS;
use Domos\Core\EstatePost;
use Illuminate\Support\Facades\Blade;
use SchemaImmo\Rentable\Space\Type;

class FrontendServiceProvider implements Provider
{
    public function register()
    {
        add_filter('single_template', [EstatePost::class, 'template']);

        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts'], 100);

		add_shortcode('domos-list', [$this, 'renderListShortcode']);
    }

    public function boot()
    {
        Blade::directive('svg', function ($expression) {
            // read name and class from expression
            [$name, $class] = explode(',', $expression);

            // remove quotes
            $name = trim($name, "'\"");
            $class = trim($class, "'\"");
            $component = "components.icons.{$name}";

            if (view()->exists($component) === false) {
                return;
            }

            echo view($component, compact('class'))->render();
        });

//        dd(Blade::getCustomDirectives());
    }

    public function enqueueScripts()
    {
        global $post;

        if (!$post || $post->post_type !== EstatePost::POST_TYPE) {
            return;
        }

		$domos = DOMOS::instance();
        $url = $domos->urlResolver;


        wp_register_style('domos-frontend', $url->pluginUrl('public/css/frontend.css'), [], DOMOS_CORE_VERSION);
		wp_register_style('domos-frontend-external', $url->externalWebExposeStyleUrl(), [], DOMOS_CORE_VERSION);

		wp_register_script('domos-frontend--estate', $url->pluginUrl('public/js/estate.js'), [], DOMOS_CORE_VERSION, true);
//		wp_register_script('domos-frontend--estate-external', $url->externalWebExposeScriptUrl(), [], DOMOS_CORE_VERSION, true);

		// @see resources/js/options.ts
		wp_localize_script('domos-frontend--estate', 'DOMOS', [
			'lightbox' => $domos->isLightboxEnabled(),

			'colors' => [
				'primary' => $domos->getPrimaryShades(),
				'gray' => $domos->getGrayShades(),
				'lottie' => $domos->getLottieColors()
			]
		]);
    }

	public function renderListShortcode()
	{
		$domos = DOMOS::instance();
        $url = $domos->urlResolver;
		$options = $domos->options;

		$usages = array_map(function (string $value) {
			return Type::tryFrom($value);
		}, $options->usages->get() ?? []);

		$usages = array_filter($usages);

		return view('frontend.adler.shortcodes.list', [
			'cities' => $options->cities->get() ?? [],
			'usages' => $usages,
			'searchApiUrl' => $url->estateSearchUrl(),
			'jsUrl' => $url->pluginUrl('public/js/estate.js'),
			'cssUrl' => $url->pluginUrl('public/css/frontend.css')
		])->render();
	}
}
