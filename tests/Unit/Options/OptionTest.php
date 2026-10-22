<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Options;

use Domos\Core\Options\CitiesOption;
use Domos\Core\Options\DefaultLanguageOption;
use Domos\Core\Options\LanguagesOption;
use Domos\Core\Options\TokenOption;
use Domos\Core\Options\UrlOption;
use Domos\Core\Options\UsagesOption;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OptionTest extends TestCase
{
	protected function setUp(): void
	{
		WpState::reset();
	}

	/**
	 * Renaming an option key silently orphans every customer's stored settings,
	 * so the exact names are part of the plugin's contract.
	 *
	 * @return array<string, array{0: class-string, 1: string}>
	 */
	public static function optionNames(): array
	{
		return [
			'API URL' => [UrlOption::class, 'domos_url'],
			'API token' => [TokenOption::class, 'domos_token'],
			'cities filter list' => [CitiesOption::class, 'domos_cities'],
			'usages filter list' => [UsagesOption::class, 'domos_usages'],
			'default language' => [DefaultLanguageOption::class, 'immocore_default_language'],
			'synced languages' => [LanguagesOption::class, 'immocore_languages'],
		];
	}

	/**
	 * @param class-string $optionClass
	 */
	#[DataProvider('optionNames')]
	public function test_option_reads_the_expected_wordpress_option_name(string $optionClass, string $name): void
	{
		WpState::instance()->options[$name] = 'stored-value';

		self::assertSame('stored-value', (new $optionClass)->fresh(), "{$optionClass} did not read {$name}");
	}

	public function test_option_falls_back_to_its_default_when_unset(): void
	{
		self::assertSame('de', (new DefaultLanguageOption)->get());
		self::assertSame([], (new LanguagesOption)->get());
		self::assertNull((new CitiesOption)->get());
		self::assertNull((new UrlOption)->get());
	}

	public function test_set_persists_the_value_for_later_requests(): void
	{
		(new LanguagesOption)->set(['en', 'fr']);

		self::assertSame(['en', 'fr'], WpState::instance()->options['immocore_languages']);
		// fresh() simulates the next request, which reads from the database.
		self::assertSame(['en', 'fr'], (new LanguagesOption)->fresh());
	}

	public function test_set_replaces_an_existing_value_instead_of_adding_a_second_option(): void
	{
		$option = new DefaultLanguageOption;
		$option->set('de');
		$option->set('en');

		self::assertSame('en', WpState::instance()->options['immocore_default_language']);
	}

	public function test_url_option_accepts_an_absolute_url(): void
	{
		(new UrlOption)->set('https://anh-hausbesitz.immocore.app');

		self::assertSame('https://anh-hausbesitz.immocore.app', (new UrlOption)->fresh());
	}

	public function test_url_option_rejects_a_bare_hostname_and_writes_nothing(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Invalid value');

		try {
			(new UrlOption)->set('anh-hausbesitz.immocore.app');
		} finally {
			self::assertArrayNotHasKey('domos_url', WpState::instance()->options);
		}
	}

	public function test_url_option_rejects_null(): void
	{
		$this->expectException(\Exception::class);

		(new UrlOption)->set(null);
	}

	public function test_token_option_accepts_null_so_the_token_can_be_cleared(): void
	{
		WpState::instance()->options['domos_token'] = 'old-token';

		(new TokenOption)->set(null);

		self::assertNull((new TokenOption)->fresh());
	}

	public function test_token_option_rejects_non_string_values(): void
	{
		$this->expectException(\Exception::class);

		(new TokenOption)->set(['not-a-string']);
	}
}
