<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use Domos\Core\URLResolver;
use PHPUnit\Framework\TestCase;

/**
 * The sync endpoints are the plugin's contract with the immocore API. These
 * tests pin the exact routes so a change here is a deliberate one.
 */
final class URLResolverTest extends TestCase
{
	private URLResolver $urls;

	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://anh-hausbesitz.immocore.app';

		// A fresh DOMOS instance, so no option value is cached from a previous test.
		Fixtures::bootDomos();

		$this->urls = new URLResolver;
	}

	public function test_estate_ids_url_points_at_the_sync_v2_id_list(): void
	{
		self::assertSame(
			'https://anh-hausbesitz.immocore.app/api/sync/v2/estate-ids',
			$this->urls->estateSyncIdsUrl()
		);
	}

	public function test_estate_sync_one_url_targets_a_single_estate(): void
	{
		self::assertSame(
			'https://anh-hausbesitz.immocore.app/api/sync/v2/estates/abc-123',
			$this->urls->estateSyncOneUrl('abc-123')
		);
	}

	public function test_estate_sync_one_url_appends_the_language_query_when_given(): void
	{
		self::assertSame(
			'https://anh-hausbesitz.immocore.app/api/sync/v2/estates/abc-123?language=en',
			$this->urls->estateSyncOneUrl('abc-123', language: 'en')
		);
	}

	public function test_estate_sync_all_url_has_no_language_query_for_the_default_language(): void
	{
		self::assertSame(
			'https://anh-hausbesitz.immocore.app/api/sync/v2/estates',
			$this->urls->estateSyncAllUrl()
		);
	}

	public function test_estate_sync_all_url_appends_the_language_query_when_given(): void
	{
		self::assertSame(
			'https://anh-hausbesitz.immocore.app/api/sync/v2/estates?language=fr',
			$this->urls->estateSyncAllUrl(language: 'fr')
		);
	}
}
