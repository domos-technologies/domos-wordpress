<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The release process is: bump the version, add a changelog section, tag, and
 * paste the changelog section into the GitHub release. The auto-updater
 * compares against the version in the plugin header, so these three places
 * have to agree or customers get the wrong thing - a stale cache in
 * WordPress's update check, or release notes that describe nothing.
 */
final class ReleaseMetadataTest extends TestCase
{
	private const PLUGIN_FILE = __DIR__.'/../../domos-core.php';

	private const CHANGELOG = __DIR__.'/../../CHANGELOG.md';

	public function test_plugin_header_version_matches_the_version_constant(): void
	{
		$source = (string) file_get_contents(self::PLUGIN_FILE);

		self::assertSame(1, preg_match('/^\s*\*\s*Version:\s*(\S+)/m', $source, $header), 'No Version: header found.');
		self::assertSame(1, preg_match("/define\\('DOMOS_CORE_VERSION',\\s*'([^']+)'\\)/", $source, $constant), 'No DOMOS_CORE_VERSION found.');

		self::assertSame(
			$header[1],
			$constant[1],
			'The plugin header and DOMOS_CORE_VERSION disagree. The WordPress update check reads the header, the plugin reads the constant.'
		);
	}

	public function test_changelog_has_a_section_for_the_current_version(): void
	{
		$changelog = (string) file_get_contents(self::CHANGELOG);

		self::assertMatchesRegularExpression(
			'/^## '.preg_quote(DOMOS_CORE_VERSION, '/').'\s*$/m',
			$changelog,
			'CHANGELOG.md has no "## '.DOMOS_CORE_VERSION.'" section to use as release notes.'
		);
	}

	/**
	 * The release instructions for this plugin are version-agnostic, so the
	 * changelog section has to be readable on its own.
	 */
	public function test_current_changelog_section_is_not_empty(): void
	{
		$changelog = (string) file_get_contents(self::CHANGELOG);

		self::assertSame(
			1,
			preg_match('/^## '.preg_quote(DOMOS_CORE_VERSION, '/').'\s*$(.+?)(?=^## |\z)/ms', $changelog, $matches),
			'Could not read the changelog section for the current version.'
		);

		self::assertNotSame('', trim($matches[1]), 'The changelog section for the current version is empty.');
	}
}
