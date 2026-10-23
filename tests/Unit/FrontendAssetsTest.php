<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use Domos\Core\EstatePost;
use Domos\Core\Providers\FrontendServiceProvider;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;
use WP_Post;

/**
 * The exposition bundle is only useful on an estate page. Registering it on
 * every page would load 400 KB of JavaScript and a stylesheet into a customer's
 * entire site, and not registering it at all leaves every exposé unstyled.
 */
final class FrontendAssetsTest extends TestCase
{
	private string|false $originalPost = false;

	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://example.immocore.app';
		WpState::instance()->options['immocore_default_language'] = 'de';

		Fixtures::bootDomos();

		$this->originalPost = $GLOBALS['post'] ?? false;
	}

	protected function tearDown(): void
	{
		$GLOBALS['post'] = $this->originalPost === false ? null : $this->originalPost;
	}

	private function withCurrentPost(?WP_Post $post): void
	{
		$GLOBALS['post'] = $post;
	}

	public function test_nothing_is_registered_on_a_regular_page(): void
	{
		$this->withCurrentPost(new WP_Post(['ID' => 5, 'post_type' => 'page']));

		(new FrontendServiceProvider)->enqueueScripts();

		self::assertSame([], WpState::instance()->registeredScripts);
		self::assertSame([], WpState::instance()->registeredStyles);
	}

	public function test_nothing_is_registered_without_a_current_post(): void
	{
		$this->withCurrentPost(null);

		(new FrontendServiceProvider)->enqueueScripts();

		self::assertSame([], WpState::instance()->registeredScripts);
	}

	public function test_the_bundle_is_registered_on_an_estate_page(): void
	{
		$this->withCurrentPost(new WP_Post(['ID' => 7, 'post_type' => EstatePost::POST_TYPE]));

		(new FrontendServiceProvider)->enqueueScripts();

		$state = WpState::instance();

		self::assertArrayHasKey('domos-frontend--estate', $state->registeredScripts);
		self::assertArrayHasKey('domos-frontend', $state->registeredStyles);
		self::assertStringEndsWith('public/js/estate.js', $state->registeredScripts['domos-frontend--estate']['src']);
		self::assertStringEndsWith('public/css/frontend.css', $state->registeredStyles['domos-frontend']['src']);
		self::assertTrue($state->registeredScripts['domos-frontend--estate']['in_footer']);
	}

	public function test_the_theme_can_override_the_palette_through_the_documented_filters(): void
	{
		$this->withCurrentPost(new WP_Post(['ID' => 7, 'post_type' => EstatePost::POST_TYPE]));

		add_filter('domos_primary_shades', fn () => array_fill_keys(
			['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'],
			'#123456',
		));

		(new FrontendServiceProvider)->enqueueScripts();

		$localized = WpState::instance()->localizedScripts['domos-frontend--estate'];

		self::assertSame('DOMOS', $localized['name']);
		self::assertSame('#123456', $localized['data']['colors']['primary']['500']);
		self::assertArrayHasKey('gray', $localized['data']['colors']);
		self::assertArrayHasKey('lottie', $localized['data']['colors']);
		self::assertArrayHasKey('lightbox', $localized['data']);
	}

	public function test_the_estate_search_uses_the_configured_instance(): void
	{
		// The shortcode hands this URL to the front-end search, so it has to point
		// at the customer's instance and not at a hardcoded host.
		self::assertSame(
			'https://example.immocore.app/api/sync/v2/search',
			\Domos\Core\DOMOS::instance()->urlResolver->estateSearchUrl(),
		);
	}
}
