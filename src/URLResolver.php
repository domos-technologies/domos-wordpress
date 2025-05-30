<?php

namespace Domos\Core;

use SchemaImmo\Estate\Certifications\DGNBCertification;

class URLResolver
{
    public function pluginUrl(string $path): string
    {
        $pluginUrl = DOMOS_CORE_URL;

        if ($path[0] === '/') {
            $path = substr($path, 1);
        }

        return $pluginUrl . $path;
    }

	public function externalWebExposeScriptUrl(): string
	{
		$domosUrl = DOMOS::instance()->url();

		return "{$domosUrl}/build/assets/web-expose.js";
	}

	public function externalWebExposeStyleUrl(): string
	{
		$domosUrl = DOMOS::instance()->url();

		return "{$domosUrl}/build/assets/web-expose.css";
	}


    public function privacyUrl(): string
    {
        return "/datenschutz";
    }

    public function dgnbCertificationIconUrl(DGNBCertification $certification): string
    {
        $domosUrl = DOMOS::instance()->url();

        return "{$domosUrl}/images/certifications/DGNB/{$certification->value}.png";
    }

    public function co2NeutralIconUrl(): string
    {
        $domosUrl = DOMOS::instance()->url();

        return "{$domosUrl}/images/certifications/domos/carbon-neutral.svg";
    }

	public function featureLottieIcon(string $featureType): string
	{
		$domosUrl = DOMOS::instance()->url();

		$type = FeatureType::tryFrom($featureType);

		if ($type) {
			$icon = $type->icon();
		} else {
			$icon = 'unknown';
		}

		return "{$domosUrl}/images/lottie/{$icon}.json.template";
	}
    public function estateMapUrl(string $estateId): string
    {
        $domosUrl = DOMOS::instance()->url();

        return "{$domosUrl}/api/embeds/v1/estate-map/{$estateId}";
    }

	public function estateSearchUrl(): string
	{
		$domosUrl = DOMOS::instance()->url();

		return "{$domosUrl}/api/sync/v2/search";
	}

	public function estateSyncOneUrl(string $id): string
	{
		$domosUrl = DOMOS::instance()->url();

		return "{$domosUrl}/api/sync/v2/estates/{$id}";
	}

	public function estateSyncAllUrl(): string
	{
		$domosUrl = DOMOS::instance()->url();

		return "{$domosUrl}/api/sync/v2/estates";
	}
}
