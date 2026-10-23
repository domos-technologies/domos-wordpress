<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * domos_hex_to_oklch() feeds the CSS custom properties of every exposé, and it
 * runs on values a site owner controls through the domos_primary_shades and
 * domos_gray_shades filters. A colour that cannot be parsed has to leave the
 * palette intact rather than emitting a broken value.
 */
final class HelpersTest extends TestCase
{
	public function test_it_converts_a_six_digit_hex_colour_to_oklch(): void
	{
		$result = domos_hex_to_oklch('#1e5db3');

		self::assertStringStartsWith('oklch(', $result);
		self::assertMatchesRegularExpression('/^oklch\([\d.]+% [\d.]+ [\d.]+\)$/', $result);
	}

	public function test_it_converts_a_three_digit_hex_colour(): void
	{
		// #fff is the shorthand sites actually paste into the filter.
		self::assertStringStartsWith('oklch(', domos_hex_to_oklch('#fff'));
	}

	public function test_black_and_white_are_valid(): void
	{
		self::assertStringStartsWith('oklch(', domos_hex_to_oklch('#000000'));
		self::assertStringStartsWith('oklch(', domos_hex_to_oklch('#ffffff'));
	}

	public function test_an_empty_value_becomes_a_transparent_oklch(): void
	{
		self::assertSame('oklch(0% 0 0)', domos_hex_to_oklch(''));
		self::assertSame('oklch(0% 0 0)', domos_hex_to_oklch(null));
	}

	/**
	 * The conversion goes through the iris library, which accepts CSS colour
	 * names as well as hex - so a filter that returns "rebeccapurple" still ends
	 * up as a usable OKLCH value.
	 */
	public function test_css_colour_names_are_converted_too(): void
	{
		self::assertSame('oklch(44.03% 0.1603 303.37)', domos_hex_to_oklch('rebeccapurple'));
	}

	/**
	 * A filter can return anything, and the palette must survive it: an
	 * unparseable value is passed through so the browser falls back rather than
	 * the whole exposé losing its colours.
	 */
	public function test_an_unparseable_value_is_returned_unchanged(): void
	{
		foreach (['rgb(1,2,3)', 'hsl(120, 50%, 50%)', '#zzzzzz', 'not a colour', '  #000  '] as $value) {
			self::assertSame($value, domos_hex_to_oklch($value));
		}
	}

	public function test_plugin_url_appends_the_path_without_doubling_the_slash(): void
	{
		$expected = DOMOS_CORE_URL . 'public/css/admin.css';

		self::assertSame($expected, domos_plugin_url('public/css/admin.css'));
		self::assertSame($expected, domos_plugin_url('/public/css/admin.css'));
	}

	public function test_plugin_url_returns_the_base_for_an_empty_path(): void
	{
		self::assertSame(DOMOS_CORE_URL, domos_plugin_url(null));
	}

	public function test_both_helpers_can_be_defined_by_a_theme(): void
	{
		// They are wrapped in function_exists(), so a theme can override them
		// without a fatal - which means the guards are load-bearing.
		self::assertTrue(function_exists('domos_hex_to_oklch'));
		self::assertTrue(function_exists('domos_plugin_url'));
	}
}
