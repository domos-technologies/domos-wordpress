<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit;

use Domos\Core\EstatePost;
use Domos\Core\Exceptions\CouldNotCreatePost;
use Domos\Core\Exceptions\EstateAlreadyExists;
use Domos\Core\Exceptions\EstateNotFound;
use Domos\Core\Tests\Support\Fixtures;
use Domos\Core\Tests\Support\WpState;
use PHPUnit\Framework\TestCase;
use WP_Error;

/**
 * Failure paths for the post writes. A sync that silently drops an estate, or
 * that quietly creates a second post for one it already has, is worse than one
 * that fails loudly - both leave the site in a state nobody can explain later.
 */
final class EstatePostWriteTest extends TestCase
{
	private const EXTERNAL_ID = '11111111-2222-4333-8444-555555555555';

	protected function setUp(): void
	{
		WpState::reset();
		WpState::instance()->options['domos_url'] = 'https://example.test';
		WpState::instance()->options['immocore_default_language'] = 'de';

		Fixtures::bootDomos();
	}

	public function test_create_reports_a_failed_insert_instead_of_pretending_it_worked(): void
	{
		WpState::instance()->insertPostResult = new WP_Error('db_insert_error', 'Could not insert post into the database');

		try {
			EstatePost::create(self::EXTERNAL_ID, Fixtures::estate());
			self::fail('Expected CouldNotCreatePost.');
		} catch (CouldNotCreatePost $e) {
			self::assertStringContainsString('Could not insert post', $e->getMessage());
		}

		self::assertSame([], WpState::instance()->posts, 'A failed insert must not leave a half-written post behind.');
	}

	public function test_create_refuses_to_write_a_second_post_for_the_same_estate(): void
	{
		WpState::instance()->addPost(
			['post_type' => EstatePost::POST_TYPE],
			['domos_id' => self::EXTERNAL_ID],
		);

		$this->expectException(EstateAlreadyExists::class);

		EstatePost::create(self::EXTERNAL_ID, Fixtures::estate());
	}

	public function test_create_stores_the_external_id_so_later_lookups_find_the_post(): void
	{
		EstatePost::create(self::EXTERNAL_ID, Fixtures::estate());
		$postId = (int) array_key_first(WpState::instance()->posts);

		self::assertSame(self::EXTERNAL_ID, WpState::instance()->metaValue($postId, 'domos_id'));
		self::assertNotNull(EstatePost::find(self::EXTERNAL_ID));
	}

	public function test_create_publishes_the_post_as_the_plugin_owner(): void
	{
		EstatePost::create(self::EXTERNAL_ID, Fixtures::estate());
		$post = WpState::instance()->lastSavedPost();

		self::assertSame('publish', $post['post_status']);
		self::assertSame(EstatePost::POST_TYPE, $post['post_type']);
		self::assertSame(1, $post['post_author'], 'Estates are published under author 1.');
	}

	public function test_delete_trashes_rather_than_destroys_the_post(): void
	{
		EstatePost::create(self::EXTERNAL_ID, Fixtures::estate());
		$postId = (int) array_key_first(WpState::instance()->posts);

		EstatePost::delete(self::EXTERNAL_ID);

		// Trashing keeps the permalink and any internal links to the estate
		// recoverable, which is why the plugin does not delete.
		self::assertSame([$postId], WpState::instance()->trashedPosts);
		self::assertSame('trash', WpState::instance()->post($postId)['post_status']);
	}

	public function test_delete_reports_an_unknown_estate(): void
	{
		$this->expectException(EstateNotFound::class);

		EstatePost::delete('never-synced');
	}

	public function test_find_returns_null_for_an_empty_id(): void
	{
		// findUnneeded() calls find() with whatever the meta contained.
		self::assertNull(EstatePost::find(''));
	}
}
