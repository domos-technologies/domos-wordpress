<?php

namespace Domos\Core\Providers;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class UpdateServiceProvider implements Provider
{
	public function register()
	{

	}

	public function boot()
	{
		$updateChecker = PucFactory::buildUpdateChecker(
			'https://github.com/domos-technologies/domos-wordpress',
			DOMOS_CORE_ROOT_FILE,
			DOMOS_CORE_SLUG
		);

		//Set the branch that contains the stable release.
		$updateChecker->getVcsApi()->enableReleaseAssets();
	}
}
