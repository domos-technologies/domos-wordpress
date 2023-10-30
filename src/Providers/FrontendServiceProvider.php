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
		wp_register_script('domos-frontend--estate', $url->pluginUrl('public/js/estate.js'), [], DOMOS_CORE_VERSION, true);

		// @see resources/js/options.ts
		wp_localize_script( 'domos-frontend--estate', 'DOMOS', [
			'lightbox' => $domos->isLightboxEnabled(),

			'colors' => [
				'primary' => $domos->getPrimaryShades(),
				'lottie' => $domos->getLottieColors()
			]
		]);

//		wp_localize_script('domos-frontend--estate', 'domos', [
//			'ajaxUrl' => admin_url('admin-ajax.php'),
//			'nonce' => wp_create_nonce('domos'),
//		]);
    }

	public function renderListShortcode()
	{
		$domos = DOMOS::instance();
        $url = $domos->urlResolver;
		$options = $domos->options;

		$usages = array_map(function (string $value) {
			return Type::from($value);
		}, $options->usages->get() ?? []);

		return view('frontend.adler.shortcodes.list', [
			'cities' => $options->cities->get() ?? [],
			'usages' => $usages,
			'searchApiUrl' => $url->estateSearchUrl(),
			'jsUrl' => $url->pluginUrl('public/js/estate.js'),
			'cssUrl' => $url->pluginUrl('public/css/frontend.css')
		])->render();
	}
}
