<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use Domos\Core\EstatePost;
use Domos\Core\Exceptions\EstateNotFound;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;

/**
 * EstatePost::update() decides, per language, which WordPress fields get
 * written. The sync passes `null` for the default language, so "null" and "the
 * configured default language" have to mean the same thing here - otherwise
 * titles, descriptions and the searchable city/usage meta silently stop being
 * updated for the default language.
 */
final class EstatePostTest extends TestCase
{
	private const EXTERNAL_ID = '11111111-2222-4333-8444-555555555555';

	private int $postId;

	protected function setUp(): void
	{
		WpState::reset();

		WpState::instance()->options['immocore_default_language'] = 'de';
		WpState::instance()->options['domos_url'] = 'https://example.test';

		Fixtures::bootDomos();

		$this->postId = WpState::instance()->addPost([
			'post_type' => EstatePost::POST_TYPE,
			'post_status' => 'publish',
			'post_title' => 'Old title',
			'post_name' => 'old-slug',
		], [
			'domos_id' => self::EXTERNAL_ID,
			'estate_data' => Fixtures::estate()->toArray(),
		]);
	}

	public function test_get_estate_data_meta_key_treats_null_as_the_default_language(): void
	{
		self::assertSame('estate_data', EstatePost::getEstateDataMetaKey(null));
		self::assertSame('estate_data', EstatePost::getEstateDataMetaKey('de'));
		self::assertSame('estate_data_en', EstatePost::getEstateDataMetaKey('en'));
	}

	public function test_update_with_null_language_refreshes_the_default_language_fields(): void
	{
		$estate = Fixtures::estate(['name' => 'New title', 'slug' => 'new-slug']);

		EstatePost::update(self::EXTERNAL_ID, $estate, language: null);

		$saved = WpState::instance()->lastSavedPost();

		self::assertSame('New title', $saved['post_title'] ?? null);
		self::assertSame('new-slug', $saved['post_name'] ?? null);
		self::assertSame('<p>Description</p>', $saved['post_content'] ?? null);
		self::assertSame('Slogan', $saved['post_excerpt'] ?? null);
	}

	public function test_update_with_the_default_language_behaves_like_null(): void
	{
		EstatePost::update(self::EXTERNAL_ID, Fixtures::estate(['name' => 'New title']), language: 'de');

		self::assertSame('New title', WpState::instance()->lastSavedPost()['post_title'] ?? null);
	}

	public function test_update_with_null_language_refreshes_the_searchable_meta(): void
	{
		WpState::instance()->postMeta[$this->postId]['estate_city'] = 'Stale city';

		EstatePost::update(self::EXTERNAL_ID, Fixtures::estate(), language: null);

		self::assertSame('Berlin', WpState::instance()->metaValue($this->postId, 'estate_city'));
		self::assertSame('Deutschland', WpState::instance()->metaValue($this->postId, 'estate_country'));
	}

	public function test_update_with_null_language_stores_the_payload_under_the_default_meta_key(): void
	{
		EstatePost::update(self::EXTERNAL_ID, Fixtures::estate(['name' => 'New title']), language: null);

		self::assertSame('New title', WpState::instance()->metaValue($this->postId, 'estate_data')['name']);
	}

	public function test_update_with_a_translated_language_leaves_the_default_fields_alone(): void
	{
		WpState::instance()->postMeta[$this->postId]['estate_city'] = 'Berlin';

		EstatePost::update(self::EXTERNAL_ID, Fixtures::estate(['name' => 'English title']), language: 'en');

		$saved = WpState::instance()->lastSavedPost();

		self::assertArrayNotHasKey('post_title', $saved);
		self::assertArrayNotHasKey('post_content', $saved);
		self::assertArrayNotHasKey('post_excerpt', $saved);
		// Searchable meta describes the default language only.
		self::assertSame('Berlin', WpState::instance()->metaValue($this->postId, 'estate_city'));
		self::assertNotSame('English title', WpState::instance()->metaValue($this->postId, 'estate_data')['name']);
	}

	public function test_update_with_a_translated_language_stores_the_payload_under_a_language_meta_key(): void
	{
		EstatePost::update(self::EXTERNAL_ID, Fixtures::estate(['name' => 'English title']), language: 'en');

		self::assertSame('English title', WpState::instance()->metaValue($this->postId, 'estate_data_en')['name']);
		self::assertSame('Test Estate', WpState::instance()->metaValue($this->postId, 'estate_data')['name']);
	}

	public function test_update_throws_for_an_unknown_estate(): void
	{
		$this->expectException(EstateNotFound::class);

		EstatePost::update('does-not-exist', Fixtures::estate());
	}

	public function test_find_returns_null_when_no_post_holds_that_external_id(): void
	{
		self::assertNull(EstatePost::find('does-not-exist'));
	}

	public function test_find_resolves_a_post_by_its_external_id(): void
	{
		// EstatePost::$id is typed as a string even though it holds a post ID.
		self::assertSame((string) $this->postId, EstatePost::find(self::EXTERNAL_ID)?->id);
	}
}
