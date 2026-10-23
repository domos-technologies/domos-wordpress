<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * `bun run package` builds the zip that customers install, and a WordPress
 * plugin is served straight out of a web root - so everything in that zip is
 * downloadable over HTTP. Shipping the test suite or development tooling is not
 * acceptable.
 *
 * The release path is: rsync the repository (filtered by .distignore) into a
 * staging directory, run `composer install --no-dev` there, and zip the result.
 * vendor/ must therefore never be copied from the working tree, because that
 * copy contains phpunit and pint and its generated autoloader requires their
 * files on every single request.
 */
final class PackagingTest extends TestCase
{
	private const DISTIGNORE = __DIR__.'/../../.distignore';

	private const PACKAGE_JSON = __DIR__.'/../../package.json';

	private const COMPOSER_JSON = __DIR__.'/../../composer.json';

	/** @var string[] */
	private array $rules = [];

	protected function setUp(): void
	{
		self::assertFileExists(self::DISTIGNORE, 'The release build has no filter list.');

		$this->rules = $this->readRules();
	}

	public function test_distignore_excludes_the_test_suite_and_its_configuration(): void
	{
		self::assertContains('/tests', $this->rules);
		self::assertContains('/phpunit.xml', $this->rules);
		self::assertContains('/node_modules', $this->rules);
		self::assertContains('/.git', $this->rules);
	}

	/**
	 * Repository-level development files are not part of the plugin. AGENTS.md
	 * documents how to work in the repo and release.sh is the release tooling;
	 * neither belongs in a customer's plugin directory.
	 */
	public function test_distignore_excludes_repository_only_files(): void
	{
		foreach (['/AGENTS.md', '/release.sh', '/.github', '/.editorconfig'] as $rule) {
			self::assertContains($rule, $this->rules, "{$rule} would ship to customers.");
		}
	}

	public function test_distignore_excludes_the_working_tree_vendor_directory(): void
	{
		self::assertContains(
			'/vendor',
			$this->rules,
			'vendor/ must not be copied from the working tree; it holds the dev tooling and an autoloader that requires it.'
		);
	}

	public function test_distignore_keeps_the_manifest_the_release_build_needs(): void
	{
		self::assertNotContains(
			'/composer.json',
			$this->rules,
			'package:vendor runs composer install in the staging copy, so composer.json has to be there.'
		);
		self::assertNotContains(
			'/composer.lock',
			$this->rules,
			'Without the lockfile the release build could resolve different versions than the ones that were tested.'
		);
	}

	public function test_release_build_installs_dependencies_without_dev_packages(): void
	{
		$vendorStep = $this->scripts()['package:vendor'] ?? '';

		self::assertStringContainsString('--no-dev', $vendorStep, 'The release vendor tree must be built without dev packages.');
		self::assertStringContainsString('--working-dir=./plugin/domos-core', $vendorStep);
	}

	public function test_release_build_runs_the_vendor_step_before_zipping(): void
	{
		$steps = array_map(
			static fn (string $script): string => $script,
			$this->scripts()
		);

		$package = $steps['package'] ?? '';

		self::assertStringContainsString('package:vendor', $package);
		self::assertStringContainsString('package:rsync', $package);
		self::assertStringContainsString('package:zip', $package);
		self::assertLessThan(
			strpos($package, 'package:zip'),
			strpos($package, 'package:vendor'),
			'The zip must be built after the runtime dependencies are installed.'
		);
	}

	/**
	 * composer install leaves CLI entry points from the runtime dependencies
	 * (acorn, carbon, var-dump-server, patch-type-declarations) in vendor/bin.
	 * Nothing in the plugin or in any shipped package calls them, and a WordPress
	 * plugin sits in a web root, so they would just be more URL-addressable PHP.
	 */
	public function test_release_build_prunes_the_unused_composer_binaries(): void
	{
		$prune = $this->scripts()['package:prune'] ?? '';

		self::assertStringContainsString('./plugin/domos-core/vendor/bin', $prune);
		self::assertStringContainsString(
			'package:prune',
			$this->scripts()['package'] ?? '',
			'package:prune exists but the release build never runs it.'
		);
	}

	/**
	 * If the test framework ever moves from require-dev to require, --no-dev
	 * stops removing it and it silently ships.
	 */
	public function test_phpunit_is_a_development_dependency_only(): void
	{
		$composer = $this->json(self::COMPOSER_JSON);

		self::assertArrayHasKey('phpunit/phpunit', $composer['require-dev'] ?? []);
		self::assertArrayNotHasKey('phpunit/phpunit', $composer['require'] ?? []);
	}

	/** @return array<string, string> */
	private function scripts(): array
	{
		return $this->json(self::PACKAGE_JSON)['scripts'] ?? [];
	}

	/** @return array<string, mixed> */
	private function json(string $path): array
	{
		return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
	}

	/** @return string[] */
	private function readRules(): array
	{
		$rules = [];

		foreach (file(self::DISTIGNORE, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
			$line = trim($line);

			if ($line === '' || str_starts_with($line, '#')) {
				continue;
			}

			$rules[] = $line;
		}

		return $rules;
	}
}
