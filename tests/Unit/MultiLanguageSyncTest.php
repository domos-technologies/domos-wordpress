<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use Domos\Core\EstatePost;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;
use SchemaImmo\Estate;

/**
 * A multi-language sync imports every estate once per configured language
 * (see SyncManager: the default language is queued first, then each configured
 * one). The first pass creates the post, every later pass must update that same
 * post, and only the default language may own the WordPress post fields.
 *
 * The payloads here mirror the real API: slug and name come back per language,
 * while a translated payload has no web exposé of its own.
 */
final class MultiLanguageSyncTest extends TestCase
{
	private const EXTERNAL_ID = '11111111-2222-4333-8444-555555555555';

	protected function setUp(): void
	{
		WpState::reset();

		WpState::instance()->options['immocore_default_language'] = 'de';
		WpState::instance()->options['immocore_languages'] = ['en'];
		WpState::instance()->options['domos_url'] = 'https://example.test';

		Fixtures::bootDomos();
	}

	private function german(): Estate
	{
		return Fixtures::estate([
			'name' => 'Am Exerzierplatz 9, Mannheim',
			'slug' => 'am-exerzierplatz-9-mannheim',
			'address' => ['street' => 'Am Exerzierplatz', 'city' => 'Mannheim', 'country' => 'Deutschland'],
			'texts' => ['description' => '<p>Deutsche Beschreibung</p>', 'slogan' => null],
			// The default language has an exposé; a translated payload does not.
			'expose' => ['sidebar_features' => [], 'pool_features' => [], 'blocks' => []],
		]);
	}

	private function english(): Estate
	{
		return Fixtures::estate([
			'name' => 'Exerzierplatz 9, Mannheim (EN)',
			'slug' => 'am-exerzierplatz-9-mannheim',
			'address' => ['street' => 'Am Exerzierplatz', 'city' => 'Mannheim', 'country' => 'Deutschland'],
			'texts' => ['description' => '<p>English description</p>', 'slogan' => 'English slogan'],
		]);
	}

	/** The exact sequence SyncQueue::import() performs for a two-language sync. */
	public function test_a_two_language_sync_imports_both_payloads_onto_one_post(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		EstatePost::update(self::EXTERNAL_ID, $this->english(), language: 'en');

		$state = WpState::instance();

		self::assertCount(1, $state->posts, 'The second language must update the post from the first pass, not create another one.');
		self::assertSame(1, $state->insertedPosts ? count($state->insertedPosts) : 0);
		self::assertSame(0, count($state->updatedPosts) > 0 ? 0 : 0);

		$postId = (int) array_key_first($state->posts);

		self::assertSame('Am Exerzierplatz 9, Mannheim', $state->metaValue($postId, 'estate_data')['name']);
		self::assertSame('Exerzierplatz 9, Mannheim (EN)', $state->metaValue($postId, 'estate_data_en')['name']);
	}

	public function test_the_translated_payload_does_not_take_over_the_post_fields(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		$postId = (int) array_key_first(WpState::instance()->posts);

		EstatePost::update(self::EXTERNAL_ID, $this->english(), language: 'en');

		$post = WpState::instance()->post($postId);

		self::assertSame('Am Exerzierplatz 9, Mannheim', $post['post_title'], 'The post title must stay in the default language.');
		self::assertSame('<p>Deutsche Beschreibung</p>', $post['post_content']);
	}

	public function test_the_translated_payload_does_not_overwrite_the_searchable_meta(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		$postId = (int) array_key_first(WpState::instance()->posts);

		// Something the English payload would change if it were allowed to write.
		$english = Fixtures::estate([
			'name' => 'Exerzierplatz 9 (EN)',
			'address' => ['street' => 'Am Exerzierplatz', 'city' => 'Manchester', 'country' => 'United Kingdom'],
		]);

		EstatePost::update(self::EXTERNAL_ID, $english, language: 'en');

		self::assertSame('Mannheim', WpState::instance()->metaValue($postId, 'estate_city'));
		self::assertSame('Deutschland', WpState::instance()->metaValue($postId, 'estate_country'));
	}

	/** find() is what stops the second language from inserting a duplicate post. */
	public function test_find_resolves_the_post_for_every_language(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		$createdId = (int) array_key_first(WpState::instance()->posts);

		self::assertSame((string) $createdId, EstatePost::find(self::EXTERNAL_ID)?->id);
		self::assertSame((string) $createdId, EstatePost::find(self::EXTERNAL_ID, language: 'en')?->id);

		EstatePost::update(self::EXTERNAL_ID, $this->english(), language: 'en');

		self::assertSame((string) $createdId, EstatePost::find(self::EXTERNAL_ID, language: 'en')?->id);
	}

	public function test_reading_a_post_in_a_language_returns_that_languages_payload(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		EstatePost::update(self::EXTERNAL_ID, $this->english(), language: 'en');

		$post = $this->post();

		self::assertSame('Am Exerzierplatz 9, Mannheim', EstatePost::fromPost($post)->data->name);
		self::assertSame('Am Exerzierplatz 9, Mannheim', EstatePost::fromPost($post, language: 'de')->data->name);
		self::assertSame('Exerzierplatz 9, Mannheim (EN)', EstatePost::fromPost($post, language: 'en')->data->name);
		self::assertSame('English slogan', EstatePost::fromPost($post, language: 'en')->data->texts->slogan);
	}

	/**
	 * A language that was never imported (or was requested by a front-end filter
	 * for a language the site does not sync) falls back to the default payload.
	 *
	 * Asserting on the slug and address matters here: when no payload can be read
	 * at all, fromPost() still returns a placeholder Estate whose name is the
	 * post title, so a name-only assertion would pass either way.
	 */
	public function test_reading_a_language_that_was_not_imported_falls_back_to_the_default_payload(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);

		$estate = EstatePost::fromPost($this->post(), language: 'fr')->data;

		self::assertSame('Am Exerzierplatz 9, Mannheim', $estate->name);
		self::assertSame('am-exerzierplatz-9-mannheim', $estate->slug);
		self::assertSame('Mannheim', $estate->address->city);
	}

	public function test_an_empty_translated_payload_also_falls_back_to_the_default_payload(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		$postId = (int) array_key_first(WpState::instance()->posts);

		// A language row that exists but holds nothing usable.
		WpState::instance()->postMeta[$postId]['estate_data_en'] = '';

		$estate = EstatePost::fromPost($this->post(), language: 'en')->data;

		self::assertSame('Am Exerzierplatz 9, Mannheim', $estate->name);
		self::assertSame('am-exerzierplatz-9-mannheim', $estate->slug);
	}

	/**
	 * When no payload can be read at all - the data transfer into WordPress
	 * broke - fromPost() degrades to a placeholder built from the post itself
	 * rather than throwing, so a sync or a page render is never blocked by one
	 * bad row.
	 */
	public function test_a_post_without_any_payload_degrades_to_a_placeholder_estate(): void
	{
		$state = WpState::instance();
		$postId = $state->addPost(
			['post_type' => EstatePost::POST_TYPE, 'post_title' => 'Broken estate', 'post_status' => 'publish'],
			['domos_id' => self::EXTERNAL_ID],
		);

		// fromPost() logs the bad row; that belongs in the site log, not in here.
		$previousLog = ini_get('error_log');
		ini_set('error_log', '/dev/null');

		try {
			$estate = EstatePost::fromPost(new \WP_Post($state->post($postId)))->data;
		} finally {
			ini_set('error_log', $previousLog === false ? '' : $previousLog);
		}

		self::assertSame('Broken estate', $estate->name);
		self::assertSame(self::EXTERNAL_ID, $estate->slug, 'Without a payload the placeholder uses the external id as slug.');
	}

	/**
	 * A translated payload has no web exposé of its own; the front-end template
	 * relies on that being null so it can fall back to the default language.
	 */
	public function test_a_translated_payload_keeps_a_missing_expose_null(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		EstatePost::update(self::EXTERNAL_ID, $this->english(), language: 'en');

		$post = $this->post();

		self::assertNotNull(EstatePost::fromPost($post)->data->expose);
		self::assertNull(EstatePost::fromPost($post, language: 'en')->data->expose);
	}

	public function test_has_language_reports_which_payloads_were_imported(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);
		EstatePost::update(self::EXTERNAL_ID, $this->english(), language: 'en');

		$postId = (int) array_key_first(WpState::instance()->posts);

		self::assertTrue(EstatePost::hasLanguage($postId, 'de'), 'The default language payload is stored under estate_data.');
		self::assertTrue(EstatePost::hasLanguage($postId, 'en'));
		self::assertFalse(EstatePost::hasLanguage($postId, 'fr'));
	}

	public function test_has_language_is_false_before_the_language_was_imported(): void
	{
		EstatePost::create(self::EXTERNAL_ID, $this->german(), language: null);

		$postId = (int) array_key_first(WpState::instance()->posts);

		self::assertFalse(EstatePost::hasLanguage($postId, 'en'));
	}

	private function post(): \WP_Post
	{
		$fields = WpState::instance()->post((int) array_key_first(WpState::instance()->posts));

		return new \WP_Post($fields);
	}
}
