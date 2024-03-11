<?php

namespace Domos\Core;

use Domos\Core\Sync\DomosClient;
use Domos\Core\Sync\SyncManager;

class DOMOS
{
    protected ?string $url;

	public DomosClient $api;
    public SyncManager $sync;
    public URLResolver $urlResolver;
	public Options $options;

    public function __construct()
    {
		$this->options = new Options();
        $this->urlResolver = new URLResolver();
		$this->sync = new SyncManager();
		$this->api = new DomosClient($this->url());
    }

    public function url(): ?string
    {
        if (!isset($this->url)) {
            $this->url = $this->options->url->get();
        }

        return $this->url;
    }

	/**
	 * @return array<'50'|'100'|'200'|'300'|'400'|'500'|'600'|'700'|'800'|'900'|'950', string>
	 */
	public function getPrimaryShades(): array
	{
		$colors = [
			'50' => '#f4f7fb',
			'100' => '#e9eff7',
			'200' => '#c7d7ec',
			'300' => '#a5bee1',
			'400' => '#628eca',
			'500' => '#1e5db3',
			'600' => '#1b54a1',
			'700' => '#174686',
			'800' => '#12386b',
			'900' => '#0f2e58',
			'950' => '#091c36',
		];

		// Apply wordpress filter to allow overriding the colors
		$colors = apply_filters('domos_primary_shades', $colors);

		return $colors;
	}

	/**
	 * @return array<'50'|'100'|'200'|'300'|'400'|'500'|'600'|'700'|'800'|'900'|'950', string>
	 */
	public function getGrayShades(): array
	{
		$colors = [
			'50' => '#fafafa',
			'100' => '#f5f5f5',
			'200' => '#e5e5e5',
			'300' => '#d4d4d4',
			'400' => '#a3a3a3',
			'500' => '#737373',
			'600' => '#525252',
			'700' => '#404040',
			'800' => '#262626',
			'900' => '#171717',
			'950' => '#0a0a0a',
		];

		// Apply wordpress filter to allow overriding the colors
		$colors = apply_filters('domos_gray_shades', $colors);

		return $colors;
	}

	/**
	 * @return array<'color1'|'color2', string>
	 */
	public function getLottieColors(): array
	{
		$primaryShades = $this->getPrimaryShades();
		$lottieColors = [
			'color1' => $primaryShades['900'],
			'color2' => $primaryShades['500'],
		];

		// Apply wordpress filter to allow overriding the colors
		return apply_filters('domos_lottie_colors', $lottieColors);
	}

	public function isLightboxEnabled(): bool
	{
		$enabled = false;

		return apply_filters('domos_lightbox_enabled', $enabled);
	}

    public static self $instance;

    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
