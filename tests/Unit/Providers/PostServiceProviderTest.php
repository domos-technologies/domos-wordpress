<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Providers;

use Domos\Core\EstatePost;
use Domos\Core\Providers\PostServiceProvider;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;

/**
 * The `objekte` permastruct only exists in the stored rewrite rules if
 * something flushed them, and WordPress does not do that on activation or
 * update. Without that flush a fresh install serves a 404 for every estate URL
 * until someone happens to re-save Settings -> Permalinks.
 */
final class PostServiceProviderTest extends TestCase
{
	protected function setUp(): void
	{
		WpState::reset();
	}

	public function test_boot_registers_the_estate_post_type(): void
	{
		(new PostServiceProvider)->boot();

		$registered = WpState::instance()->registeredPostTypes;

		self::assertSame(EstatePost::POST_TYPE, $registered[0]['post_type']);
		self::assertSame('objekte', $registered[0]['args']['rewrite']['slug']);
		self::assertTrue($registered[0]['args']['publicly_queryable']);
	}

	public function test_boot_flushes_on_wp_loaded_rather_than_during_init(): void
	{
		(new PostServiceProvider)->boot();

		self::assertSame(0, WpState::instance()->rewriteRuleFlushes, 'The flush must not run while other plugins are still registering their rewrites.');
		self::assertContains('wp_loaded', WpState::instance()->actionHooks());

		WpState::instance()->runAction('wp_loaded');

		self::assertSame(1, WpState::instance()->rewriteRuleFlushes);
	}

	public function test_flush_is_soft_so_it_never_rewrites_server_config_files(): void
	{
		$flushes = [];

		// The plugin asks for a soft flush; record the argument WordPress receives.
		WpState::instance()->onRewriteRuleFlush = function (bool $hard) use (&$flushes): void {
			$flushes[] = $hard;
		};

		(new PostServiceProvider)->flushRewriteRulesOnce();

		self::assertSame([false], $flushes, 'A hard flush would rewrite .htaccess / web.config on admin requests.');
	}

	public function test_boot_flushes_rewrite_rules_for_a_new_plugin_version(): void
	{
		$provider = new PostServiceProvider;
		$provider->boot();

		WpState::instance()->runAction('wp_loaded');

		self::assertSame(1, WpState::instance()->rewriteRuleFlushes);
		self::assertSame(DOMOS_CORE_VERSION, WpState::instance()->options['immocore_rewrite_version']);
	}

	public function test_boot_does_not_flush_again_while_the_version_is_unchanged(): void
	{
		$provider = new PostServiceProvider;
		$provider->boot();

		$state = WpState::instance();
		$state->runAction('wp_loaded');
		$state->runAction('wp_loaded');
		$state->runAction('wp_loaded');

		self::assertSame(1, $state->rewriteRuleFlushes, 'Rewrite rules must not be regenerated on every request.');
	}

	public function test_flushes_again_after_the_plugin_is_updated(): void
	{
		$state = WpState::instance();
		$state->options['immocore_rewrite_version'] = '3.4.1';

		(new PostServiceProvider)->flushRewriteRulesOnce();

		self::assertSame(1, $state->rewriteRuleFlushes);
		self::assertSame(DOMOS_CORE_VERSION, $state->options['immocore_rewrite_version']);
	}

	/**
	 * The stored value is the marker for "rules are current", so a version that is
	 * not a non-empty string would make the check match on every request or never
	 * match at all.
	 */
	public function test_stored_version_is_a_readable_scalar(): void
	{
		(new PostServiceProvider)->flushRewriteRulesOnce();

		self::assertIsString(WpState::instance()->options['immocore_rewrite_version']);
		self::assertNotSame('', WpState::instance()->options['immocore_rewrite_version']);
	}
}
